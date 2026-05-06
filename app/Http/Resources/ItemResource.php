<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode' => $this->kode,
            'barang_id' => $this->barang_id,
            'kodeobatres' => $this->kodeobatres,
            'kodekemasan' => $this->kodekemasan,
            'nm_barang_resmi' => $this->nm_barang_resmi,
            'kodeobatpom' => $this->kodeobatpom,
            'nama_barang' => $this->nama_barang,
            'merk' => $this->merk,
            'komposisi' => $this->komposisi,
            'indikasi' => $this->indikasi,
            'warna' => $this->warna,
            'golongan' => $this->golongan,
            'jenis' => $this->jenis,
            'harga_jual' => $this->harga_jual,
            'disc' => $this->disc,
            'jlh_stok' => $this->jlh_stok,
            'expired' => $this->expired?->format('Y-m-d'),
            'no_batch' => $this->no_batch,
            'modedisc' => $this->modedisc,
            'harga_beli' => $this->harga_beli,
            'netto' => $this->netto,
            'harga_bebas' => $this->harga_bebas,
            'harga_resep' => $this->harga_resep,
            'harga_bebas_besar' => $this->harga_bebas_besar,
            'harga_resep_besar' => $this->harga_resep_besar,
            'harga_distribusi' => $this->harga_distribusi,
            'harga_distribusi_cash' => $this->harga_distribusi_cash,
            'harga_panel' => $this->harga_panel,
            'totalpcs' => $this->totalpcs,
            'satuan' => $this->satuan,
            'qty' => $this->qty,
            'cat' => $this->cat,
            'ket' => $this->ket,
            'efek_samping' => $this->efek_samping,
            'gambar' => $this->gambar,
            'sqty' => $this->sqty,
            'norak' => $this->norak,
            'kategori' => $this->kategori,
            'dosis' => $this->dosis,
            'ppn' => $this->ppn,
            'jasa' => $this->jasa,
            'aktif' => $this->aktif,
            'pabrik' => $this->pabrik,
            'supplier' => $this->supplier,
            'serving' => $this->serving,
            'barcode' => $this->barcode,
            'tgl_update_hrg' => $this->tgl_update_hrg?->format('Y-m-d H:i:s'),
            'sync' => $this->sync,
            'stokmin' => $this->stokmin,
            'image' => $this->image,
            'status' => $this->status,
            'hpp' => $this->hpp,
            'bonus' => $this->bonus,
            'kode_lain' => $this->kode_lain,
            'nama_lain' => $this->nama_lain,
            'distribusi' => $this->distribusi,
            'berat' => $this->berat,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'last_updated' => $this->last_updated?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
        ];
    }
}
