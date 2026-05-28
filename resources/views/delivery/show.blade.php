@extends('layouts.dashboard')

@section('title', 'Detail Delivery')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Detail Delivery</h1>
        <a href="{{ route('delivery.index') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">&larr; Kembali</a>
    </div>

    {{-- Status highlight banner --}}
    @php
        $statusClasses = [
            'PENDING'        => ['banner' => 'bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600',         'badge' => 'bg-gray-400 text-white',                    'icon' => '⏳'],
            'DIMUAT'         => ['banner' => 'bg-yellow-50 dark:bg-yellow-900/30 border-yellow-300 dark:border-yellow-700', 'badge' => 'bg-yellow-400 dark:bg-yellow-500 text-white', 'icon' => '📦'],
            'MENUNGGU_SUPIR' => ['banner' => 'bg-blue-50 dark:bg-blue-900/30 border-blue-300 dark:border-blue-700',       'badge' => 'bg-blue-500 dark:bg-blue-600 text-white',     'icon' => '🕐'],
            'SEDANG_DIKIRIM' => ['banner' => 'bg-orange-50 dark:bg-orange-900/30 border-orange-300 dark:border-orange-700', 'badge' => 'bg-orange-500 dark:bg-orange-600 text-white', 'icon' => '🚚'],
            'SELESAI'        => ['banner' => 'bg-green-50 dark:bg-green-900/30 border-green-300 dark:border-green-700',   'badge' => 'bg-green-500 dark:bg-green-600 text-white',   'icon' => '✅'],
        ];
        $sc = $statusClasses[$delivery->status] ?? ['banner' => 'bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600', 'badge' => 'bg-gray-400 text-white', 'icon' => '•'];
    @endphp

    <div class="border-2 rounded-xl p-5 mb-6 flex items-center gap-4 {{ $sc['banner'] }}" data-status-banner>
        <span class="text-3xl" data-status-icon>{{ $sc['icon'] }}</span>
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Status Pengiriman</p>
            <span class="inline-block px-4 py-1.5 rounded-full text-sm font-bold tracking-wide {{ $sc['badge'] }}" data-status-badge>
                {{ \App\Models\DELIVERY_STATUS::tryFrom($delivery->status)?->label() ?? $delivery->status }}
            </span>
        </div>
        <div class="ml-auto text-right">
            <p class="text-xs text-gray-400 dark:text-gray-500">Terakhir diperbarui</p>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $delivery->updated_at->format('d M Y, H:i') }}</p>
        </div>
    </div>

    {{-- Detail card --}}
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 space-y-4">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1">No. Invoice</p>
                <p class="font-mono text-sm font-medium text-gray-800 dark:text-gray-200">{{ $delivery->no_invoice }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1">PBF</p>
                <p class="text-sm text-gray-800 dark:text-gray-200">{{ $delivery->pbf?->nama_pbf ?? '-' }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 font-mono">{{ $delivery->kode_pbf }}</p>
            </div>
        </div>

        <hr class="border-gray-200 dark:border-gray-700">

        <div class="grid grid-cols-3 gap-4">
            <div class="text-center bg-gray-50 dark:bg-gray-700/50 rounded-lg py-3">
                <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $delivery->jumlah_barang_besar }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Barang Besar</p>
            </div>
            <div class="text-center bg-gray-50 dark:bg-gray-700/50 rounded-lg py-3">
                <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $delivery->jumlah_barang_sedang }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Barang Sedang</p>
            </div>
            <div class="text-center bg-gray-50 dark:bg-gray-700/50 rounded-lg py-3">
                <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $delivery->jumlah_barang_kecil }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Barang Kecil</p>
            </div>
        </div>

        <hr class="border-gray-200 dark:border-gray-700">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1">No. Kendaraan</p>
                <p class="text-sm text-gray-800 dark:text-gray-200">{{ $delivery->no_kendaraan ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1">Dibuat</p>
                <p class="text-sm text-gray-800 dark:text-gray-200">{{ $delivery->created_at->format('d M Y, H:i') }}</p>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="mt-4 flex gap-3 flex-wrap">
        <button onclick="openTrackModal()"
                class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Track
        </button>
        <a href="{{ route('delivery.edit', $delivery->no_invoice) }}"
           class="px-5 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-lg transition">Edit</a>
        <form method="POST" action="{{ route('delivery.destroy', $delivery->no_invoice) }}"
              onsubmit="return confirm('Yakin ingin menghapus delivery ini?')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">Hapus</button>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     TRACKING MODAL
═══════════════════════════════════════════════════════════════ --}}
<div id="track-modal"
     class="fixed inset-0 z-50"
     style="display:none"
     role="dialog" aria-modal="true" aria-labelledby="track-modal-title">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeTrackModal()"></div>

    {{-- Panel --}}
    <div class="absolute inset-0 sm:inset-4 lg:inset-8 bg-white dark:bg-gray-900 sm:rounded-2xl shadow-2xl flex flex-col overflow-hidden">

        {{-- ── Header ── --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
            <div>
                <h3 id="track-modal-title" class="text-base font-bold text-gray-800 dark:text-white">Track Pengiriman</h3>
                <p class="text-xs text-gray-400 dark:text-gray-500 font-mono mt-0.5">
                    {{ $delivery->no_invoice }}
                    @if($delivery->no_kendaraan) &bull; {{ $delivery->no_kendaraan }} @endif
                </p>
            </div>
            <button onclick="closeTrackModal()"
                    class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- ── Body: timeline + map side-by-side on large screens ── --}}
        <div class="flex-1 overflow-hidden flex flex-col lg:flex-row">

            {{-- ── Left: History Timeline ── --}}
            <div class="lg:w-72 xl:w-80 shrink-0 border-b lg:border-b-0 lg:border-r border-gray-200 dark:border-gray-700 overflow-y-auto" style="max-height:260px" id="history-panel">
                <div class="p-5">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-5">Riwayat Status</p>

                    @php
                        // History status values may be enum values OR legacy Indonesian strings
                        $dotColor = [
                            // Enum values
                            'SELESAI'            => 'bg-green-500',
                            'SEDANG_DIKIRIM'     => 'bg-orange-500',
                            'MENUNGGU_SUPIR'     => 'bg-blue-500',
                            'DIMUAT'             => 'bg-yellow-500',
                            'PENDING'            => 'bg-gray-400',
                            // Legacy Indonesian strings (kept for old history records)
                            'Sampai Tujuan'      => 'bg-green-500',
                            'Selesai'            => 'bg-green-500',
                            'Dikirim'            => 'bg-orange-500',
                            'Sedang Dikirim'     => 'bg-orange-500',
                            'Menunggu Supir'     => 'bg-blue-500',
                            'Menunggu Staff PBF' => 'bg-sky-400',
                            'Dimuat'             => 'bg-yellow-500',
                        ];
                    @endphp

                    @if($history->isEmpty())
                        <div class="py-8 text-center">
                            <p class="text-sm text-gray-400 italic">Belum ada riwayat pengiriman.</p>
                        </div>
                    @else
                        <ol class="relative ml-3">
                            @foreach($history as $h)
                            @php $dc = $dotColor[$h->status] ?? 'bg-gray-400'; @endphp
                            <li class="relative pb-6 last:pb-0">
                                {{-- Vertical connector --}}
                                @if(!$loop->last)
                                <div class="absolute left-[7px] top-4 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                                @endif
                                {{-- Dot --}}
                                <div class="absolute left-0 top-1.5 w-3.5 h-3.5 rounded-full {{ $dc }} border-2 border-white dark:border-gray-900 shadow-sm"></div>
                                {{-- Content --}}
                                <div class="pl-6">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 leading-snug">{{ $h->status }}</p>
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

            {{-- ── Right: Map ── --}}
            <div class="flex-1 relative" style="min-height:280px">

                @if($delivery->status === \App\Models\DELIVERY_STATUS::IN_DELIVERY->value && $delivery->no_kendaraan)

                    {{-- Map canvas --}}
                    <div id="delivery-map" class="absolute inset-0"></div>

                    {{-- Loading spinner --}}
                    <div id="map-loading-overlay"
                         class="absolute inset-0 flex flex-col items-center justify-center bg-gray-100 dark:bg-gray-800 z-10">
                        <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Memuat peta...</p>
                    </div>

                    {{-- LIVE badge --}}
                    <div id="live-badge" class="absolute top-3 left-3 z-20 bg-white dark:bg-gray-800 rounded-full shadow-md px-3 py-1.5 flex items-center gap-1.5 pointer-events-none">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                        </span>
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300">LIVE</span>
                    </div>

                    {{-- Vehicle + timestamp --}}
                    <div class="absolute top-3 right-3 z-20 bg-white dark:bg-gray-800 rounded-lg shadow-md px-3 py-2 text-right pointer-events-none">
                        <p class="text-xs font-bold font-mono text-gray-700 dark:text-gray-300">{{ $delivery->no_kendaraan }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500" id="last-update-label">–</p>
                    </div>

                    {{-- No-signal overlay (hidden by default) --}}
                    <div id="no-signal-overlay"
                         class="absolute inset-0 z-10 flex-col items-center justify-center bg-gray-100/90 dark:bg-gray-800/90"
                         style="display:none">
                        <span class="text-5xl mb-3">📡</span>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Menunggu data lokasi...</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Kendaraan belum mengirim posisi</p>
                    </div>

                @elseif($delivery->status === \App\Models\DELIVERY_STATUS::COMPLETED->value && $delivery->pbf?->lat && $delivery->pbf?->lng)

                    {{-- Completed: static map centred on drop-point --}}
                    <div id="completed-map" class="absolute inset-0"></div>
                    <div class="absolute inset-0 flex items-end justify-center pb-6 pointer-events-none z-10">
                        <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl px-5 py-3 flex items-center gap-3">
                            <span class="text-3xl">&#x2705;</span>
                            <div>
                                <p class="text-sm font-bold text-green-600 dark:text-green-400">Pengiriman Selesai</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $delivery->pbf->nama_pbf }}
                                    &bull; {{ $delivery->updated_at->format('d M Y, H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>

                @else

                    <div class="absolute inset-0 flex items-center justify-center bg-gray-50 dark:bg-gray-800/50">
                        <div class="text-center max-w-xs px-6">
                            <span class="text-6xl block mb-4">🗺️</span>
                            <p class="text-base font-semibold text-gray-600 dark:text-gray-300">Peta Tidak Tersedia</p>
                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-2 leading-relaxed">
                                Peta lokasi real-time hanya aktif saat status<br>
                                pengiriman adalah <span class="font-semibold text-orange-500">Sedang Dikirim</span>.
                            </p>
                            <p class="mt-3 text-xs text-gray-400">
                                Status saat ini: <span class="font-semibold text-gray-600 dark:text-gray-300">{{ $delivery->status }}</span>
                            </p>
                        </div>
                    </div>

                @endif
            </div>

        </div>{{-- end body --}}
    </div>{{-- end panel --}}
</div>{{-- end modal --}}

{{-- ═══════════════════════════════════════════════════════════════
     SDKs  (only loaded when the truck is actively in transit)
═══════════════════════════════════════════════════════════════ --}}
@if(in_array($delivery->status, [\App\Models\DELIVERY_STATUS::IN_TRANSIT->value, \App\Models\DELIVERY_STATUS::COMPLETED->value]) && ($delivery->no_kendaraan || ($delivery->pbf?->lat && $delivery->pbf?->lng)))
<script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-database-compat.js"></script>
<script
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=geometry"
    defer>
</script>
@endif

<script>
// ── Modal helpers ────────────────────────────────────────────
function openTrackModal() {
    const modal = document.getElementById('track-modal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    // Expand timeline panel height on large screens (no max-height constraint)
    const panel = document.getElementById('history-panel');
    if (panel && window.innerWidth >= 1024) panel.style.maxHeight = 'none';

    @if($delivery->status === \App\Models\DELIVERY_STATUS::IN_TRANSIT->value && $delivery->no_kendaraan)
    if (!window._trackingMapInitialized) {
        window._trackingMapInitialized = true;
        waitForSDKs(initDeliveryTracking);
    }
    @endif
    @if($delivery->status === \App\Models\DELIVERY_STATUS::COMPLETED->value && $delivery->pbf?->lat && $delivery->pbf?->lng)
    if (typeof window._initCompletedMap === 'function') window._initCompletedMap();
    @endif
}

function closeTrackModal() {
    document.getElementById('track-modal').style.display = 'none';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeTrackModal();
});

@if($delivery->status === \App\Models\DELIVERY_STATUS::IN_TRANSIT->value && $delivery->no_kendaraan)
// ── Config (server-rendered, safe to expose as client config) ─
const VEHICLE_NO    = @json($delivery->no_kendaraan);
const INVOICE_NO         = @json($delivery->no_invoice);
const KODE_PBF           = @json($delivery->kode_pbf);
const STATUS_URL         = @json(route('delivery.statusCheck', $delivery->no_invoice));

const FIREBASE_CONFIG = {
    apiKey:            @json(config('firebase.web_api_key')),
    authDomain:        @json(config('firebase.auth_domain')),
    databaseURL:       @json(config('firebase.database_url')),
    projectId:         @json(config('firebase.project_id')),
    storageBucket:     @json(config('firebase.storage_bucket')),
    messagingSenderId: @json(config('firebase.messaging_sender_id')),
    appId:             @json(config('firebase.app_id')),
};

const GMAPS_KEY       = @json(config('services.google_maps.api_key'));
const DROP_LAT        = @json((float) ($delivery->pbf?->lat ?? 0));
const DROP_LNG        = @json((float) ($delivery->pbf?->lng ?? 0));
const DROP_NAME       = @json($delivery->pbf?->nama_pbf ?? $delivery->kode_pbf);

let _map = null, _truckMarker = null, _destinationMarker = null;
let _directionsRenderer = null, _directionsService = null;
let _completedMarkers = [];
let _lastRouteOrigin = null, _lastRouteTime = 0;
let _completionFired = false;
const ROUTE_MIN_DISTANCE_M = 50;   // re-request only if truck moved ≥50 m
const ROUTE_MIN_INTERVAL_MS = 120000; // or at least every 2 min

// Wait until both Firebase and Google Maps SDKs are ready
function waitForSDKs(callback, attempts) {
    attempts = attempts || 0;
    const firebaseReady = typeof firebase !== 'undefined';
    const mapsReady     = typeof google  !== 'undefined' && google.maps;
    if (firebaseReady && mapsReady) {
        callback();
    } else if (attempts < 60) {
        setTimeout(() => waitForSDKs(callback, attempts + 1), 200);
    } else {
        showMapError('SDK gagal dimuat. Periksa koneksi internet.');
    }
}

function initDeliveryTracking() {
    // ── Google Map ───────────────────────────────────────────
    if (!GMAPS_KEY) {
        showMapError('GOOGLE_MAPS_API_KEY belum dikonfigurasi.');
        return;
    }

    _map = new google.maps.Map(document.getElementById('delivery-map'), {
        zoom: 14,
        center: { lat: -6.9, lng: 107.6 },
        mapTypeId: 'roadmap',
        zoomControl: true,
        streetViewControl: false,
        mapTypeControl: false,
        fullscreenControl: true,
    });

    document.getElementById('map-loading-overlay').style.display = 'none';

    // ── Directions service + renderer ─────────────────────────
    _directionsService  = new google.maps.DirectionsService();
    _directionsRenderer = new google.maps.DirectionsRenderer({
        map: _map,
        suppressMarkers: true,          // we draw our own truck + destination markers
        polylineOptions: {
            strokeColor:   '#3B82F6',
            strokeOpacity: 0.85,
            strokeWeight:  5,
        },
    });

    // ── Destination marker ────────────────────────────────────
    if (DROP_LAT && DROP_LNG) {
        placeDestinationMarker({ lat: DROP_LAT, lng: DROP_LNG });
    }

    // ── Firebase ─────────────────────────────────────────────
    if (!FIREBASE_CONFIG.databaseURL) {
        showNoSignal('FIREBASE_DATABASE_URL belum dikonfigurasi.');
        return;
    }

    try {
        const fbApp = firebase.apps.length
            ? firebase.apps[0]
            : firebase.initializeApp(FIREBASE_CONFIG);

        const db = fbApp.database();

        // Poll DB status every 10 s — reacts when the other app updates the record
        startStatusPolling();

        // 1. Real-time truck location + route refresh
        db.ref('trucks/' + VEHICLE_NO + '/currentLocation').on('value', function (snap) {
            const loc = snap.val();
            if (!loc || !loc.latitude || !loc.longitude) {
                showNoSignal();
                return;
            }
            hideNoSignal();
            const pos = { lat: loc.latitude, lng: loc.longitude };
            updateTruckMarker(pos, loc.bearing || 0);
            updateTimestampLabel(loc.timestamp);
            maybeRefreshDirectionsRoute(pos);
        });

        // 2. Watch this invoice's delivery record in Firebase for drop-point location.
        //    Completion detection is handled exclusively by the DB status poll
        //    to avoid false positives from stale Firebase nodes.
        db.ref('deliveries/' + INVOICE_NO).on('value', function (snap) {
            clearCompletedMarkers();
            const data = snap.val();
            if (!data) return;
            if (data.status === 'delivered' && data.latitude && data.longitude) {
                addCompletedMarker(data);
            }
        });

    } catch (err) {
        console.error('Firebase error:', err);
        showNoSignal('Gagal terhubung ke Firebase.');
    }
}

// ── Truck marker ─────────────────────────────────────────────
const TRUCK_SVG = [
    '<svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 44 44">',
    '<circle cx="22" cy="22" r="20" fill="#3B82F6" stroke="white" stroke-width="3"/>',
    '<text x="22" y="29" text-anchor="middle" font-size="20">🚚</text>',
    '</svg>',
].join('');

function updateTruckMarker(pos, bearing) {
    if (!_truckMarker) {
        _truckMarker = new google.maps.Marker({
            position: pos,
            map: _map,
            title: VEHICLE_NO,
            icon: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(TRUCK_SVG),
                scaledSize: new google.maps.Size(44, 44),
                anchor:      new google.maps.Point(22, 22),
            },
            zIndex: 10,
        });
    } else {
        _truckMarker.setPosition(pos);
    }
}

// ── Destination marker ────────────────────────────────────────
const DEST_SVG = [
    '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="48" viewBox="0 0 40 48">',
    '<path d="M20 0C9 0 0 9 0 20c0 15 20 28 20 28S40 35 40 20C40 9 31 0 20 0z" fill="#EF4444"/>',
    '<circle cx="20" cy="20" r="9" fill="white"/>',
    '</svg>',
].join('');

function placeDestinationMarker(pos) {
    if (_destinationMarker) _destinationMarker.setMap(null);
    _destinationMarker = new google.maps.Marker({
        position: pos,
        map: _map,
        title: DROP_NAME,
        icon: {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(DEST_SVG),
            scaledSize: new google.maps.Size(40, 48),
            anchor:      new google.maps.Point(20, 48),
        },
        zIndex: 9,
    });

    var infoWindow = new google.maps.InfoWindow({
        content: '<div style="font-size:13px;font-weight:600;padding:2px 4px">' + DROP_NAME + '</div>',
    });
    _destinationMarker.addListener('click', function () {
        infoWindow.open(_map, _destinationMarker);
    });
}

// ── Directions route ─────────────────────────────────────────
function maybeRefreshDirectionsRoute(origin) {
    if (!DROP_LAT || !DROP_LNG) return;
    const now = Date.now();
    // Skip if called too recently AND truck hasn't moved much
    if (_lastRouteOrigin) {
        const moved = google.maps.geometry.spherical.computeDistanceBetween(
            new google.maps.LatLng(_lastRouteOrigin.lat, _lastRouteOrigin.lng),
            new google.maps.LatLng(origin.lat, origin.lng)
        );
        if (moved < ROUTE_MIN_DISTANCE_M && (now - _lastRouteTime) < ROUTE_MIN_INTERVAL_MS) return;
    }
    _lastRouteOrigin = origin;
    _lastRouteTime   = now;

    _directionsService.route({
        origin:      origin,
        destination: { lat: DROP_LAT, lng: DROP_LNG },
        travelMode:  google.maps.TravelMode.DRIVING,
    }, function (result, status) {
        if (status === google.maps.DirectionsStatus.OK) {
            _directionsRenderer.setDirections(result);
        } else {
            // Directions failed (e.g. no API billing) – fall back to straight line
            drawFallbackPolyline(origin, { lat: DROP_LAT, lng: DROP_LNG });
            console.warn('Directions API status:', status);
        }
    });
}

function drawFallbackPolyline(origin, destination) {
    if (_directionsRenderer) _directionsRenderer.setMap(null);
    if (window._fallbackPolyline) window._fallbackPolyline.setMap(null);
    window._fallbackPolyline = new google.maps.Polyline({
        path: [origin, destination],
        geodesic: true,
        strokeColor:   '#3B82F6',
        strokeOpacity: 0.6,
        strokeWeight:  4,
        strokeDashArray: [8, 6],
        map: _map,
    });
    const bounds = new google.maps.LatLngBounds();
    bounds.extend(origin);
    bounds.extend(destination);
    _map.fitBounds(bounds, 80);
}

// ── Completed delivery markers ────────────────────────────────
const CHECK_SVG = [
    '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">',
    '<circle cx="16" cy="16" r="14" fill="#22C55E" stroke="white" stroke-width="2.5"/>',
    '<path d="M10 16l4 4 8-8" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
    '</svg>',
].join('');

function addCompletedMarker(d) {
    var marker = new google.maps.Marker({
        position: { lat: d.latitude, lng: d.longitude },
        map: _map,
        title: d.dropPointCode || 'Drop Point',
        icon: {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(CHECK_SVG),
            scaledSize: new google.maps.Size(32, 32),
            anchor:      new google.maps.Point(16, 16),
        },
    });
    _completedMarkers.push(marker);
}

function clearCompletedMarkers() {
    _completedMarkers.forEach(function (m) { m.setMap(null); });
    _completedMarkers = [];
}

// ── DB status polling ──────────────────────────────────────────
var _statusPollTimer = null;
function startStatusPolling() {
    if (_statusPollTimer) return;
    _statusPollTimer = setInterval(function () {
        if (_completionFired) {
            clearInterval(_statusPollTimer);
            return;
        }
        fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.status === @json(\App\Models\DELIVERY_STATUS::COMPLETED->value)) {
                    onDeliveryCompleted();
                }
            })
            .catch(function (e) { console.warn('Status poll error:', e); });
    }, 10000);
}

