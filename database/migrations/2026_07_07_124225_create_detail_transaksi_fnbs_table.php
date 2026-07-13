<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_transaksi_fnb', function (Blueprint $table) {
            $table->id('id_detail');
            $table->unsignedBigInteger('id_transaksi_fnb');
            $table->unsignedBigInteger('id_menu');
            $table->integer('qty');
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();

            $table->foreign('id_transaksi_fnb')->references('id_transaksi_fnb')->on('transaksi_fnb')->onDelete('cascade');
            $table->foreign('id_menu')->references('id_menu')->on('menu_fnb')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi_fnb');
    }
};