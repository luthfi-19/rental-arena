@extends('layouts.app')
@section('title', 'Dashboard Kasir')

@section('content')

<div class="p-6 max-w-[1600px] mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6">
    
    <div class="lg:col-span-8 bg-arena-card/40 border border-gray-800 p-6 rounded-lg">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400">Denah Monitor Live Unit</h2>
            <button onclick="openUnitModal()" class="bg-electric-blue/10 border border-electric-blue text-electric-blue hover:bg-electric-blue hover:text-white transition-all text-[10px] font-bold py-1.5 px-3 rounded uppercase tracking-wider">
                + Tambah Unit
            </button>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($allPerangkat as $unit)
                @php
                    $isNearLimit = (($unit->ambang_batas_servis - $unit->jam_terbang_total) <= ($unit->ambang_batas_servis * 0.1)) && ($unit->status !== 'maintenance');
                    $sesiAktif = $unit->transaksiSewa->first();
                    
                    // Logika Styling Warna Sesuai Kontrak UI
                    $cardClass = 'border-gray-800';
                    $statusIndicator = 'bg-green-500';
                    $textStatus = 'Tersedia';

                    if($unit->status === 'dipakai') {
                        $cardClass = 'glow-active border-electric-blue';
                        $statusIndicator = 'bg-blue-500';
                        $textStatus = 'Sesi Berjalan';
                    } elseif ($unit->status === 'maintenance') {
                        $cardClass = 'border-critical-red bg-critical-red/10';
                        $statusIndicator = 'bg-critical-red';
                        $textStatus = 'Maintenance';
                    } elseif ($isNearLimit) {
                        $cardClass = 'border-amber-600 pulse-warn';
                        $statusIndicator = 'bg-amber-500';
                        $textStatus = 'Batas Servis';
                    }
                @endphp

                <div class="bg-arena-card border {{ $cardClass }} p-4 rounded-lg flex flex-col justify-between h-40 transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs font-mono text-gray-500 block uppercase">{{ $unit->zona }} | {{ $unit->jenis }}</span>
                            <h3 class="text-lg font-bold text-white tracking-wide mt-0.5">{{ $unit->kode_unit }}</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($unit->status !== 'dipakai')
                                <button type="button"
                                    onclick="openUnitModal({{ $unit->id_perangkat }}, '{{ $unit->kode_unit }}', '{{ $unit->jenis }}', '{{ $unit->zona }}', {{ $unit->ambang_batas_servis }}, {{ $unit->jam_terbang_total }})"
                                    title="Edit unit" class="text-gray-500 hover:text-electric-blue transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form action="{{ route('perangkat.destroy', $unit->id_perangkat) }}" method="POST" onsubmit="return confirm('Hapus unit {{ $unit->kode_unit }}? Tindakan ini tidak bisa dibatalkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus unit" class="text-gray-500 hover:text-critical-red transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            @endif
                            <span class="flex h-2.5 w-2.5 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $statusIndicator }} opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 {{ $statusIndicator }}"></span>
                            </span>
                        </div>
                    </div>

                    <div class="my-2">
                        @if($unit->status === 'dipakai' && $sesiAktif)
                            <p class="text-xs text-gray-300">User: <span class="text-white font-medium">{{ $sesiAktif->nama_pelanggan }}</span></p>
                            <p class="text-xs text-blue-400 mt-1 font-mono">Mulai: {{ $sesiAktif->waktu_mulai->format('H:i') }}</p>
                        @elseif($unit->status === 'maintenance')
                            <p class="text-xs text-red-400 italic">OFFLINE — Perlu Servis</p>
                        @elseif($isNearLimit)
                            <p class="text-xs text-amber-400 font-medium">Jam Terbang: {{ $unit->jam_terbang_total }}h / {{ $unit->ambang_batas_servis }}h</p>
                        @else
                            <p class="text-xs text-green-400">Siap Digunakan</p>
                        @endif
                    </div>

                    <div>
                        @if($unit->status === 'tersedia')
                            <button onclick="openMulaiModal({{ $unit->id_perangkat }}, '{{ $unit->kode_unit }}')" 
                                class="w-full bg-electric-blue text-white font-semibold text-xs py-1.5 px-3 rounded hover:bg-blue-700 transition-colors uppercase tracking-wider">
                                Mulai Sesi
                            </button>
                        @elseif($unit->status === 'dipakai' && $sesiAktif)
                            <form action="{{ route('sewa.selesai', $sesiAktif->id_sewa) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-critical-red text-white font-semibold text-xs py-1.5 px-3 rounded hover:bg-red-700 transition-colors uppercase tracking-wider">
                                    Stop & Bayar
                                </button>
                            </form>
                        @else
                            <button disabled class="w-full bg-gray-800 text-gray-500 font-semibold text-xs py-1.5 px-3 rounded cursor-not-allowed uppercase tracking-wider">
                                Locked
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="lg:col-span-4 space-y-6">
        
        <div class="bg-arena-card border border-gray-800 p-6 rounded-lg">
            <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Ringkasan Sesi Hari Ini</h2>
            <div class="space-y-4">
                <div>
                    <span class="text-xs text-gray-500 block uppercase">Unit Aktif Digunakan</span>
                    <span class="text-3xl font-bold text-blue-400 tracking-tight">{{ $unitAktifCount }} <span class="text-sm font-normal text-gray-400">Unit</span></span>
                </div>
        
                <div class="pt-3 border-t border-gray-800">
                    <div class="flex justify-between mb-1">
                        <span class="text-[10px] text-gray-500 block uppercase">Pendapatan Rental</span>
                        <span class="text-[10px] font-mono text-gray-300">Rp {{ number_format($pendapatanSewaHariIni, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="text-[10px] text-gray-500 block uppercase">Pendapatan F&B</span>
                        <span class="text-[10px] font-mono text-gray-300">Rp {{ number_format($pendapatanFnbHariIni, 0, ',', '.') }}</span>
                    </div>
                    <div class="pt-2 border-t border-gray-700 border-dashed">
                        <span class="text-xs text-gray-400 block uppercase">Total Revenue (Live)</span>
                        <span class="text-2xl font-bold text-green-400 tracking-tight">Rp {{ number_format($totalPendapatanHariIni, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-arena-card border border-gray-800 p-6 rounded-lg">
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400">Perhatian Perangkat</h2>
                <span class="px-2 py-0.5 bg-critical-red/20 text-critical-red text-[10px] font-bold rounded uppercase tracking-wider border border-critical-red/30">
                    {{ $unitPerhatian->count() }} Alert
                </span>
            </div>
            <div class="max-h-44 overflow-y-auto space-y-2 pr-1 no-scrollbar">
                @forelse($unitPerhatian as $perhatian)
                    <div class="p-2.5 bg-arena-bg rounded border border-gray-800 flex justify-between items-center">
                        <div>
                            <span class="text-xs font-bold text-white block">{{ $perhatian->kode_unit }}</span>
                            <span class="text-[10px] text-gray-400">{{ $perhatian->zona }}</span>
                        </div>
                        <span class="text-xs {{ $perhatian->status === 'maintenance' ? 'text-critical-red font-bold' : 'text-amber-500 font-medium' }}">
                            {{ $perhatian->status === 'maintenance' ? 'Offline Servis' : $perhatian->jam_terbang_total . 'h Pemakaian' }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-gray-500 italic py-2">Semua perangkat dalam kondisi aman optimal.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-arena-card border border-electric-blue p-4 rounded-lg text-center mt-4">
            <a href="{{ route('kasir.fnb.index') }}" class="block w-full bg-electric-blue/10 border border-electric-blue text-electric-blue hover:bg-electric-blue hover:text-white transition-all text-xs py-3 rounded font-bold tracking-wide uppercase">
                🚀 Buka Terminal POS F&B
            </a>
        </div>
    </div>
</div>

<div id="mulaiModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
    <div class="bg-arena-card border border-gray-800 p-6 rounded-lg max-w-sm w-full">
        <h3 class="text-base font-bold text-white mb-4 uppercase tracking-wider">Mulai Sesi <span id="modal_kode_unit" class="text-blue-400"></span></h3>
        
        <form action="{{ route('sewa.mulai') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="id_perangkat" id="modal_id_perangkat">
            
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Nama Pelanggan (Opsional)</label>
                <input type="text" name="nama_pelanggan" placeholder="Guest" class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-electric-blue">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Tarif Per Jam (Rp)</label>
                <select name="tarif_per_jam" class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-electric-blue">
                    <option value="10000">Regular Area - Rp 10.000 / jam</option>
                    <option value="15000">VIP Room - Rp 15.000 / jam</option>
                    <option value="20000">PC E-Sport Stage - Rp 20.000 / jam</option>
                </select>
            </div>

            <div class="flex space-x-2 pt-2">
                <button type="button" onclick="closeMulaiModal()" class="flex-1 bg-gray-800 text-gray-400 text-xs font-bold py-2 rounded uppercase tracking-wider">Batal</button>
                <button type="submit" class="flex-1 bg-electric-blue text-white text-xs font-bold py-2 rounded uppercase tracking-wider shadow-md hover:bg-blue-700">Open Session</button>
            </div>
        </form>
    </div>
</div>

<div id="unitModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
    <div class="bg-arena-card border border-gray-800 p-6 rounded-lg max-w-sm w-full">
        <h3 id="unitModalTitle" class="text-base font-bold text-white mb-4 uppercase tracking-wider">Tambah Unit Baru</h3>

        <form id="unitForm" action="{{ route('perangkat.store') }}" method="POST" class="space-y-3">
            @csrf
            <input type="hidden" name="_method" id="unitFormMethod" value="POST">

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Kode Unit</label>
                <input type="text" name="kode_unit" id="unit_kode_unit" required placeholder="PS5-VIP-02" class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-electric-blue">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Jenis</label>
                <select name="jenis" id="unit_jenis" class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-electric-blue">
                    <option value="konsol">Konsol</option>
                    <option value="joystick">Joystick</option>
                    <option value="headset">Headset</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Zona</label>
                <input type="text" name="zona" id="unit_zona" required placeholder="VIP Room / Regular Area / E-Sport Stage" class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-electric-blue">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Ambang Batas Servis (jam)</label>
                <input type="number" name="ambang_batas_servis" id="unit_ambang" required min="1" placeholder="300" class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-electric-blue">
            </div>

            <div id="unitJamTerbangWrap" class="hidden">
                <label class="block text-xs font-medium text-gray-400 mb-1">Jam Terbang Saat Ini</label>
                <input type="text" id="unit_jam_terbang" disabled class="w-full bg-gray-900 border border-gray-800 rounded px-3 py-2 text-sm text-gray-500 cursor-not-allowed">
                <p class="text-[10px] text-gray-500 mt-1 italic">🔒 Terkunci — nilai ini otomatis terupdate dari histori pemakaian, tidak bisa diedit manual.</p>
            </div>

            <div class="flex space-x-2 pt-2">
                <button type="button" onclick="closeUnitModal()" class="flex-1 bg-gray-800 text-gray-400 text-xs font-bold py-2 rounded uppercase tracking-wider">Batal</button>
                <button type="submit" class="flex-1 bg-electric-blue text-white text-xs font-bold py-2 rounded uppercase tracking-wider shadow-md hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openUnitModal(id, kode, jenis, zona, ambang, jamTerbang) {
        const form = document.getElementById('unitForm');
        const title = document.getElementById('unitModalTitle');
        const jamWrap = document.getElementById('unitJamTerbangWrap');

        if (id) {
            // Mode edit
            title.innerText = 'Edit Unit ' + kode;
            form.action = '{{ url("/perangkat") }}/' + id;
            document.getElementById('unitFormMethod').value = 'PUT';
            document.getElementById('unit_kode_unit').value = kode;
            document.getElementById('unit_jenis').value = jenis;
            document.getElementById('unit_zona').value = zona;
            document.getElementById('unit_ambang').value = ambang;
            document.getElementById('unit_jam_terbang').value = jamTerbang + ' jam';
            jamWrap.classList.remove('hidden');
        } else {
            // Mode tambah
            title.innerText = 'Tambah Unit Baru';
            form.action = '{{ route("perangkat.store") }}';
            document.getElementById('unitFormMethod').value = 'POST';
            form.reset();
            jamWrap.classList.add('hidden');
        }

        document.getElementById('unitModal').style.display = 'flex';
    }

    function closeUnitModal() {
        document.getElementById('unitModal').style.display = 'none';
    }
</script>

<script>
    function openMulaiModal(id, kode) {
        document.getElementById('modal_id_perangkat').value = id;
        document.getElementById('modal_kode_unit').innerText = kode;
        document.getElementById('mulaiModal').style.display = 'flex';
    }

    function closeMulaiModal() {
        document.getElementById('mulaiModal').style.display = 'none';
    }
    setInterval(function() {
        window.location.reload();
    }, 60000);
</script>
@endsection