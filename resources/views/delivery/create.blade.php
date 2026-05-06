@extends('layouts.dashboard')

@section('title', 'Tambah Delivery')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Tambah Delivery</h1>
        <a href="{{ route('delivery.index') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">&larr; Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <form method="POST" action="{{ route('delivery.store') }}">
            @csrf

            <div class="mb-4">
                <label for="no_invoice" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. Invoice</label>
                <input type="text" name="no_invoice" id="no_invoice" value="{{ old('no_invoice', $nextInvoice) }}" required readonly
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-mono cursor-not-allowed">
                @error('no_invoice')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="kode_pbf" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">PBF</label>
                <select name="kode_pbf" id="kode_pbf" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">-- Pilih PBF --</option>
                    @foreach ($pbfs as $pbf)
                        <option value="{{ $pbf->kode_pbf }}" {{ old('kode_pbf') === $pbf->kode_pbf ? 'selected' : '' }}>
                            {{ $pbf->nama_pbf }} ({{ $pbf->kode_pbf }})
                        </option>
                    @endforeach
                </select>
                @error('kode_pbf')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <label for="jumlah_barang_besar" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah Besar</label>
                    <input type="number" name="jumlah_barang_besar" id="jumlah_barang_besar" value="{{ old('jumlah_barang_besar', 0) }}" min="0" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @error('jumlah_barang_besar')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="jumlah_barang_sedang" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah Sedang</label>
                    <input type="number" name="jumlah_barang_sedang" id="jumlah_barang_sedang" value="{{ old('jumlah_barang_sedang', 0) }}" min="0" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @error('jumlah_barang_sedang')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="jumlah_barang_kecil" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah Kecil</label>
                    <input type="number" name="jumlah_barang_kecil" id="jumlah_barang_kecil" value="{{ old('jumlah_barang_kecil', 0) }}" min="0" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @error('jumlah_barang_kecil')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 mb-6">
                <div>
                    <label for="no_kendaraan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. Kendaraan <span class="text-gray-400">(opsional)</span></label>
                    <input type="text" name="no_kendaraan" id="no_kendaraan" value="{{ old('no_kendaraan') }}"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @error('no_kendaraan')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">Simpan</button>
                <a href="{{ route('delivery.index') }}"
                   class="px-6 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg transition">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
