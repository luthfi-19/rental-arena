<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuFnb;
use App\Models\TransaksiSewa;
use App\Models\TransaksiFnb;
use App\Models\DetailTransaksiFnb;
use Illuminate\Support\Facades\DB;

class KasirFnbController extends Controller
{
    public function index()
    {
        // Ambil menu yang aktif
        $menus = MenuFnb::where('status_aktif', true)->get();
        // Ambil sesi sewa yang sedang berjalan (untuk di-link dengan F&B)
        $sesiAktif = TransaksiSewa::with('perangkat')->where('status_sesi', 'berjalan')->get();
        
        return view('kasir.fnb.index', compact('menus', 'sesiAktif'));
    }

    public function checkout(Request $request)
    {
        $cart = json_decode($request->cart_data, true);
        if (empty($cart)) return redirect()->back()->withErrors('Keranjang kosong!');

        DB::transaction(function () use ($request, $cart) {
            $transaksi = TransaksiFnb::create([
                'id_sewa' => $request->id_sewa,
                'id_user' => auth()->user()->id_user,
                'waktu_transaksi' => now(),
                'total_bayar' => 0 // Set awal 0, akan diupdate
            ]);

            $totalBayarServer = 0;

            foreach ($cart as $item) {
                // VERIFIKASI KE DATABASE
                $menu = MenuFnb::findOrFail($item['id_menu']);
                $subtotalAsli = $menu->harga * $item['qty'];
                $totalBayarServer += $subtotalAsli;

                DetailTransaksiFnb::create([
                    'id_transaksi_fnb' => $transaksi->id_transaksi_fnb,
                    'id_menu' => $menu->id_menu,
                    'qty' => $item['qty'],
                    'subtotal' => $subtotalAsli // Simpan nilai asli dari server
                ]);
            }

            // Update total akhir
            $transaksi->update(['total_bayar' => $totalBayarServer]);
        });

        return redirect()->route('kasir.fnb.index')->with('success', 'Transaksi F&B berhasil diproses.');
    }
}