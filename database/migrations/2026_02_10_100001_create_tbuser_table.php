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
        if (!Schema::hasTable('tbuser')) {
            Schema::create('tbuser', function (Blueprint $table) {
                $table->string('iduser')->primary();
                $table->string('nama')->nullable();
                $table->string('status')->nullable();
                $table->longText('ket')->nullable();
                $table->string('pass')->nullable();
                $table->string('idunit')->nullable();
                $table->string('cek')->nullable();
                $table->enum('akses', ['Y', 'N'])->default('N');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('last_updated')->nullable();
                $table->enum('sync', ['Y', 'N'])->default('N');
                $table->string('pass2')->nullable();
                $table->timestamp('deleted_at')->nullable();
                
                // Additional columns for Laravel authentication compatibility
                // $table->string('email')->nullable()->unique();
                // $table->timestamp('email_verified_at')->nullable();
                // $table->rememberToken();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('tbuser');
    }
};
