<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perangkat', function (Blueprint $table) {
            $table->id('id_perangkat');
            $table->string('kode_unit', 20)->unique();
            $table->enum('jenis', ['konsol', 'joystick', 'headset']);
            $table->string('zona', 50);
            $table->integer('jam_terbang_total')->default(0);
            $table->integer('ambang_batas_servis');
            $table->enum('status', ['tersedia', 'dipakai', 'maintenance'])->default('tersedia');
            $table->date('tanggal_servis_terakhir')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perangkat');
    }
};