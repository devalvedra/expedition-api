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
        Schema::create('tbhistorypengiriman', function (Blueprint $table) {
            $table->id();
            $table->string('no_invoice');
            $table->string('kode_pbf');
            $table->string('status');
            $table->string('username');
            $table->timestamps();

            $table->foreign('no_invoice')->references('no_invoice')->on('tbpengiriman')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbhistorypengiriman');
    }
};