// ── Arrival / completion ──────────────────────────────────────
function onDeliveryCompleted() {
    if (_completionFired) return;
    _completionFired = true;
    if (_statusPollTimer) { clearInterval(_statusPollTimer); _statusPollTimer = null; }

    // Update status banner
    var icon   = document.querySelector('[data-status-icon]');
    var badge  = document.querySelector('[data-status-badge]');
    var banner = document.querySelector('[data-status-banner]');
    if (icon)   icon.textContent = '\u2705';
    if (badge) {
        badge.className = badge.className.replace(/bg-\S+/g, '').trim()
            + ' bg-green-500 dark:bg-green-600 text-white';
        badge.textContent = 'Selesai';
    }
    if (banner) {
        banner.className = banner.className
            .replace(/bg-\S+/g, '').replace(/border-\S+/g, '').trim()
            + ' bg-green-50 dark:bg-green-900/30 border-green-300 dark:border-green-700';
    }

    // Swap LIVE badge
    var liveBadge = document.getElementById('live-badge');
    if (liveBadge) {
        liveBadge.innerHTML =
            '<span class="text-base leading-none">\u2705</span>' +
            '<span class="text-xs font-bold text-green-600 dark:text-green-400">SELESAI</span>';
    }

    // Hide truck + route
    if (_truckMarker)          _truckMarker.setMap(null);
    if (_directionsRenderer)   _directionsRenderer.setMap(null);
    if (window._fallbackPolyline) window._fallbackPolyline.setMap(null);

    // Pan map to drop point and place completed marker
    if (_map && DROP_LAT && DROP_LNG) {
        _map.panTo({ lat: DROP_LAT, lng: DROP_LNG });
        _map.setZoom(16);
    }
    showCompletedMapOverlay();
}

