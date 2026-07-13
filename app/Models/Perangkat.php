<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perangkat extends Model
{
    protected $table = 'perangkat';
    protected $primaryKey = 'id_perangkat';

    protected $fillable = [
        'kode_unit', 'jenis', 'zona', 'jam_terbang_total', 'ambang_batas_servis', 'status', 'tanggal_servis_terakhir', 'riwayat_kerusakan'
    ];

    public function transaksiSewa()
    {
        return $this->hasMany(TransaksiSewa::class, 'id_perangkat', 'id_perangkat');
    }
}