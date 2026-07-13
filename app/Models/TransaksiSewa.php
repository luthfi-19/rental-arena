<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiSewa extends Model
{
    protected $table = 'transaksi_sewa';
    protected $primaryKey = 'id_sewa';

    protected $fillable = [
        'id_perangkat', 'id_user', 'nama_pelanggan', 'waktu_mulai', 'waktu_selesai', 'durasi_menit', 'tarif_per_jam', 'total_biaya', 'status_sesi'
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    public function perangkat()
    {
        return $this->belongsTo(Perangkat::class, 'id_perangkat', 'id_perangkat');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}