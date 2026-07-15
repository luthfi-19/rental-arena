<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perangkat;
use App\Models\TransaksiSewa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KasirDashboardController extends Controller
{
    public function index()
    {
        $allPerangkat = Perangkat::with(['transaksiSewa' => function($q) {
            $q->where('status_sesi', 'berjalan');
        }])->get();

        $unitAktifCount = Perangkat::where('status', 'dipakai')->count();
        
        $pendapatanSewaHariIni = TransaksiSewa::where('status_sesi', 'selesai')
            ->whereDate('waktu_selesai', Carbon::today())
            ->sum('total_biaya');

        // TAMBAHAN SPRINT 2: Ambil total pendapatan F&B hari ini
        $pendapatanFnbHariIni = \App\Models\TransaksiFnb::whereDate('waktu_transaksi', Carbon::today())
            ->sum('total_bayar');
            
        $totalPendapatanHariIni = $pendapatanSewaHariIni + $pendapatanFnbHariIni;

        // Konsisten dengan modul SAW (maintenance/index): warning saat sisa umur <= 10% ambang batas
        $unitPerhatian = Perangkat::where(function($query) {
            $query->where('status', 'maintenance')
                  ->orWhereRaw('(ambang_batas_servis - jam_terbang_total) <= (ambang_batas_servis * 0.1)');
        })->get();

        return view('kasir.dashboard', compact(
            'allPerangkat', 'unitAktifCount', 'pendapatanSewaHariIni', 'pendapatanFnbHariIni', 'totalPendapatanHariIni', 'unitPerhatian'
        ));
    }

    public function mulaiSesi(Request $request)
    {
        $request->validate([
            'id_perangkat' => 'required|exists:perangkat,id_perangkat',
            'nama_pelanggan' => 'nullable|string|max:100',
            'tarif_per_jam' => 'required|numeric',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Lock row supaya tidak ada request bersamaan yang lolos bareng (double booking)
                $perangkat = Perangkat::where('id_perangkat', $request->id_perangkat)->lockForUpdate()->firstOrFail();

                if ($perangkat->status !== 'tersedia') {
                    throw new \RuntimeException('Unit ini sedang tidak tersedia (sudah dipakai/maintenance).');
                }

                $perangkat->update(['status' => 'dipakai']);

                // Create Transaksi Baru
                TransaksiSewa::create([
                    'id_perangkat' => $perangkat->id_perangkat,
                    'id_user' => auth()->user()->id_user,
                    'nama_pelanggan' => $request->nama_pelanggan ?? 'Guest Player',
                    'waktu_mulai' => now(),
                    'tarif_per_jam' => $request->tarif_per_jam,
                    'status_sesi' => 'berjalan'
                ]);
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('kasir.dashboard')->withErrors(['id_perangkat' => $e->getMessage()]);
        }

        return redirect()->route('kasir.dashboard')->with('success', 'Sesi berhasil dimulai.');
    }

   public function selesaiSesi(Request $request, $id_sewa)
    {
        try {
            DB::transaction(function () use ($id_sewa) {
                $sewa = TransaksiSewa::where('id_sewa', $id_sewa)->lockForUpdate()->firstOrFail();

                if ($sewa->status_sesi !== 'berjalan') {
                    throw new \RuntimeException('Sesi ini sudah diselesaikan sebelumnya.');
                }

                $waktuSelesai = now();

                $durasiMenit = max(1, $sewa->waktu_mulai->diffInMinutes($waktuSelesai));
                $totalBiaya = round(($durasiMenit / 60) * $sewa->tarif_per_jam, 2);

                $sewa->update([
                    'waktu_selesai' => $waktuSelesai,
                    'durasi_menit' => $durasiMenit,
                    'total_biaya' => $totalBiaya,
                    'status_sesi' => 'selesai'
                ]);

                // SPRINT 3: Update Jam Terbang & Trigger Status Otomatis
                $perangkat = Perangkat::findOrFail($sewa->id_perangkat);
                $durasiJam = ceil($durasiMenit / 60);

                $akumulasiJamBaru = $perangkat->jam_terbang_total + $durasiJam;
                $sisaUmur = $perangkat->ambang_batas_servis - $akumulasiJamBaru;

                $statusBaru = 'tersedia';
                $riwayatKerusakan = $perangkat->riwayat_kerusakan;

                // Trigger kondisi: Jika sisa umur <= 0, otomatis maintenance
                if ($sisaUmur <= 0) {
                    $statusBaru = 'maintenance';
                    $riwayatKerusakan += 1; // Tambah riwayat kerusakan untuk perhitungan SAW (C3)
                }

                $perangkat->update([
                    'jam_terbang_total' => $akumulasiJamBaru,
                    'status' => $statusBaru,
                    'riwayat_kerusakan' => $riwayatKerusakan
                ]);
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('kasir.dashboard')->withErrors(['id_sewa' => $e->getMessage()]);
        }

        return redirect()->route('kasir.dashboard')->with('success', 'Sesi diselesaikan. Status perangkat telah diperbarui otomatis.');
    }
}