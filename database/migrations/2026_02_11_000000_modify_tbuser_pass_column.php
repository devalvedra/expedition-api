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
        Schema::table('tbuser', function (Blueprint $table) {
            // Modify pass column to be larger for bcrypt hashes (60 chars minimum)
            $table->string('pass', 60)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbuser', function (Blueprint $table) {
            // Revert to original size
            $table->string('pass')->nullable()->change();
        });
    }
};
