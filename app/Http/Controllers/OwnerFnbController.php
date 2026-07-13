<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuFnb;
use Illuminate\Support\Facades\Storage;

class OwnerFnbController extends Controller
{
    public function index()
    {
        $menus = MenuFnb::orderBy('kategori')->get();
        return view('owner.fnb.index', compact('menus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_menu' => 'required|string|max:100',
            'kategori' => 'required|in:makanan,minuman',
            'harga' => 'required|numeric',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $data = $request->except('gambar');
        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('menu_fnb', 'public');
        }

        MenuFnb::create($data);
        return redirect()->back()->with('success', 'Menu berhasil ditambahkan.');
    }

    public function toggleStatus($id_menu)
    {
        $menu = MenuFnb::findOrFail($id_menu);
        $menu->update(['status_aktif' => !$menu->status_aktif]);
        return redirect()->back()->with('success', 'Status menu diperbarui.');
    }

    public function destroy($id_menu)
    {
        $menu = MenuFnb::findOrFail($id_menu);
        if ($menu->gambar) {
            Storage::disk('public')->delete($menu->gambar);
        }
        $menu->delete();
        return redirect()->back()->with('success', 'Menu dihapus.');
    }

    public function update(Request $request, $id_menu)
    {
        $request->validate([
            'nama_menu' => 'required|string|max:100',
            'kategori' => 'required|in:makanan,minuman',
            'harga' => 'required|numeric',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $menu = MenuFnb::findOrFail($id_menu);
        $data = $request->except('gambar');

        // Cek jika owner mengunggah gambar baru
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama dari storage jika ada
            if ($menu->gambar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($menu->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('menu_fnb', 'public');
        }

        $menu->update($data);
        return redirect()->back()->with('success', 'Data menu berhasil diperbarui.');
    }
}