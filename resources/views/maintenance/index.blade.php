@extends('layouts.app')
@section('title', 'Prioritas Maintenance Unit')

@section('content')
<div class="p-6 max-w-[1200px] mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white tracking-wider">PREDICTIVE MAINTENANCE</h1>
        <p class="text-xs text-gray-400 uppercase mt-1">Sistem Pendukung Keputusan Metode SAW (Simple Additive Weighting)</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-arena-card border border-gray-800 p-4 rounded-lg">
            <span class="text-[10px] text-gray-500 uppercase block font-bold">Kriteria 1 (Cost)</span>
            <span class="text-sm text-electric-blue font-bold">Sisa Umur Pakai (W=0.5)</span>
        </div>
        <div class="bg-arena-card border border-gray-800 p-4 rounded-lg">
            <span class="text-[10px] text-gray-500 uppercase block font-bold">Kriteria 2 (Benefit)</span>
            <span class="text-sm text-green-400 font-bold">Frekuensi 7 Hari (W=0.3)</span>
        </div>
        <div class="bg-arena-card border border-gray-800 p-4 rounded-lg">
            <span class="text-[10px] text-gray-500 uppercase block font-bold">Kriteria 3 (Benefit)</span>
            <span class="text-sm text-amber-500 font-bold">Riwayat Kerusakan (W=0.2)</span>
        </div>
    </div>

    <div class="bg-arena-card border border-gray-800 rounded-lg overflow-hidden shadow-2xl">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-800/50 text-xs uppercase text-gray-400 tracking-wider">
                    <th class="p-4 border-b border-gray-800 w-16 text-center">Rank</th>
                    <th class="p-4 border-b border-gray-800">Kode Unit</th>
                    <th class="p-4 border-b border-gray-800">Sisa Umur (C1)</th>
                    <th class="p-4 border-b border-gray-800">Frekuensi (C2)</th>
                    <th class="p-4 border-b border-gray-800">Kerusakan (C3)</th>
                    <th class="p-4 border-b border-gray-800">Skor Akhir (V)</th>
                    <th class="p-4 border-b border-gray-800">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ranking as $index => $item)
                @php
                    $isKritis = $item['perangkat']->status === 'maintenance' || $item['sisa_umur'] <= ($item['perangkat']->ambang_batas_servis * 0.1);
                    $rowClass = $isKritis ? 'bg-critical-red/10 border-l-4 border-l-critical-red' : 'border-l-4 border-l-transparent hover:bg-gray-800/20';
                @endphp
                <tr class="border-b border-gray-800/50 transition text-sm {{ $rowClass }}">
                    <td class="p-4 text-center font-bold text-gray-300">#{{ $index + 1 }}</td>
                    <td class="p-4">
                        <span class="block font-bold text-white">{{ $item['perangkat']->kode_unit }}</span>
                        <span class="text-[10px] text-gray-500 uppercase">{{ $item['perangkat']->zona }}</span>
                    </td>
                    <td class="p-4 font-mono {{ $item['sisa_umur'] <= 50 ? 'text-critical-red font-bold' : 'text-gray-300' }}">
                        {{ $item['sisa_umur'] }} Jam
                    </td>
                    <td class="p-4 text-gray-300">{{ $item['frekuensi'] }} Sesi</td>
                    <td class="p-4 text-gray-300">{{ $item['riwayat'] }} Kali</td>
                    <td class="p-4 font-mono font-bold text-electric-blue text-lg">{{ $item['skor'] }}</td>
                    <td class="p-4">
                        @if($item['perangkat']->status === 'maintenance')
                            <form action="{{ route('maintenance.selesai', $item['perangkat']->id_perangkat) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-2 py-1 text-[10px] font-bold rounded bg-green-500 text-black uppercase hover:bg-green-400 transition cursor-pointer">
                                    Selesai Servis
                                </button>
                            </form>
                        @elseif($item['sisa_umur'] <= ($item['perangkat']->ambang_batas_servis * 0.1))
                            <span class="px-2 py-1 text-[10px] font-bold rounded bg-amber-500/20 text-amber-500 uppercase border border-amber-500/30">Warning</span>
                        @else
                            <span class="px-2 py-1 text-[10px] font-bold rounded bg-green-500/20 text-green-500 uppercase border border-green-500/30">Aman</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection