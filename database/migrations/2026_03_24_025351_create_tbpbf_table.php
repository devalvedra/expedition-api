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
        Schema::create('tbpbf', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pbf')->unique();
            $table->string('nama_pbf');
            $table->string('alamat');
            $table->float('lat', 7);
            $table->float('lng', 7);
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbpbf');
    }
};
