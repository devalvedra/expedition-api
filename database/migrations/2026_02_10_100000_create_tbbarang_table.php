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
        if (!Schema::hasTable('tbbarang')) {
            Schema::create('tbbarang', function (Blueprint $table) {
                $table->id();
                $table->integer('kode')->nullable();
                $table->string('barang_id')->unique();
                $table->string('kodeobatres')->nullable();
                $table->text('kodekemasan')->nullable();
                $table->text('nm_barang_resmi')->nullable();
                $table->text('kodeobatpom')->nullable();
                $table->string('nama_barang');
                $table->string('merk')->nullable();
                $table->string('komposisi')->nullable();
                $table->string('indikasi')->nullable();
                $table->string('warna')->nullable();
                $table->string('golongan')->nullable();
                $table->string('jenis')->nullable();
                $table->double('harga_jual')->nullable();
                $table->double('disc')->nullable();
                $table->double('jlh_stok')->default(0);
                $table->date('expired')->nullable();
                $table->string('no_batch')->nullable();
                $table->char('modedisc', 1)->nullable();
                $table->double('harga_beli')->nullable();
                $table->double('netto')->nullable();
                $table->double('harga_bebas')->nullable();
                $table->double('harga_resep')->nullable();
                $table->double('harga_bebas_besar')->nullable();
                $table->double('harga_resep_besar')->nullable();
                $table->double('harga_distribusi')->nullable();
                $table->double('harga_distribusi_cash')->nullable();
                $table->double('harga_panel')->nullable();
                $table->bigInteger('totalpcs')->nullable();
                $table->string('satuan')->nullable();
                $table->integer('qty')->nullable();
                $table->string('cat')->nullable();
                $table->text('ket')->nullable();
                $table->text('efek_samping')->nullable();
                $table->text('gambar')->nullable();
                $table->string('sqty')->nullable();
                $table->string('norak')->nullable();
                $table->string('kategori')->nullable();
                $table->string('dosis')->nullable();
                $table->string('ppn')->nullable();
                $table->string('jasa')->nullable();
                $table->string('aktif')->nullable();
                $table->string('pabrik')->nullable();
                $table->string('supplier')->nullable();
                $table->string('serving')->nullable();
                $table->text('barcode')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('last_updated')->nullable();
                $table->timestamp('tgl_update_hrg')->nullable();
                $table->enum('sync', ['Y', 'N'])->default('N');
                $table->integer('stokmin')->default(0);
                $table->text('image')->nullable();
                $table->enum('status', ['active', 'inactive'])->nullable();
                $table->double('hpp')->nullable();
                $table->double('bonus')->nullable();
                $table->string('kode_lain')->nullable();
                $table->string('nama_lain')->nullable();
                $table->string('distribusi')->nullable();
                $table->double('berat')->nullable();
                $table->timestamp('deleted_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('tbbarang');
    }
};
