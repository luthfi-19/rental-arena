@extends('layouts.app')
@section('title', 'Manajemen Menu F&B')

@section('content')
<div class="p-6 max-w-[1200px] mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-wider">MANAJEMEN F&B</h1>
            <p class="text-xs text-gray-400 uppercase">Panel Kontrol Owner</p>
        </div>
        <button onclick="document.getElementById('modalAdd').style.display='flex'" class="bg-electric-blue text-white px-4 py-2 rounded text-sm font-bold shadow-lg hover:bg-blue-700 transition uppercase">
            + Tambah Menu
        </button>
    </div>

    <div class="bg-arena-card border border-gray-800 rounded-lg overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-800/50 text-xs uppercase text-gray-400 tracking-wider">
                    <th class="p-4 border-b border-gray-800">Menu</th>
                    <th class="p-4 border-b border-gray-800">Kategori</th>
                    <th class="p-4 border-b border-gray-800">Harga</th>
                    <th class="p-4 border-b border-gray-800">Status</th>
                    <th class="p-4 border-b border-gray-800 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($menus as $m)
                <tr class="border-b border-gray-800/50 hover:bg-gray-800/20 transition text-sm">
                    <td class="p-4 flex items-center gap-3 text-white font-medium">
                        @if($m->gambar)
                            <img src="{{ asset('storage/'.$m->gambar) }}" class="w-10 h-10 rounded object-cover border border-gray-700">
                        @else
                            <div class="w-10 h-10 rounded bg-gray-800 flex items-center justify-center border border-gray-700 text-xs text-gray-500">N/A</div>
                        @endif
                        {{ $m->nama_menu }}
                    </td>
                    <td class="p-4 text-gray-300 capitalize">{{ $m->kategori }}</td>
                    <td class="p-4 text-green-400 font-mono">Rp {{ number_format($m->harga, 0, ',', '.') }}</td>
                    <td class="p-4">
                        <form action="{{ route('owner.fnb.toggle', $m->id_menu) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-2 py-1 rounded text-xs font-bold uppercase {{ $m->status_aktif ? 'bg-green-500/20 text-green-500 border border-green-500/30' : 'bg-gray-700 text-gray-400' }}">
                                {{ $m->status_aktif ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </form>
                    </td>
                    <td class="p-4 text-right flex justify-end gap-4 items-center h-full pt-6">
                        <button onclick="openEditModal({{ $m->id_menu }}, '{{ $m->nama_menu }}', '{{ $m->kategori }}', {{ $m->harga }})" class="text-electric-blue hover:text-blue-400 text-xs font-bold uppercase">Ubah</button>
                        
                        <form action="{{ route('owner.fnb.delete', $m->id_menu) }}" method="POST" onsubmit="return confirm('Hapus menu ini?')">
                            @csrf
                            <button type="submit" class="text-critical-red hover:text-red-400 text-xs font-bold uppercase">Hapus</button>
                        </form>
                    </td>
                </tr>
                <!-- Modal Edit Menu -->
                <div id="modalEdit" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
                    <div class="bg-arena-card border border-gray-800 p-6 rounded-lg w-full max-w-md shadow-2xl">
                        <h2 class="text-white font-bold mb-4 uppercase tracking-wider">Ubah Data Menu</h2>
                        <form id="formEdit" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Nama Menu</label>
                                <input type="text" name="nama_menu" id="edit_nama" required class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-white text-sm focus:border-electric-blue outline-none transition-colors">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Kategori</label>
                                    <select name="kategori" id="edit_kategori" class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-white text-sm focus:border-electric-blue outline-none transition-colors">
                                        <option value="makanan">Makanan</option>
                                        <option value="minuman">Minuman</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Harga (Rp)</label>
                                    <input type="number" name="harga" id="edit_harga" required class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-white text-sm focus:border-electric-blue outline-none transition-colors">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Update Gambar (Opsional)</label>
                                <input type="file" name="gambar" accept="image/*" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-bold file:bg-gray-800 file:text-white hover:file:bg-gray-700 transition-colors">
                            </div>
                            <div class="flex gap-2 pt-4">
                                <button type="button" onclick="document.getElementById('modalEdit').style.display='none'" class="flex-1 bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold py-3 rounded uppercase tracking-wider transition-colors">Batal</button>
                                <button type="submit" class="flex-1 bg-electric-blue hover:bg-blue-700 text-white text-xs font-bold py-3 rounded uppercase tracking-wider shadow-lg transition-colors">Update Menu</button>
                            </div>
                        </form>
                    </div>
                </div>

                    <script>
                        function openEditModal(id, nama, kategori, harga) {
                            // Isi form dengan data saat ini
                            document.getElementById('edit_nama').value = nama;
                            document.getElementById('edit_kategori').value = kategori;
                            document.getElementById('edit_harga').value = harga;
                            
                            // Ubah action form ke route update yang sesuai dengan ID
                            document.getElementById('formEdit').action = '/owner/fnb/update/' + id;
                            
                            // Tampilkan Modal
                            document.getElementById('modalEdit').style.display = 'flex';
                        }
                    </script>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div id="modalAdd" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
    <div class="bg-arena-card border border-gray-800 p-6 rounded-lg w-full max-w-md">
        <h2 class="text-white font-bold mb-4 uppercase">Tambah Menu F&B</h2>
        <form action="{{ route('owner.fnb.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs text-gray-400 mb-1">Nama Menu</label>
                <input type="text" name="nama_menu" required class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-white text-sm focus:border-electric-blue outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Kategori</label>
                    <select name="kategori" class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-white text-sm focus:border-electric-blue outline-none">
                        <option value="makanan">Makanan</option>
                        <option value="minuman">Minuman</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Harga (Rp)</label>
                    <input type="number" name="harga" required class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-white text-sm focus:border-electric-blue outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Upload Gambar (Opsional)</label>
                <input type="file" name="gambar" accept="image/*" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-bold file:bg-gray-800 file:text-white hover:file:bg-gray-700">
            </div>
            <div class="flex gap-2 pt-4">
                <button type="button" onclick="document.getElementById('modalAdd').style.display='none'" class="flex-1 bg-gray-800 text-white text-xs font-bold py-2 rounded uppercase">Batal</button>
                <button type="submit" class="flex-1 bg-electric-blue text-white text-xs font-bold py-2 rounded uppercase">Simpan Menu</button>
            </div>
        </form>
    </div>
</div>
@endsection