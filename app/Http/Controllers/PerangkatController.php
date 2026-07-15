<?php

namespace App\Http\Controllers;

use App\Models\Perangkat;
use Illuminate\Http\Request;

class PerangkatController extends Controller
{
    /**
     * Field yang boleh diisi lewat form CRUD.
     * CATATAN: 'jam_terbang_total' SENGAJA tidak dimasukkan di sini.
     * Nilai itu murni hasil akumulasi otomatis dari sesi sewa (KasirDashboardController@selesaiSesi)
     * dan dari fitur "Selesai Servis" (MaintenanceController@selesaiServis) — tidak boleh
     * diubah manual lewat CRUD supaya perhitungan SAW & notifikasi ambang batas tetap valid.
     */
    private function rules($idPerangkat = null): array
    {
        return [
            'kode_unit' => 'required|string|max:20|unique:perangkat,kode_unit,' . $idPerangkat . ',id_perangkat',
            'jenis' => 'required|in:konsol,joystick,headset',
            'zona' => 'required|string|max:50',
            'ambang_batas_servis' => 'required|integer|min:1',
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        Perangkat::create([
            'kode_unit' => $data['kode_unit'],
            'jenis' => $data['jenis'],
            'zona' => $data['zona'],
            'ambang_batas_servis' => $data['ambang_batas_servis'],
            'jam_terbang_total' => 0, // selalu mulai dari 0, tidak menerima input manual
            'status' => 'tersedia',
            'riwayat_kerusakan' => 0,
        ]);

        return redirect()->back()->with('success', 'Unit baru berhasil ditambahkan.');
    }

    public function update(Request $request, $id_perangkat)
    {
        $perangkat = Perangkat::findOrFail($id_perangkat);

        // Guard: jangan izinkan edit metadata unit selagi sedang dipakai pelanggan,
        // supaya tidak membingungkan sesi yang sedang berjalan.
        if ($perangkat->status === 'dipakai') {
            return redirect()->back()->withErrors([
                'kode_unit' => 'Tidak bisa mengedit unit yang sedang dipakai pelanggan. Selesaikan sesi terlebih dahulu.'
            ]);
        }

        $data = $request->validate($this->rules($id_perangkat));

        // jam_terbang_total sengaja TIDAK diambil dari $request meskipun dikirim,
        // supaya tetap terkunci di level server (bukan hanya disembunyikan di UI).
        $perangkat->update([
            'kode_unit' => $data['kode_unit'],
            'jenis' => $data['jenis'],
            'zona' => $data['zona'],
            'ambang_batas_servis' => $data['ambang_batas_servis'],
        ]);

        return redirect()->back()->with('success', 'Data unit berhasil diperbarui.');
    }

    public function destroy($id_perangkat)
    {
        $perangkat = Perangkat::findOrFail($id_perangkat);

        if ($perangkat->status === 'dipakai') {
            return redirect()->back()->withErrors([
                'kode_unit' => 'Tidak bisa menghapus unit yang sedang dipakai pelanggan.'
            ]);
        }

        // Guard: unit yang sudah punya histori transaksi sewa tidak boleh dihapus,
        // karena FK 'id_perangkat' di transaksi_sewa pakai onDelete('cascade') —
        // menghapus unit akan ikut menghapus seluruh histori transaksi & pendapatan unit ini.
        if ($perangkat->transaksiSewa()->exists()) {
            return redirect()->back()->withErrors([
                'kode_unit' => 'Unit ini sudah memiliki histori transaksi dan tidak bisa dihapus (akan merusak data laporan). '
                    . 'Jika unit sudah tidak dipakai secara permanen, ubah statusnya ke "maintenance" saja.'
            ]);
        }

        $perangkat->delete();

        return redirect()->back()->with('success', 'Unit berhasil dihapus.');
    }
}