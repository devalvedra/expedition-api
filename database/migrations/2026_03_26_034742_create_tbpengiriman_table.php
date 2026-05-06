<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbpengiriman', function (Blueprint $table) {
            $table->string('no_invoice')->primary();
            $table->string('kode_pbf');
            $table->integer('jumlah_barang_besar');
            $table->integer('jumlah_barang_sedang');
            $table->integer('jumlah_barang_kecil');
            $table->string('status');
            $table->string('no_kendaraan')->nullable();
            $table->timestamps();


            $table->foreign('kode_pbf')->references('kode_pbf')->on('tbpbf')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbpengiriman');
    }
};
