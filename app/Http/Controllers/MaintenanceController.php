<?php

namespace App\Http\Controllers;

use App\Models\Perangkat;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index()
    {
        // 1. FILTERING HANYA UNIT YANG BUTUH PERHATIAN (Sesuai kritik)
        $perangkats = Perangkat::where('status', 'maintenance')
            ->orWhereRaw('jam_terbang_total >= (ambang_batas_servis * 0.9)')
            ->get();

        if ($perangkats->isEmpty()) {
            return view('maintenance.index', ['ranking' => collect([])]);
        }

        // 2. BOBOT DINAMIS (Mengambil dari config, default fallback sesuai laporan)
        $w1 = config('saw.weights.c1', 0.5); 
        $w2 = config('saw.weights.c2', 0.3);
        $w3 = config('saw.weights.c3', 0.2);

        $dataKriteria = $perangkats->map(function($p) {
            $sisaUmur = $p->ambang_batas_servis - $p->jam_terbang_total; // Bisa negatif
            $c2 = $p->transaksiSewa()->where('waktu_mulai', '>=', now()->subDays(7))->count();
            return [
                'perangkat' => $p,
                'c1_raw' => $sisaUmur,
                'c2' => $c2,
                'c3' => $p->riwayat_kerusakan
            ];
        });

        // 3. PENANGANAN NILAI NEGATIF C1 (Value Shifting)
        $minSisaUmur = $dataKriteria->min('c1_raw');
        $shiftValue = $minSisaUmur <= 0 ? abs($minSisaUmur) + 1 : 0;

        $dataKriteria->transform(function($item) use ($shiftValue) {
            $item['c1'] = $item['c1_raw'] + $shiftValue; // Mentranslasi nilai negatif ke positif
            return $item;
        });

        $minC1 = $dataKriteria->min('c1'); // Sekarang pasti >= 1
        $maxC2 = max(1, $dataKriteria->max('c2')); 
        $maxC3 = max(1, $dataKriteria->max('c3'));

        $hasilSAW = $dataKriteria->map(function($item) use ($minC1, $maxC2, $maxC3, $w1, $w2, $w3) {
            $normC1 = $minC1 / $item['c1']; // Cost
            $normC2 = $item['c2'] / $maxC2; // Benefit
            $normC3 = $item['c3'] / $maxC3; // Benefit

            $skorAkhir = ($w1 * $normC1) + ($w2 * $normC2) + ($w3 * $normC3);

            return [
                'perangkat' => $item['perangkat'],
                'sisa_umur' => $item['c1_raw'], // Tampilkan nilai asli ke UI (bukan yang di-shift)
                'frekuensi' => $item['c2'],
                'riwayat' => $item['c3'],
                'skor' => round($skorAkhir, 4)
            ];
        });

        $ranking = $hasilSAW->sortByDesc('skor')->values();
        return view('maintenance.index', compact('ranking'));
    }

    public function selesaiServis($id)
    {
        $perangkat = Perangkat::findOrFail($id);
        
        // Reset status dan data pemakaian
        $perangkat->update([
            'status' => 'tersedia',
            'jam_terbang_total' => 0, // Reset jam terbang ke 0 setelah servis
            'riwayat_kerusakan' => 0,  // Reset riwayat kerusakan
            'tanggal_servis_terakhir' => now()
        ]);

        return redirect()->route('maintenance.index')->with('success', 'Unit telah selesai diservis dan siap digunakan.');
    }
}