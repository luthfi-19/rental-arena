@extends('layouts.app')
@section('title', 'Analytics Dashboard - Owner')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="p-4 md:p-6 max-w-[1400px] mx-auto space-y-6">
    
    <div class="mb-2">
        <h1 class="text-xl md:text-2xl font-bold text-white tracking-wider">BUSINESS ANALYTICS</h1>
        <p class="text-xs text-gray-400 uppercase">Live Performance Dashboard</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-arena-card border border-electric-blue/40 p-4 rounded-lg flex flex-col justify-center">
            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Total Revenue (Hari Ini)</span>
            <span class="text-xl md:text-2xl font-bold text-white">Rp {{ number_format($totalPendapatanHariIni, 0, ',', '.') }}</span>
        </div>
        <div class="bg-arena-card border border-electric-blue/40 p-4 rounded-lg flex flex-col justify-center">
            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">F&B Revenue (Hari Ini)</span>
            <span class="text-xl md:text-2xl font-bold text-white">Rp {{ number_format($pendapatanFnbHariIni, 0, ',', '.') }}</span>
        </div>
        <div class="bg-arena-card border border-electric-blue/40 p-4 rounded-lg flex flex-col justify-center">
            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Sesi Aktif</span>
            <span class="text-xl md:text-2xl font-bold text-white">{{ $sesiAktif }} <span class="text-xs text-gray-500 font-normal">Sesi</span></span>
        </div>
        <div class="bg-arena-card border border-critical-red/50 p-4 rounded-lg flex flex-col justify-center">
            <span class="text-[10px] text-critical-red uppercase font-bold tracking-wider mb-1">Perlu Maintenance</span>
            <span class="text-xl md:text-2xl font-bold text-white">{{ $perangkatMaintenance }} <span class="text-xs text-gray-500 font-normal">Unit</span></span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 bg-arena-card border border-gray-800 p-4 rounded-lg">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400">Trend Pendapatan</h2>
                <div class="bg-arena-bg border border-gray-700 rounded p-1 flex text-[10px] font-bold">
                    <button onclick="updateLineChart('7')" id="btn7" class="px-3 py-1 rounded bg-electric-blue text-white transition">7 Hari</button>
                    <button onclick="updateLineChart('30')" id="btn30" class="px-3 py-1 rounded text-gray-400 hover:text-white transition">30 Hari</button>
                </div>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="bg-arena-card border border-gray-800 p-4 rounded-lg">
            <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Proporsi Pendapatan (Bulan Ini)</h2>
            <div class="relative h-48 w-full flex justify-center mb-4">
                <canvas id="proportionChart"></canvas>
            </div>
            <div class="text-center">
                <p class="text-[10px] text-gray-500 uppercase mt-2">Sewa Alat vs F&B</p>
                <div class="flex justify-center gap-4 mt-2 text-xs font-bold">
                    <span class="text-electric-blue">■ Sewa</span>
                    <span class="text-gray-400">■ F&B</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-arena-card border border-gray-800 p-4 rounded-lg overflow-hidden">
        <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Heatmap Utilisasi Arena (Jam Sibuk)</h2>
        <div class="overflow-x-auto pb-2">
            <div class="min-w-[700px]">
                <div class="grid grid-cols-[auto_repeat(24,_1fr)] gap-1 text-[8px] text-gray-500 text-center mb-1">
                    <div class="text-left w-12">HARI</div>
                    @for($h=0; $h<24; $h++) <div>{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}</div> @endfor
                </div>
                @php $days = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']; @endphp
                @for($d=0; $d<7; $d++)
                <div class="grid grid-cols-[auto_repeat(24,_1fr)] gap-1 mb-1">
                    <div class="text-[10px] font-bold text-gray-400 w-12 flex items-center">{{ $days[$d] }}</div>
                    @for($h=0; $h<24; $h++)
                        @php 
                            $count = $heatmapData['matrix'][$d][$h];
                            $opacity = $count > 0 ? max(0.2, $count / $heatmapData['max']) : 0.05; 
                        @endphp
                        <div class="h-6 rounded relative group" style="background-color: rgba(0, 77, 152, {{ $opacity }})">
                            <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 hidden group-hover:block bg-gray-800 text-white text-[9px] px-2 py-1 rounded whitespace-nowrap z-10">
                                {{ $count }} Sesi
                            </div>
                        </div>
                    @endfor
                </div>
                @endfor
            </div>
        </div>
    </div>

    <div class="bg-arena-card border border-gray-800 p-4 rounded-lg">
        <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Laporan Performa & Status Unit</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800/50 text-xs uppercase text-gray-400 tracking-wider">
                        <th class="p-3 border-b border-gray-800">Unit / Zona</th>
                        <th class="p-3 border-b border-gray-800">Total Jam Terbang</th>
                        <th class="p-3 border-b border-gray-800">Frekuensi Disewa</th>
                        <th class="p-3 border-b border-gray-800">Status Mesin</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($performaUnit as $unit)
                    <tr class="border-b border-gray-800/50 hover:bg-gray-800/20 text-sm transition">
                        <td class="p-3">
                            <span class="font-bold text-white block">{{ $unit->kode_unit }}</span>
                            <span class="text-[10px] text-gray-500 uppercase">{{ $unit->zona }} | {{ $unit->jenis }}</span>
                        </td>
                        <td class="p-3 font-mono text-gray-300">{{ $unit->jam_terbang_total }}h <span class="text-[10px] text-gray-600">/ {{ $unit->ambang_batas_servis }}h</span></td>
                        <td class="p-3 text-gray-300">{{ $unit->frekuensi_sewa }} Kali</td>
                        <td class="p-3">
                            @if($unit->status === 'maintenance')
                                <span class="text-[10px] font-bold bg-critical-red/20 text-critical-red px-2 py-1 rounded uppercase border border-critical-red/30">Maintenance</span>
                            @else
                                <span class="text-[10px] font-bold bg-green-500/20 text-green-500 px-2 py-1 rounded uppercase border border-green-500/30">Beroperasi</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    // Data JSON dari Controller
    const data7 = @json($chart7Days);
    const data30 = @json($chart30Days);
    const pSewa = {{ $proporsiSewa }};
    const pFnb = {{ $proporsiFnb }};

    // Konfigurasi Warna Tema
    Chart.defaults.color = '#9CA3AF';
    Chart.defaults.font.family = 'Inter, sans-serif';

    // 1. Line Chart (Revenue)
    const ctxLine = document.getElementById('revenueChart').getContext('2d');
    
    // Buat Gradient Biru Elektrik untuk area bawah garis
    let gradientBlue = ctxLine.createLinearGradient(0, 0, 0, 400);
    gradientBlue.addColorStop(0, 'rgba(0, 77, 152, 0.5)'); // #004D98
    gradientBlue.addColorStop(1, 'rgba(0, 77, 152, 0.0)');

    let revenueChart = new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: data7.labels,
            datasets: [{
                label: 'Total Revenue (Rp)',
                data: data7.values,
                borderColor: '#004D98', // Biru Elektrik
                backgroundColor: gradientBlue,
                borderWidth: 2,
                pointBackgroundColor: '#004D98',
                fill: true,
                tension: 0.4 // Membuat garis melengkung smooth
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: 'rgba(31, 41, 55, 0.5)' } }, // border-gray-800
                y: { grid: { color: 'rgba(31, 41, 55, 0.5)' }, beginAtZero: true }
            }
        }
    });

    // Fungsi Toggle 7 Hari / 30 Hari
    function updateLineChart(days) {
        let newData = days === '7' ? data7 : data30;
        revenueChart.data.labels = newData.labels;
        revenueChart.data.datasets[0].data = newData.values;
        revenueChart.update();

        // Update styling toggle button
        document.getElementById('btn7').className = days === '7' ? 'px-3 py-1 rounded bg-electric-blue text-white transition' : 'px-3 py-1 rounded text-gray-400 hover:text-white transition';
        document.getElementById('btn30').className = days === '30' ? 'px-3 py-1 rounded bg-electric-blue text-white transition' : 'px-3 py-1 rounded text-gray-400 hover:text-white transition';
    }

    // 2. Doughnut Chart (Proporsi Pendapatan)
    const ctxPie = document.getElementById('proportionChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ['Sewa Rental', 'F&B'],
            datasets: [{
                data: [pSewa, pFnb],
                backgroundColor: ['#004D98', '#D1D5DB'], // Biru Elektrik & Abu-abu terang (NO RED)
                borderColor: '#0D0D0F', // Arena Background
                borderWidth: 2,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            cutout: '75%'
        }
    });
</script>
@endsection