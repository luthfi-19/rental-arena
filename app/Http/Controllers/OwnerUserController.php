<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OwnerUserController extends Controller
{
    public function index()
    {
        // Tampilkan semua user, urutkan berdasarkan role (owner di atas)
        $users = User::orderBy('role', 'desc')->get();
        return view('owner.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => 'required|in:kasir,owner'
        ]);

        User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'password' => Hash::make($request->password), // Enkripsi password
            'role' => $request->role
        ]);

        return redirect()->back()->with('success', 'Akun pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, $id_user)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username,'.$id_user.',id_user',
            'role' => 'required|in:kasir,owner'
        ]);

        $user = User::findOrFail($id_user);
        
        $data = [
            'nama' => $request->nama,
            'username' => $request->username,
            'role' => $request->role
        ];

        // Jika password diisi, berarti owner ingin mereset password user tersebut
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return redirect()->back()->with('success', 'Data akun berhasil diperbarui.');
    }

    public function destroy($id_user)
    {
        $user = User::findOrFail($id_user);

        // Mencegah owner menghapus akunnya sendiri yang sedang dipakai login
        if ($user->id_user === auth()->user()->id_user) {
            return redirect()->back()->withErrors(['Gagal: Anda tidak bisa menghapus akun Anda sendiri!']);
        }

        $user->delete();
        return redirect()->back()->with('success', 'Akun pengguna berhasil dihapus.');
    }
}