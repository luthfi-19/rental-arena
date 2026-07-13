@extends('layouts.app')
@section('title', 'Manajemen Akun Sistem')

@section('content')
<div class="p-6 max-w-[1200px] mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-wider">MANAJEMEN AKUN PENGGUNA</h1>
            <p class="text-xs text-gray-400 uppercase">Kelola Akses Owner & Kasir</p>
        </div>
        <button onclick="document.getElementById('modalAddUser').style.display='flex'" class="bg-electric-blue text-white px-4 py-2 rounded text-sm font-bold shadow-lg hover:bg-blue-700 transition uppercase">
            + Tambah Akun
        </button>
    </div>

    @if($errors->any())
        <div class="mb-4 p-3 bg-critical-red/20 border border-critical-red text-red-200 text-xs rounded">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-arena-card border border-gray-800 rounded-lg overflow-hidden shadow-2xl">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-800/50 text-xs uppercase text-gray-400 tracking-wider">
                    <th class="p-4 border-b border-gray-800 w-16 text-center">ID</th>
                    <th class="p-4 border-b border-gray-800">Nama Lengkap</th>
                    <th class="p-4 border-b border-gray-800">Username Login</th>
                    <th class="p-4 border-b border-gray-800">Role / Hak Akses</th>
                    <th class="p-4 border-b border-gray-800 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                <tr class="border-b border-gray-800/50 hover:bg-gray-800/20 transition text-sm">
                    <td class="p-4 text-center font-bold text-gray-500">{{ $u->id_user }}</td>
                    <td class="p-4 text-white font-medium">{{ $u->nama }}</td>
                    <td class="p-4 text-gray-300 font-mono">{{ $u->username }}</td>
                    <td class="p-4">
                        @if($u->role === 'owner')
                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase bg-electric-blue/20 text-electric-blue border border-electric-blue/30">Owner</span>
                        @else
                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase bg-gray-700 text-gray-300 border border-gray-600">Kasir</span>
                        @endif
                    </td>
                    <td class="p-4 text-right flex justify-end gap-4 items-center h-full pt-6">
                        <button onclick="openEditModal({{ $u->id_user }}, '{{ $u->nama }}', '{{ $u->username }}', '{{ $u->role }}')" class="text-electric-blue hover:text-blue-400 text-xs font-bold uppercase">Ubah</button>
                        
                        @if($u->id_user !== auth()->user()->id_user)
                            <form action="{{ route('owner.users.delete', $u->id_user) }}" method="POST" onsubmit="return confirm('Hapus akun ini dari sistem?')">
                                @csrf
                                <button type="submit" class="text-critical-red hover:text-red-400 text-xs font-bold uppercase">Hapus</button>
                            </form>
                        @else
                            <span class="text-gray-600 text-xs font-bold uppercase cursor-not-allowed" title="Anda sedang login dengan akun ini">Hapus</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div id="modalAddUser" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
    <div class="bg-arena-card border border-gray-800 p-6 rounded-lg w-full max-w-md shadow-2xl">
        <h2 class="text-white font-bold mb-4 uppercase">Buat Akun Baru</h2>
        <form action="{{ route('owner.users.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs text-gray-400 mb-1">Nama Lengkap</label>
                <input type="text" name="nama" required class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-white text-sm focus:border-electric-blue outline-none transition-colors">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Username Login</label>
                <input type="text" name="username" required class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-white text-sm focus:border-electric-blue outline-none transition-colors">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Password</label>
                    <input type="password" name="password" required class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-white text-sm focus:border-electric-blue outline-none transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Role</label>
                    <select name="role" class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-white text-sm focus:border-electric-blue outline-none transition-colors">
                        <option value="kasir">Kasir</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2 pt-4">
                <button type="button" onclick="document.getElementById('modalAddUser').style.display='none'" class="flex-1 bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold py-3 rounded uppercase transition-colors">Batal</button>
                <button type="submit" class="flex-1 bg-electric-blue hover:bg-blue-700 text-white text-xs font-bold py-3 rounded uppercase transition-colors shadow-lg">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditUser" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
    <div class="bg-arena-card border border-gray-800 p-6 rounded-lg w-full max-w-md shadow-2xl">
        <h2 class="text-white font-bold mb-4 uppercase">Ubah Data Akun</h2>
        <form id="formEditUser" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs text-gray-400 mb-1">Nama Lengkap</label>
                <input type="text" name="nama" id="edit_nama" required class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-white text-sm focus:border-electric-blue outline-none transition-colors">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Username Login</label>
                <input type="text" name="username" id="edit_username" required class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-white text-sm focus:border-electric-blue outline-none transition-colors">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Reset Password</label>
                    <input type="password" name="password" placeholder="(Kosongkan jika tetap)" class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-white text-sm focus:border-electric-blue outline-none transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Role</label>
                    <select name="role" id="edit_role" class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-white text-sm focus:border-electric-blue outline-none transition-colors">
                        <option value="kasir">Kasir</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2 pt-4">
                <button type="button" onclick="document.getElementById('modalEditUser').style.display='none'" class="flex-1 bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold py-3 rounded uppercase transition-colors">Batal</button>
                <button type="submit" class="flex-1 bg-electric-blue hover:bg-blue-700 text-white text-xs font-bold py-3 rounded uppercase transition-colors shadow-lg">Update Akun</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, nama, username, role) {
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_role').value = role;
        
        document.getElementById('formEditUser').action = '/owner/users/update/' + id;
        document.getElementById('modalEditUser').style.display = 'flex';
    }
</script>
@endsection