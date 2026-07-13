<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_fnb', function (Blueprint $table) {
            $table->id('id_transaksi_fnb');
            
            // Relasi nullable ke sesi sewa (jika dipesan oleh pemain yang sedang main)
            $table->unsignedBigInteger('id_sewa')->nullable();
            $table->unsignedBigInteger('id_user'); // Kasir yang input
            
            $table->dateTime('waktu_transaksi');
            $table->decimal('total_bayar', 10, 2);
            $table->timestamps();

            $table->foreign('id_sewa')->references('id_sewa')->on('transaksi_sewa')->onDelete('set null');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_fnb');
    }
};