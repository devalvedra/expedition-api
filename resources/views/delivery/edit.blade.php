@extends('layouts.dashboard')

@section('title', 'Edit Delivery')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Delivery</h1>
        <a href="{{ route('delivery.index') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">&larr; Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <form method="POST" action="{{ route('delivery.update', $delivery->no_invoice) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. Invoice</label>
                <input type="text" value="{{ $delivery->no_invoice }}" disabled
                       class="w-full px-4 py-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 text-sm cursor-not-allowed">
                <p class="mt-1 text-xs text-gray-400">No. invoice tidak dapat diubah.</p>
            </div>

            <div class="mb-4">
                <label for="kode_pbf" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">PBF</label>
                <select name="kode_pbf" id="kode_pbf" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">-- Pilih PBF --</option>
                    @foreach ($pbfs as $pbf)
                        <option value="{{ $pbf->kode_pbf }}" {{ old('kode_pbf', $delivery->kode_pbf) === $pbf->kode_pbf ? 'selected' : '' }}>
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
                    <input type="number" name="jumlah_barang_besar" id="jumlah_barang_besar" value="{{ old('jumlah_barang_besar', $delivery->jumlah_barang_besar) }}" min="0" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @error('jumlah_barang_besar')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="jumlah_barang_sedang" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah Sedang</label>
                    <input type="number" name="jumlah_barang_sedang" id="jumlah_barang_sedang" value="{{ old('jumlah_barang_sedang', $delivery->jumlah_barang_sedang) }}" min="0" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @error('jumlah_barang_sedang')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="jumlah_barang_kecil" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah Kecil</label>
                    <input type="number" name="jumlah_barang_kecil" id="jumlah_barang_kecil" value="{{ old('jumlah_barang_kecil', $delivery->jumlah_barang_kecil) }}" min="0" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @error('jumlah_barang_kecil')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select name="status" id="status" required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach ($statuses as $s)
                            <option value="{{ $s->value }}" {{ old('status', $delivery->status) === $s->value ? 'selected' : '' }}>
                                {{ $s->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="no_kendaraan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. Kendaraan <span class="text-gray-400">(opsional)</span></label>
                    <input type="text" name="no_kendaraan" id="no_kendaraan" value="{{ old('no_kendaraan', $delivery->no_kendaraan) }}"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @error('no_kendaraan')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">Perbarui</button>
                <a href="{{ route('delivery.index') }}"
                   class="px-6 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg transition">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
