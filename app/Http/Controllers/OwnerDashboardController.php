<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiSewa;
use App\Models\TransaksiFnb;
use App\Models\Perangkat;
use Carbon\Carbon;

class OwnerDashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // 1. KPI Cards Data
        $pendapatanSewaHariIni = TransaksiSewa::whereDate('waktu_selesai', $today)->sum('total_biaya');
        $pendapatanFnbHariIni = TransaksiFnb::whereDate('waktu_transaksi', $today)->sum('total_bayar');
        $totalPendapatanHariIni = $pendapatanSewaHariIni + $pendapatanFnbHariIni;
        
        $sesiAktif = TransaksiSewa::where('status_sesi', 'berjalan')->count();
        $perangkatMaintenance = Perangkat::where('status', 'maintenance')->count();

        // 2. Data Grafik Garis Pendapatan (7 & 30 Hari)
        $chart7Days = $this->getRevenueData(7);
        $chart30Days = $this->getRevenueData(30);

        // 3. Data Heatmap (Hari x Jam)
        $heatmapData = $this->getHeatmapData();

        // 4. Data Performa Unit
        $performaUnit = Perangkat::withCount(['transaksiSewa as frekuensi_sewa' => function($q) {
            $q->where('status_sesi', 'selesai');
        }])->orderBy('jam_terbang_total', 'desc')->get();

        // 5. Data Proporsi Pendapatan (Bulan Ini)
        $proporsiSewa = TransaksiSewa::whereMonth('waktu_selesai', Carbon::now()->month)->sum('total_biaya');
        $proporsiFnb = TransaksiFnb::whereMonth('waktu_transaksi', Carbon::now()->month)->sum('total_bayar');

        return view('owner.dashboard', compact(
            'totalPendapatanHariIni', 'pendapatanFnbHariIni', 'sesiAktif', 'perangkatMaintenance',
            'chart7Days', 'chart30Days', 'heatmapData', 'performaUnit', 'proporsiSewa', 'proporsiFnb'
        ));
    }

    private function getRevenueData($days)
    {
        $data = ['labels' => [], 'values' => []];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $sewa = TransaksiSewa::whereDate('waktu_selesai', $date)->sum('total_biaya');
            $fnb = TransaksiFnb::whereDate('waktu_transaksi', $date)->sum('total_bayar');
            
            $data['labels'][] = $date->format('d M');
            $data['values'][] = $sewa + $fnb;
        }
        return $data;
    }

    private function getHeatmapData()
    {
        // Hanya tarik data 7 hari terakhir sesuai spesifikasi
        $sessions = TransaksiSewa::where('waktu_mulai', '>=', now()->subDays(7))->get();
        
        $matrix = array_fill(0, 7, array_fill(0, 24, 0));
        $maxVal = 1;

        foreach($sessions as $s) {
            $day = $s->waktu_mulai->dayOfWeekIso - 1; 
            $hour = $s->waktu_mulai->hour;
            $matrix[$day][$hour]++;
            $maxVal = max($maxVal, $matrix[$day][$hour]);
        }
        return ['matrix' => $matrix, 'max' => $maxVal];
    }
}