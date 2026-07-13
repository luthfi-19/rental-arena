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

        $unitPerhatian = Perangkat::where(function($query) {
            $query->where('status', 'maintenance')
                  // jam_terbang >= 90% ambang_batas, artinya sisa <= 10%
                  ->orWhereRaw('jam_terbang_total >= (ambang_batas_servis * 0.9)');
        })->get();

        return view('kasir.dashboard', compact(
            'allPerangkat', 'unitAktifCount', 'pendapatanSewaHariIni', 'pendapatanFnbHariIni', 'totalPendapatanHariIni', 'unitPerhatian'
        ));
    }

    public function mulaiSesi(Request $request)
    {
        $request->validate([
            'id_perangkat' => 'required|exists:perangkat,id_perangkat',
            'tarif_per_jam' => 'required|numeric',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // LOCK BARIS PERANGKAT AGAR TIDAK BISA DIAKSES PROSES LAIN BERSAMAAN
                $perangkat = Perangkat::where('id_perangkat', $request->id_perangkat)
                                      ->lockForUpdate()
                                      ->firstOrFail();

                // GUARD EKSPLISIT: Pastikan benar-benar tersedia
                if ($perangkat->status !== 'tersedia') {
                    throw new \Exception('Unit sedang digunakan atau dalam perbaikan.');
                }

                $perangkat->update(['status' => 'dipakai']);

                TransaksiSewa::create([
                    'id_perangkat' => $perangkat->id_perangkat,
                    'id_user' => auth()->user()->id_user,
                    'nama_pelanggan' => $request->nama_pelanggan ?? 'Guest Player',
                    'waktu_mulai' => now(),
                    'tarif_per_jam' => $request->tarif_per_jam,
                    'status_sesi' => 'berjalan'
                ]);
            });
            return redirect()->route('kasir.dashboard')->with('success', 'Sesi berhasil dimulai.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function selesaiSesi(Request $request, $id_sewa)
    {
        try {
            DB::transaction(function () use ($id_sewa) {
                // LOCK BARIS TRANSAKSI
                $sewa = TransaksiSewa::where('id_sewa', $id_sewa)->lockForUpdate()->firstOrFail();
                
                // GUARD: Cegah double click atau eksploitasi API
                if ($sewa->status_sesi !== 'berjalan') {
                    throw new \Exception('Sesi ini sudah diselesaikan atau dibatalkan sebelumnya.');
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

                // Update Jam Terbang & Trigger Otomatis
                $perangkat = Perangkat::where('id_perangkat', $sewa->id_perangkat)->lockForUpdate()->firstOrFail();
                $durasiJam = ceil($durasiMenit / 60); 
                
                $akumulasiJamBaru = $perangkat->jam_terbang_total + $durasiJam;
                // FIX AMBANG BATAS: Hitung sisa berdasarkan persentase
                $sisaUmurPersen = ($perangkat->ambang_batas_servis - $akumulasiJamBaru) / $perangkat->ambang_batas_servis;
                
                $statusBaru = 'tersedia';
                $riwayatKerusakan = $perangkat->riwayat_kerusakan;

                if ($sisaUmurPersen <= 0) {
                    $statusBaru = 'maintenance';
                    $riwayatKerusakan += 1; 
                }

                $perangkat->update([
                    'jam_terbang_total' => $akumulasiJamBaru,
                    'status' => $statusBaru,
                    'riwayat_kerusakan' => $riwayatKerusakan
                ]);
            });
            return redirect()->route('kasir.dashboard')->with('success', 'Sesi diselesaikan.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}