@extends('layouts.dashboard')

@section('title', 'Data PBF')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Data PBF</h1>
    <a href="{{ route('pbf.create') }}"
       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
        + Tambah PBF
    </a>
</div>

{{-- Search --}}
<form method="GET" action="{{ route('pbf.index') }}" class="mb-4">
    <div class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama PBF atau alamat..."
               class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">Cari</button>
        @if(request('search'))
            <a href="{{ route('pbf.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-medium rounded-lg transition">Reset</a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase text-xs">
            <tr>
                <th class="px-6 py-3">#</th>
                <th class="px-6 py-3">Kode PBF</th>
                <th class="px-6 py-3">Nama PBF</th>
                <th class="px-6 py-3">Alamat</th>
                <th class="px-6 py-3">Latitude</th>
                <th class="px-6 py-3">Longitude</th>
                <th class="px-6 py-3">Image</th>
                <th class="px-6 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($pbfs as $index => $pbf)
                <tr>
                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $pbfs->firstItem() + $index }}</td>
                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300 font-mono">{{ $pbf->kode_pbf }}</td>
                    <td class="px-6 py-4 text-gray-800 dark:text-gray-200 font-medium">{{ $pbf->nama_pbf }}</td>
                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $pbf->alamat }}</td>
                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $pbf->lat }}</td>
                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $pbf->lng }}</td>
                    <td class="px-6 py-4">
                        @if($pbf->image_path)
                            <img src="{{ asset('storage/' . $pbf->image_path) }}" alt="{{ $pbf->nama_pbf }}" class="h-10 w-10 object-cover rounded">
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="{{ route('pbf.edit', $pbf->id) }}"
                               class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium rounded transition">Edit</a>
                            <form method="POST" action="{{ route('pbf.destroy', $pbf->id) }}" class="inline"
                                  onsubmit="return confirm('Yakin ingin menghapus PBF ini?')">
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
                    <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada data PBF.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if ($pbfs->hasPages())
    <div class="mt-4">
        {{ $pbfs->withQueryString()->links() }}
    </div>
@endif
@endsection
