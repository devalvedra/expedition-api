<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        @if(!empty($notFound))
            Invoice Tidak Ditemukan
        @else
            Lacak {{ $delivery->no_invoice }}
        @endif
        - {{ config('app.name', 'Eshia') }}
    </title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        /* Smooth timeline connector */
        .timeline-connector {
            position: absolute;
            left: 7px;
            top: 1.5rem;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, #d1d5db, #e5e7eb);
        }
        .dark .timeline-connector {
            background: linear-gradient(to bottom, #374151, #1f2937);
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen flex flex-col">

    {{-- Header --}}
    <header class="bg-gray-800 dark:bg-gray-950 shadow-lg">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="text-xl font-bold text-white tracking-tight">
                {{ config('app.name', 'Eshia') }}
            </a>
            <a href="{{ route('tracking.index') }}"
               class="text-sm text-gray-400 hover:text-white transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Lacak Invoice Lain
            </a>
        </div>
    </header>

    <main class="flex-1 max-w-6xl mx-auto w-full px-4 py-10">

        @if(!empty($notFound))
        {{-- ═══ NOT FOUND STATE ═══════════════════════════════════════ --}}
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-red-100 dark:bg-red-900/30 mb-6">
                <svg class="w-12 h-12 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l1.5 1.5M13 16l1.5 1.5M13 16H9m4-10h2.586a1 1 0 01.707.293l3.414 3.414A1 1 0 0120 10.414V16a1 1 0 01-1 1h-1"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Invoice Tidak Ditemukan</h1>
            <p class="text-gray-500 dark:text-gray-400 mb-1">Nomor invoice</p>
            <p class="font-mono text-lg font-semibold text-red-600 dark:text-red-400 mb-6 px-4 py-2 bg-red-50 dark:bg-red-900/20 rounded-lg">
                {{ $invoice }}
            </p>
            <p class="text-sm text-gray-400 dark:text-gray-500 max-w-sm mb-8">
                Invoice tersebut tidak terdaftar dalam sistem. Pastikan nomor invoice yang Anda masukkan sudah benar.
            </p>
            <a href="{{ route('tracking.index') }}"
               class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                Coba Invoice Lain
            </a>
        </div>

        @else
        {{-- ═══ FOUND STATE ════════════════════════════════════════════ --}}

        @php
            $statusConfig = [
                'PENDING'        => ['banner' => 'bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-600',           'badge' => 'bg-gray-500 text-white',                     'icon' => '⏳', 'label' => 'Pending'],
                'DIMUAT'         => ['banner' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-300 dark:border-yellow-700', 'badge' => 'bg-yellow-500 text-white',                   'icon' => '📦', 'label' => 'Dimuat'],
                'MENUNGGU_SUPIR' => ['banner' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700',         'badge' => 'bg-blue-500 text-white',                     'icon' => '🕐', 'label' => 'Menunggu Supir'],
                'SEDANG_DIKIRIM' => ['banner' => 'bg-orange-50 dark:bg-orange-900/20 border-orange-300 dark:border-orange-700', 'badge' => 'bg-orange-500 text-white',                   'icon' => '🚚', 'label' => 'Sedang Dikirim'],
                'SELESAI'        => ['banner' => 'bg-green-50 dark:bg-green-900/20 border-green-300 dark:border-green-700',     'badge' => 'bg-green-600 text-white',                    'icon' => '✅', 'label' => 'Selesai'],
            ];
            $sc = $statusConfig[$delivery->status] ?? ['banner' => 'bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-600', 'badge' => 'bg-gray-500 text-white', 'icon' => '•', 'label' => $delivery->status];

            $dotColor = [
                'SELESAI'        => 'bg-green-500',
                'SEDANG_DIKIRIM' => 'bg-orange-500',
                'MENUNGGU_SUPIR' => 'bg-blue-500',
                'DIMUAT'         => 'bg-yellow-500',
                'PENDING'        => 'bg-gray-400',
                // Legacy strings
                'Sampai Tujuan'      => 'bg-green-500',
                'Selesai'            => 'bg-green-500',
                'Dikirim'            => 'bg-orange-500',
                'Sedang Dikirim'     => 'bg-orange-500',
                'Menunggu Supir'     => 'bg-blue-500',
                'Menunggu Staff PBF' => 'bg-sky-400',
                'Dimuat'             => 'bg-yellow-500',
            ];

            $statusLabels = collect(\App\Models\DELIVERY_STATUS::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])->all();
            $isInTransit   = $delivery->status === \App\Models\DELIVERY_STATUS::IN_TRANSIT->value;
            $isCompleted   = $delivery->status === \App\Models\DELIVERY_STATUS::COMPLETED->value;
            $hasPbfCoords  = $delivery->pbf && $delivery->pbf->lat && $delivery->pbf->lng;
            $showMap       = $isInTransit || $hasPbfCoords;
        @endphp

        {{-- ── Page heading ────────────────────────────────────────────── --}}
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Nomor Invoice</p>
            <h1 class="text-2xl font-bold font-mono text-gray-800 dark:text-white">{{ $delivery->no_invoice }}</h1>
        </div>

        {{-- ── Status banner ───────────────────────────────────────────── --}}
        <div class="border-2 rounded-xl p-5 mb-6 flex flex-wrap items-center gap-4 {{ $sc['banner'] }}">
            <span class="text-4xl leading-none">{{ $sc['icon'] }}</span>
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Status Saat Ini</p>
                <span class="inline-block px-4 py-1.5 rounded-full text-sm font-bold tracking-wide {{ $sc['badge'] }}">
                    {{ $sc['label'] }}
                </span>
            </div>
            <div class="ml-auto text-right">
                <p class="text-xs text-gray-400 dark:text-gray-500">Terakhir diperbarui</p>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $delivery->updated_at->format('d M Y, H:i') }}</p>
            </div>
        </div>

        {{-- ── Delivery Info + Timeline + Map ─────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left column: Info + Timeline --}}
            <div class="lg:col-span-1 space-y-5">

                {{-- Delivery info card --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 space-y-3">
                    <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">Detail Pengiriman</h2>
                    <hr class="border-gray-100 dark:border-gray-700">
                    <div class="space-y-2.5 text-sm">
                        <div class="flex justify-between gap-2">
                            <span class="text-gray-400 dark:text-gray-500 shrink-0">PBF</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200 text-right">
                                {{ $delivery->pbf?->nama_pbf ?? $delivery->kode_pbf }}
                            </span>
                        </div>
                        @if($delivery->no_kendaraan)
                        <div class="flex justify-between gap-2">
                            <span class="text-gray-400 dark:text-gray-500 shrink-0">Kendaraan</span>
                            <span class="font-mono font-medium text-gray-800 dark:text-gray-200">{{ $delivery->no_kendaraan }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between gap-2">
                            <span class="text-gray-400 dark:text-gray-500 shrink-0">Paket</span>
                            <span class="text-gray-700 dark:text-gray-300 text-right">
                                @if($delivery->jumlah_barang_besar > 0)
                                    {{ $delivery->jumlah_barang_besar }} Besar
                                @endif
                                @if($delivery->jumlah_barang_sedang > 0)
                                    {{ $delivery->jumlah_barang_sedang }} Sedang
                                @endif
                                @if($delivery->jumlah_barang_kecil > 0)
                                    {{ $delivery->jumlah_barang_kecil }} Kecil
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between gap-2">
                            <span class="text-gray-400 dark:text-gray-500 shrink-0">Dibuat</span>
                            <span class="text-gray-600 dark:text-gray-400 text-right">{{ $delivery->created_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Timeline card --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                    <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-5">Riwayat Status</h2>

                    @if($history->isEmpty())
                        <div class="py-6 text-center">
                            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-sm text-gray-400 dark:text-gray-500 italic">Belum ada riwayat pengiriman.</p>
                        </div>
                    @else
                        <ol class="relative ml-3">
                            @foreach($history as $h)
                            @php
                                $dc = $dotColor[$h->status] ?? 'bg-gray-400';
                                $label = $statusLabels[$h->status] ?? $h->status;
                            @endphp
                            <li class="relative pb-6 last:pb-0">
                                {{-- Vertical connector line --}}
                                @if(!$loop->last)
                                    <div class="absolute left-[7px] top-4 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                                @endif

                                {{-- Status dot --}}
                                @if($loop->first)
                                    {{-- Most recent: pulsing ring --}}
                                    <div class="absolute left-0 top-1.5">
                                        <span class="relative flex h-3.5 w-3.5">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $dc }} opacity-60"></span>
                                            <span class="relative inline-flex rounded-full h-3.5 w-3.5 {{ $dc }} border-2 border-white dark:border-gray-800 shadow-sm"></span>
                                        </span>
                                    </div>
                                @else
                                    <div class="absolute left-0 top-1.5 w-3.5 h-3.5 rounded-full {{ $dc }} border-2 border-white dark:border-gray-800 shadow-sm"></div>
                                @endif

                                {{-- Content --}}
                                <div class="pl-6">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 leading-snug">{{ $label }}</p>
                                    @if($h->username)
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">oleh {{ $h->username }}</p>
                                    @endif
                                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $h->created_at->format('d M Y, H:i') }}</p>
                                </div>
                            </li>
                            @endforeach
                        </ol>
                    @endif
                </div>

            </div>

            {{-- Right column: Google Map --}}
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden" style="min-height: 480px; height: 100%;">

                    <div class="px-5 pt-4 pb-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">Lokasi Pengiriman</h2>
                        @if($isInTransit && $delivery->no_kendaraan)
                            <div class="flex items-center gap-1.5">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                </span>
                                <span class="text-xs font-bold text-green-600 dark:text-green-400">LIVE</span>
                            </div>
                        @endif
                    </div>

                    @if($showMap)
                        {{-- Map canvas --}}
                        <div id="tracking-map" style="height: calc(100% - 48px); min-height: 432px; position: relative;"></div>

                        {{-- Loading overlay --}}
                        <div id="map-loading"
                             style="position:absolute; inset:0; z-index:10; display:flex; flex-direction:column; align-items:center; justify-content:center;"
                             class="bg-gray-100 dark:bg-gray-800">
                            <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Memuat peta...</p>
                        </div>

                        @if($isInTransit && $delivery->no_kendaraan)
                            {{-- No-signal overlay for live mode --}}
                            <div id="no-signal"
                                 style="position:absolute; inset:0; z-index:9; display:none; flex-direction:column; align-items:center; justify-content:center;"
                                 class="bg-gray-100/90 dark:bg-gray-800/90 text-center">
                                <span class="text-5xl mb-3">📡</span>
                                <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Menunggu data lokasi...</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Kendaraan belum mengirim posisi</p>
                            </div>
                        @endif
                    @else
                        {{-- No map available --}}
                        <div class="flex flex-col items-center justify-center py-20 px-6 text-center" style="min-height: 432px;">
                            <span class="text-6xl block mb-4">🗺️</span>
                            <p class="text-base font-semibold text-gray-600 dark:text-gray-300">Peta Belum Tersedia</p>
                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-2 max-w-xs leading-relaxed">
                                Informasi lokasi akan tersedia ketika paket sedang dalam pengiriman atau data lokasi PBF sudah dikonfigurasi.
                            </p>
                        </div>
                    @endif

                </div>
            </div>

        </div>{{-- end grid --}}
        @endif{{-- end found --}}
    </main>

    {{-- Footer --}}
    <footer class="py-4 text-center text-xs text-gray-400 dark:text-gray-600">
        &copy; {{ date('Y') }} {{ config('app.name', 'Eshia') }}. All rights reserved.
    </footer>

    {{-- ── SDKs & Map Scripts (only when delivery is found and map is shown) ── --}}
    @if(empty($notFound) && $showMap)

    @if($isInTransit && $delivery->no_kendaraan)
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-database-compat.js"></script>
    @endif

    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=geometry&callback=onMapsReady"
        defer async>
    </script>

    <script>
    // ── Server-rendered config ────────────────────────────────
    const GMAPS_KEY   = @json(config('services.google_maps.api_key'));
    const DROP_LAT    = @json((float) ($delivery->pbf?->lat ?? 0));
    const DROP_LNG    = @json((float) ($delivery->pbf?->lng ?? 0));
    const DROP_NAME   = @json($delivery->pbf?->nama_pbf ?? $delivery->kode_pbf);
    const IS_TRANSIT  = @json($isInTransit);
    const IS_COMPLETE = @json($isCompleted);
    const VEHICLE_NO  = @json($delivery->no_kendaraan ?? '');
    const INVOICE_NO  = @json($delivery->no_invoice);

    @if($isInTransit && $delivery->no_kendaraan)
    const FIREBASE_CONFIG = {
        apiKey:            @json(config('firebase.web_api_key')),
        authDomain:        @json(config('firebase.auth_domain')),
        databaseURL:       @json(config('firebase.database_url')),
        projectId:         @json(config('firebase.project_id')),
        storageBucket:     @json(config('firebase.storage_bucket')),
        messagingSenderId: @json(config('firebase.messaging_sender_id')),
        appId:             @json(config('firebase.app_id')),
    };
    @endif

    // ── State ─────────────────────────────────────────────────
    let _map = null, _truckMarker = null, _destMarker = null;
    let _directionsService = null, _directionsRenderer = null;
    let _lastRouteOrigin = null, _lastRouteTime = 0;
    const ROUTE_MIN_DIST_M = 50;
    const ROUTE_MIN_MS     = 120000;

    // ── Called by Google Maps SDK (callback) ──────────────────
    function onMapsReady() {
        const mapEl = document.getElementById('tracking-map');
        if (!mapEl) return;

        const center = (DROP_LAT && DROP_LNG)
            ? { lat: DROP_LAT, lng: DROP_LNG }
            : { lat: -6.9175, lng: 107.6191 }; // Bandung fallback

        _map = new google.maps.Map(mapEl, {
            zoom: 14,
            center: center,
            mapTypeId: 'roadmap',
            zoomControl: true,
            streetViewControl: false,
            mapTypeControl: false,
            fullscreenControl: true,
        });

        // Hide loading overlay
        const loading = document.getElementById('map-loading');
        if (loading) loading.style.display = 'none';

        // Place destination / PBF marker
        if (DROP_LAT && DROP_LNG) {
            _destMarker = new google.maps.Marker({
                position: { lat: DROP_LAT, lng: DROP_LNG },
                map: _map,
                title: DROP_NAME,
                icon: {
                    path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
                    scale: 8,
                    fillColor:   IS_COMPLETE ? '#22c55e' : '#3B82F6',
                    fillOpacity: 1,
                    strokeColor: '#ffffff',
                    strokeWeight: 2,
                },
                label: {
                    text: DROP_NAME.length > 20 ? DROP_NAME.substring(0, 20) + '…' : DROP_NAME,
                    color: '#1f2937',
                    fontSize: '11px',
                    fontWeight: 'bold',
                },
            });

            new google.maps.InfoWindow({
                content: `<div style="font-family:sans-serif;padding:4px 6px;">
                              <strong>${DROP_NAME}</strong><br>
                              <span style="font-size:12px;color:#6b7280;">Tujuan Pengiriman</span>
                          </div>`,
            }).open(_map, _destMarker);
        }

        // If in transit: connect Firebase for live truck location
        @if($isInTransit && $delivery->no_kendaraan)
        if (!FIREBASE_CONFIG.databaseURL) {
            console.warn('FIREBASE_DATABASE_URL not set.');
            showNoSignal();
            return;
        }

        _directionsService  = new google.maps.DirectionsService();
        _directionsRenderer = new google.maps.DirectionsRenderer({
            map: _map,
            suppressMarkers: true,
            polylineOptions: {
                strokeColor: '#3B82F6',
                strokeOpacity: 0.85,
                strokeWeight: 5,
            },
        });

        try {
            const fbApp = (typeof firebase !== 'undefined' && firebase.apps.length)
                ? firebase.apps[0]
                : firebase.initializeApp(FIREBASE_CONFIG);

            const db = fbApp.database();

            db.ref('trucks/' + VEHICLE_NO + '/currentLocation').on('value', function(snap) {
                const loc = snap.val();
                if (!loc || !loc.latitude || !loc.longitude) {
                    showNoSignal();
                    return;
                }
                hideNoSignal();
                const pos = { lat: loc.latitude, lng: loc.longitude };
                updateTruckMarker(pos, loc.bearing || 0);
                maybeRefreshRoute(pos);
            });

            // Watch the invoice delivery record for completion / drop-point location
            db.ref('deliveries/' + INVOICE_NO).on('value', function(snap) {
                const data = snap.val();
                if (!data) return;
                if (data.status === 'delivered' && data.latitude && data.longitude) {
                    // Hide truck, show completed marker at actual drop point
                    if (_truckMarker) _truckMarker.setMap(null);
                    if (_directionsRenderer) _directionsRenderer.setMap(null);
                    const completedPos = { lat: data.latitude, lng: data.longitude };
                    new google.maps.Marker({
                        position: completedPos,
                        map: _map,
                        title: data.dropPointCode || DROP_NAME,
                        icon: {
                            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(
                                '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">' +
                                '<circle cx="16" cy="16" r="14" fill="#22C55E" stroke="white" stroke-width="2.5"/>' +
                                '<path d="M10 16l4 4 8-8" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>' +
                                '</svg>'
                            ),
                            scaledSize: new google.maps.Size(32, 32),
                            anchor:     new google.maps.Point(16, 16),
                        },
                    });
                    _map.panTo(completedPos);
                    _map.setZoom(16);
                    hideNoSignal();
                }
            });
        } catch (err) {
            console.error('Firebase error:', err);
            showNoSignal();
        }
        @endif
    }

    // ── Truck marker ──────────────────────────────────────────
    function updateTruckMarker(pos, bearing) {
        const icon = {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(
                `<svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 44 44">
                    <circle cx="22" cy="22" r="20" fill="#3B82F6" stroke="white" stroke-width="3"/>
                    <text x="22" y="29" text-anchor="middle" font-size="20">🚚</text>
                 </svg>`
            ),
            scaledSize: new google.maps.Size(44, 44),
            anchor:     new google.maps.Point(22, 22),
        };

        if (!_truckMarker) {
            _truckMarker = new google.maps.Marker({ position: pos, map: _map, icon, title: VEHICLE_NO, zIndex: 10 });
        } else {
            _truckMarker.setPosition(pos);
        }
        _map.panTo(pos);
    }

    // ── Directions route ──────────────────────────────────────
    function maybeRefreshRoute(truckPos) {
        if (!DROP_LAT || !DROP_LNG || !_directionsService) return;

        const now = Date.now();
        const dist = _lastRouteOrigin
            ? google.maps.geometry.spherical.computeDistanceBetween(
                new google.maps.LatLng(truckPos),
                new google.maps.LatLng(_lastRouteOrigin))
            : Infinity;

        if (dist < ROUTE_MIN_DIST_M && now - _lastRouteTime < ROUTE_MIN_MS) return;

        _lastRouteOrigin = truckPos;
        _lastRouteTime   = now;

        _directionsService.route({
            origin:      truckPos,
            destination: { lat: DROP_LAT, lng: DROP_LNG },
            travelMode:  google.maps.TravelMode.DRIVING,
        }, function(result, status) {
            if (status === 'OK') _directionsRenderer.setDirections(result);
        });
    }

    // ── No-signal helpers ─────────────────────────────────────
    function showNoSignal() {
        const el = document.getElementById('no-signal');
        if (el) { el.style.display = 'flex'; el.style.flexDirection = 'column'; el.style.alignItems = 'center'; el.style.justifyContent = 'center'; }
    }
    function hideNoSignal() {
        const el = document.getElementById('no-signal');
        if (el) el.style.display = 'none';
    }

    // ── Wait for both Firebase and Maps SDKs (live mode only) ─
    @if($isInTransit && $delivery->no_kendaraan)
    function waitForFirebase(callback, attempts) {
        attempts = attempts || 0;
        if (typeof firebase !== 'undefined') { callback(); return; }
        if (attempts < 60) setTimeout(() => waitForFirebase(callback, attempts + 1), 200);
    }
    // onMapsReady is the Google Maps callback; ensure Firebase is loaded too before initialising
    const _origMapsReady = onMapsReady;
    window.onMapsReady = function() {
        waitForFirebase(_origMapsReady);
    };
    @endif
    </script>
    @endif{{-- end showMap --}}

</body>
</html>
