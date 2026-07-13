<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksiFnb extends Model
{
    protected $table = 'detail_transaksi_fnb';
    protected $primaryKey = 'id_detail';
    protected $fillable = ['id_transaksi_fnb', 'id_menu', 'qty', 'subtotal'];

    public function menu() {
        return $this->belongsTo(MenuFnb::class, 'id_menu', 'id_menu');
    }
}