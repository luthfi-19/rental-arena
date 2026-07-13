<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    // Arahkan ke tabel dan primary key yang benar
    protected $table = 'users';
    protected $primaryKey = 'id_user';

    // [HYGIENE] Pastikan isi fillable HANYA kolom yang ada di database kita
    protected $fillable = [
        'nama', 
        'username', 
        'password', 
        'role'
    ];

    // Sembunyikan password saat data dipanggil ke JSON/Array
    protected $hidden = [
        'password',
    ];

    // Relasi ke transaksi sewa (opsional jika dibutuhkan nanti)
    public function transaksiSewa()
    {
        return $this->hasMany(TransaksiSewa::class, 'id_user', 'id_user');
    }
}
