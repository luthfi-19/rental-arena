<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MenuFnb extends Model
{
    protected $table = 'menu_fnb';
    protected $primaryKey = 'id_menu';
    protected $fillable = ['nama_menu', 'kategori', 'harga', 'gambar', 'status_aktif'];
}