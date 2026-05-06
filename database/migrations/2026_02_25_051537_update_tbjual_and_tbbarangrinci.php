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
        // Schema::table('tbjual', callback: function (Blueprint $table) {
        //     $table->enum('diambil_lengkap', ['Y', 'N'])->default('Y');
        // });

        Schema::table('tbbarangrinci', callback: function (Blueprint $table) {
            $table->enum('diambil', ['Y', 'N'])->default('N');
            $table->dateTime('waktu_ambil')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('tbjual', callback: function (Blueprint $table) {
        //     $table->dropColumn('diambil_lengkap');
        // });

        Schema::table('tbbarangrinci', callback: function (Blueprint $table) {
            $table->dropColumn('diambil');
            $table->dropColumn('waktu_ambil');
        });
    }
};
