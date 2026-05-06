@extends('layouts.dashboard')

@section('title', 'Data Barang')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Data Barang</h1>
</div>

{{-- Search --}}
<form method="GET" action="{{ route('item.index') }}" class="mb-4">
    <div class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama barang, ID, merk, atau kategori..."
               class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">Cari</button>
        @if(request('search'))
            <a href="{{ route('item.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-medium rounded-lg transition">Reset</a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase text-xs">
            <tr>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">ID Barang</th>
                <th class="px-4 py-3">Nama Barang</th>
                <th class="px-4 py-3">Merk</th>
                <th class="px-4 py-3">Kategori</th>
                <th class="px-4 py-3">Stok</th>
                <th class="px-4 py-3">Satuan</th>
                <th class="px-4 py-3">Harga Jual</th>
                <th class="px-4 py-3">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($items as $index => $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750">
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $items->firstItem() + $index }}</td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 font-mono text-xs">{{ $item->barang_id }}</td>
                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200 font-medium">{{ $item->nama_barang }}</td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $item->merk ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $item->kategori ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $item->jlh_stok ?? 0 }}</td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $item->satuan ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ number_format($item->harga_jual ?? 0, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">
                        @if(($item->status ?? '') === 'active')
                            <span class="px-2 py-0.5 text-xs font-medium bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-full">Aktif</span>
                        @elseif(($item->status ?? '') === 'inactive')
                            <span class="px-2 py-0.5 text-xs font-medium bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-full">Nonaktif</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada data barang.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if ($items->hasPages())
    <div class="mt-4">
        {{ $items->withQueryString()->links() }}
    </div>
@endif
@endsection