function showCompletedMapOverlay() {
    var mapEl = document.getElementById('delivery-map');
    if (!mapEl) return;
    var overlay = document.createElement('div');
    overlay.className = 'absolute inset-0 flex items-end justify-center pb-6 pointer-events-none z-30';
    overlay.innerHTML =
        '<div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl px-5 py-3 flex items-center gap-3">' +
        '<span class="text-3xl">&#x2705;</span>' +
        '<div>' +
        '<p class="text-sm font-bold text-green-600 dark:text-green-400">Pengiriman Selesai</p>' +
        '<p class="text-xs text-gray-400 dark:text-gray-500">' + DROP_NAME + '</p>' +
        '</div>' +
        '</div>';
    mapEl.parentElement.appendChild(overlay);
}

// ── UI helpers ────────────────────────────────────────────────
function showNoSignal(msg) {
    var el = document.getElementById('no-signal-overlay');
    if (msg) {
        var p = el.querySelector('p');
        if (p) p.textContent = msg;
    }
    el.style.display = 'flex';
}

function hideNoSignal() {
    document.getElementById('no-signal-overlay').style.display = 'none';
}

function showMapError(msg) {
    var overlay = document.getElementById('map-loading-overlay');
    overlay.innerHTML = '<p class="text-sm text-red-500 px-6 text-center">' + msg + '</p>';
    overlay.style.display = 'flex';
}

