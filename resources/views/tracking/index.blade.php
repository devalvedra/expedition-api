<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lacak Pengiriman - {{ config('app.name', 'Eshia') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen flex flex-col">

    {{-- Header --}}
    <header class="bg-gray-800 dark:bg-gray-950 shadow-lg">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="text-xl font-bold text-white tracking-tight">
                {{ config('app.name', 'Eshia') }}
            </a>
            <span class="text-sm text-gray-400">Lacak Pengiriman</span>
        </div>
    </header>

    {{-- Main --}}
    <main class="flex-1 flex items-center justify-center py-16 px-4">
        <div class="w-full max-w-md">

            {{-- Icon + Heading --}}
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-blue-100 dark:bg-blue-900/40 mb-4">
                    <svg class="w-10 h-10 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l1.5 1.5M13 16l1.5 1.5M13 16H9m4-10h2.586a1 1 0 01.707.293l3.414 3.414A1 1 0 0120 10.414V16a1 1 0 01-1 1h-1"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Lacak Pengiriman</h1>
                <p class="mt-2 text-gray-500 dark:text-gray-400 text-sm">Masukkan nomor invoice untuk melihat status pengiriman Anda.</p>
            </div>

            {{-- Search Form --}}
            <form action="#" method="GET"
                  onsubmit="handleSearch(event, this)"
                  class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-6">

                <label for="invoice" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Nomor Invoice
                </label>
                <div class="flex gap-3">
                    <input id="invoice"
                           name="invoice"
                           type="text"
                           placeholder="Contoh: INV-2026-001"
                           autocomplete="off"
                           autofocus
                           required
                           class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700
                                  text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500
                                  px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                  focus:border-transparent transition">
                    <button type="submit"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold
                                   rounded-lg transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Lacak
                    </button>
                </div>
                <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
                    Nomor invoice dapat ditemukan pada dokumen pengiriman Anda.
                </p>
            </form>

        </div>
    </main>

    {{-- Footer --}}
    <footer class="py-4 text-center text-xs text-gray-400 dark:text-gray-600">
        &copy; {{ date('Y') }} {{ config('app.name', 'Eshia') }}. All rights reserved.
    </footer>

    <script>
    const TRACKING_BASE = '{{ rtrim(route('tracking.index'), '/') }}';
    function handleSearch(e) {
        e.preventDefault();
        const invoice = document.getElementById('invoice').value.trim();
        if (!invoice) return;
        window.location.href = TRACKING_BASE + '/' + encodeURIComponent(invoice);
    }
    </script>
</body>
</html>
