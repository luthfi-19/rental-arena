<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perangkat', function (Blueprint $table) {
            $table->integer('riwayat_kerusakan')->default(0)->after('tanggal_servis_terakhir');
        });
    }

    public function down(): void
    {
        Schema::table('perangkat', function (Blueprint $table) {
            $table->dropColumn('riwayat_kerusakan');
        });
    }
};