function updateTimestampLabel(ts) {
    var el = document.getElementById('last-update-label');
    if (!el || !ts) return;
    var d = new Date(ts);
    el.textContent = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}
@endif {{-- end IN_TRANSIT block --}}

@if($delivery->status === \App\Models\DELIVERY_STATUS::COMPLETED->value && $delivery->pbf?->lat && $delivery->pbf?->lng)
// ── Completed static map ──────────────────────────────────────
(function () {
    var _DROP_LAT  = @json((float) $delivery->pbf->lat);
    var _DROP_LNG  = @json((float) $delivery->pbf->lng);
    var _DROP_NAME = @json($delivery->pbf->nama_pbf ?? $delivery->kode_pbf);

    window._initCompletedMap = function () {
        var mapEl = document.getElementById('completed-map');
        if (!mapEl || window._completedMapInit) return;
        function tryInit(n) {
            if (typeof google !== 'undefined' && google.maps) {
                window._completedMapInit = true;
                var pos  = { lat: _DROP_LAT, lng: _DROP_LNG };
                var cMap = new google.maps.Map(mapEl, {
                    zoom: 16, center: pos,
                    mapTypeId: 'roadmap',
                    zoomControl: true,
                    streetViewControl: false,
                    mapTypeControl: false,
                    fullscreenControl: true,
                });
                new google.maps.Marker({
                    position: pos,
                    map: cMap,
                    title: _DROP_NAME,
                    icon: {
                        url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(
                            '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="56" viewBox="0 0 48 56">' +
                            '<path d="M24 0C11 0 0 11 0 24c0 18 24 32 24 32S48 42 48 24C48 11 37 0 24 0z" fill="#22C55E"/>' +
                            '<circle cx="24" cy="24" r="12" fill="white"/>' +
                            '<path d="M17 24l5 5 9-10" stroke="#22C55E" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none"/>' +
                            '</svg>'
                        ),
                        scaledSize: new google.maps.Size(48, 56),
                        anchor:     new google.maps.Point(24, 56),
                    },
                    zIndex: 10,
                });
            } else if (n < 40) {
                setTimeout(function () { tryInit(n + 1); }, 200);
            }
        }
        tryInit(0);
    };
})();
@endif
</script>
@endsection
