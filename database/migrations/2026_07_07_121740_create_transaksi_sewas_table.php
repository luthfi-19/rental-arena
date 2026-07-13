<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_sewa', function (Blueprint $table) {
            $table->id('id_sewa');
            
            // Foreign Keys
            $table->unsignedBigInteger('id_perangkat');
            $table->unsignedBigInteger('id_user');
            
            $table->string('nama_pelanggan', 100)->nullable();
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai')->nullable();
            $table->integer('durasi_menit')->nullable();
            $table->decimal('tarif_per_jam', 10, 2);
            $table->decimal('total_biaya', 10, 2)->nullable();
            $table->enum('status_sesi', ['berjalan', 'selesai', 'dibatalkan'])->default('berjalan');
            $table->timestamps();

            // Relations Constraints
            $table->foreign('id_perangkat')->references('id_perangkat')->on('perangkat')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_sewa');
    }
};