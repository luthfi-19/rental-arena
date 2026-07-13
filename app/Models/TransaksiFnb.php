<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TransaksiFnb extends Model
{
    protected $table = 'transaksi_fnb';
    protected $primaryKey = 'id_transaksi_fnb';
    protected $fillable = ['id_sewa', 'id_user', 'waktu_transaksi', 'total_bayar'];
    protected $casts = ['waktu_transaksi' => 'datetime'];

    public function detailTransaksi() {
        return $this->hasMany(DetailTransaksiFnb::class, 'id_transaksi_fnb', 'id_transaksi_fnb');
    }
    public function user() {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
    public function transaksiSewa() {
        return $this->belongsTo(TransaksiSewa::class, 'id_sewa', 'id_sewa');
    }
}