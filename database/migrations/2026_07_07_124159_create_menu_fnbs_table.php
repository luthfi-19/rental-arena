<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_fnb', function (Blueprint $table) {
            $table->id('id_menu');
            $table->string('nama_menu', 100);
            $table->enum('kategori', ['makanan', 'minuman']);
            $table->decimal('harga', 10, 2);
            $table->string('gambar')->nullable(); // Simpan path gambar
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_fnb');
    }
};