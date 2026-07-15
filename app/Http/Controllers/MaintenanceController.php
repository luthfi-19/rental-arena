<?php

namespace App\Http\Controllers;

use App\Models\Perangkat;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index()
    {
        // Hanya unit yang relevan untuk diprioritaskan servis: sudah maintenance
        // atau sisa umur pakainya sudah <= 10% dari ambang batas servis.
        $perangkats = Perangkat::where('status', 'maintenance')
            ->orWhereRaw('(ambang_batas_servis - jam_terbang_total) <= (ambang_batas_servis * 0.1)')
            ->get();

        // Bobot Default SAW
        $w1 = 0.5; // C1: Sisa Umur Pakai (Cost)
        $w2 = 0.3; // C2: Frekuensi Pemakaian (Benefit)
        $w3 = 0.2; // C3: Riwayat Kerusakan (Benefit)

        // Tahap 1: Ekstraksi Nilai Dasar
        $dataMentah = $perangkats->map(function($p) {
            // C1 mentah: sisa umur asli (boleh 0/negatif jika sudah lewat ambang batas)
            $sisaUmurAsli = $p->ambang_batas_servis - $p->jam_terbang_total;

            // C2: Frekuensi pemakaian dalam 7 hari terakhir (Benefit)
            $c2 = $p->transaksiSewa()->where('waktu_mulai', '>=', now()->subDays(7))->count();

            // C3: Riwayat Kerusakan (Benefit)
            $c3 = $p->riwayat_kerusakan;

            return [
                'perangkat' => $p,
                'sisa_umur_asli' => $sisaUmurAsli,
                'c2' => $c2,
                'c3' => $c3
            ];
        });

        // Geser C1 supaya nilai minimum jadi >= 1 (hindari 0/negatif merusak formula cost SAW),
        // sekaligus tetap mempertahankan selisih relatif antar unit yang overdue.
        $minSisaUmurAsli = $dataMentah->min('sisa_umur_asli');
        $shift = $minSisaUmurAsli <= 0 ? (1 - $minSisaUmurAsli) : 0;

        $dataKriteria = $dataMentah->map(function($item) use ($shift) {
            $item['c1'] = $item['sisa_umur_asli'] + $shift;
            return $item;
        });

        // Tahap 2: Cari Nilai Min & Max untuk Normalisasi
        $minC1 = $dataKriteria->min('c1');
        $maxC2 = max(1, $dataKriteria->max('c2')); // max(1) untuk hindari division by zero
        $maxC3 = max(1, $dataKriteria->max('c3'));

        // Tahap 3: Normalisasi & Perhitungan Skor Akhir
        $hasilSAW = $dataKriteria->map(function($item) use ($minC1, $maxC2, $maxC3, $w1, $w2, $w3) {
            $normC1 = $minC1 / $item['c1']; // Cost formula
            $normC2 = $item['c2'] / $maxC2; // Benefit formula
            $normC3 = $item['c3'] / $maxC3; // Benefit formula

            $skorAkhir = ($w1 * $normC1) + ($w2 * $normC2) + ($w3 * $normC3);

            return [
                'perangkat' => $item['perangkat'],
                'sisa_umur' => $item['sisa_umur_asli'],
                'frekuensi' => $item['c2'],
                'riwayat' => $item['c3'],
                'skor' => round($skorAkhir, 4)
            ];
        });

        // Urutkan berdasarkan skor tertinggi
        $ranking = $hasilSAW->sortByDesc('skor')->values();

        return view('maintenance.index', compact('ranking'));
    }

    /**
     * Tandai unit sudah selesai diservis: jam terbang direset ke 0,
     * status kembali 'tersedia' supaya bisa disewakan lagi, dan
     * tanggal_servis_terakhir dicatat. 'riwayat_kerusakan' SENGAJA tidak
     * direset karena itu adalah counter historis (kriteria C3 SAW) untuk
     * melacak seberapa sering unit ini pernah rusak sepanjang umurnya.
     */
    public function selesaiServis($id_perangkat)
    {
        $perangkat = Perangkat::findOrFail($id_perangkat);

        if ($perangkat->status !== 'maintenance') {
            return redirect()->back()->withErrors([
                'servis' => 'Unit ini tidak sedang berstatus maintenance.'
            ]);
        }

        $perangkat->update([
            'status' => 'tersedia',
            'jam_terbang_total' => 0,
            'tanggal_servis_terakhir' => now()->toDateString(),
        ]);

        return redirect()->route('maintenance.index')
            ->with('success', "Unit {$perangkat->kode_unit} telah selesai diservis dan siap digunakan kembali.");
    }
}