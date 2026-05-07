@extends('layouts.dashboard')

@section('title', 'Data Delivery')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Data Delivery</h1>
    <a href="{{ route('delivery.create') }}"
       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
        + Tambah Delivery
    </a>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('delivery.index') }}" class="mb-4">
    <div class="flex gap-2 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. invoice, kode PBF, kendaraan..."
               class="flex-1 min-w-48 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <select name="status"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">Semua Status</option>
            @foreach (\App\Models\DELIVERY_STATUS::cases() as $case)
                <option value="{{ $case->value }}" {{ request('status') === $case->value ? 'selected' : '' }}>
                    {{ $case->label() }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">Cari</button>
        @if(request('search') || request('status'))
            <a href="{{ route('delivery.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-medium rounded-lg transition">Reset</a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase text-xs">
            <tr>
                <th class="px-4 py-3">No. Invoice</th>
                <th class="px-4 py-3">PBF</th>
                <th class="px-4 py-3 text-center">Besar</th>
                <th class="px-4 py-3 text-center">Sedang</th>
                <th class="px-4 py-3 text-center">Kecil</th>
                <th class="px-4 py-3">No. Kendaraan</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($deliveries as $delivery)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750">
                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200 font-mono text-xs font-medium">{{ $delivery->no_invoice }}</td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                        {{ $delivery->pbf?->nama_pbf ?? $delivery->kode_pbf }}
                    </td>
                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $delivery->jumlah_barang_besar }}</td>
                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $delivery->jumlah_barang_sedang }}</td>
                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $delivery->jumlah_barang_kecil }}</td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $delivery->no_kendaraan ?? '-' }}</td>
                    <td class="px-4 py-3">
                        @php
                            $statusClasses = [
                                'PENDING'        => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
                                'DIMUAT'         => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300',
                                'MENUNGGU_SUPIR' => 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300',
                                'SEDANG_DIKIRIM' => 'bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-300',
                                'SELESAI'        => 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300',
                            ];
                            $class = $statusClasses[$delivery->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $class }}">
                            {{ \App\Models\DELIVERY_STATUS::tryFrom($delivery->status)?->label() ?? $delivery->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="{{ route('delivery.show', $delivery->no_invoice) }}"
                               class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded transition">Detail</a>
                            <a href="{{ route('delivery.edit', $delivery->no_invoice) }}"
                               class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium rounded transition">Edit</a>
                            <form method="POST" action="{{ route('delivery.destroy', $delivery->no_invoice) }}" class="inline"
                                  onsubmit="return confirm('Yakin ingin menghapus delivery ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded transition">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada data delivery.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if ($deliveries->hasPages())
    <div class="mt-4">
        {{ $deliveries->withQueryString()->links() }}
    </div>
@endif
@endsection
