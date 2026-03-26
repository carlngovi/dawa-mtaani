@extends('layouts.app')
@section('title', 'Routes & Dispatch Planning — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-white">Routes & Dispatch Planning</h1>
            <p class="text-sm text-gray-400 mt-1">Packed orders grouped by county, ready for dispatch</p>
        </div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-900/30 text-yellow-400 border border-yellow-800">
            {{ $orders->count() }} pending routes
        </span>
    </div>

    {{-- Map card --}}
    <div class="rounded-xl bg-gray-800 border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700 flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-white">Delivery Map</h2>
                <p class="text-xs text-gray-400 mt-0.5">Click a pin to see order details</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-400">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span> Packed</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-400 inline-block"></span> Dispatched</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> Warehouse</span>
            </div>
        </div>
        <div id="delivery-map" class="w-full" style="height: 500px;"></div>
    </div>

    {{-- County route groups --}}
    <div class="rounded-xl bg-gray-800 border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700">
            <h2 class="font-semibold text-white">County Route Groups</h2>
            <p class="text-xs text-gray-400 mt-0.5">Orders grouped by delivery county</p>
        </div>

        @if($orders->isEmpty())
        <div class="px-5 py-12 text-center">
            <svg class="w-10 h-10 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-sm font-medium text-gray-400">All packed orders have been dispatched</p>
            <p class="text-xs text-gray-500 mt-1">New packed orders will appear here</p>
        </div>
        @else
        <div class="divide-y divide-gray-700">
            @foreach($byCounty as $county => $countyOrders)
            <div class="px-5 py-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-yellow-400"></span>
                        <h3 class="font-medium text-white text-sm">{{ $county }}</h3>
                        <span class="text-xs text-gray-400">{{ $countyOrders->count() }} {{ Str::plural('order', $countyOrders->count()) }}</span>
                    </div>
                    <span class="text-xs text-gray-500">KES {{ number_format($countyOrders->sum('total_amount'), 2) }}</span>
                </div>
                <div class="space-y-2 ml-4">
                    @foreach($countyOrders as $order)
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-300 font-mono">{{ strtoupper(substr($order->ulid, -8)) }}</span>
                        <span class="text-gray-400">{{ $order->facility_name }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $order->status === 'DISPATCHED' ? 'bg-blue-900/30 text-blue-400 border border-blue-800' : 'bg-yellow-900/30 text-yellow-400 border border-yellow-800' }}">
                            {{ $order->status }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

<script>
const WAREHOUSE = {!! $warehouseJson !!};
const ORDERS = {!! $mapOrdersJson !!};

function initMap() {
    const map = new google.maps.Map(document.getElementById('delivery-map'), {
        zoom: 6,
        center: { lat: -0.0236, lng: 37.9062 },
        mapTypeId: 'roadmap',
        styles: [
            { elementType: 'geometry', stylers: [{ color: '#1d2535' }] },
            { elementType: 'labels.text.fill', stylers: [{ color: '#8ec3b9' }] },
            { elementType: 'labels.text.stroke', stylers: [{ color: '#1a3646' }] },
            { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#304a7d' }] },
            { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#0e1626' }] },
            { featureType: 'administrative', elementType: 'geometry.stroke', stylers: [{ color: '#4b6878' }] },
        ]
    });

    const infoWindow = new google.maps.InfoWindow();

    new google.maps.Marker({
        position: { lat: WAREHOUSE.lat, lng: WAREHOUSE.lng },
        map, title: WAREHOUSE.name,
        icon: { path: google.maps.SymbolPath.CIRCLE, scale: 10, fillColor: '#EF4444', fillOpacity: 1, strokeColor: '#fff', strokeWeight: 2 },
        zIndex: 100,
    }).addListener('click', function() {
        infoWindow.setContent('<div style="color:#111;padding:8px"><strong>' + WAREHOUSE.name + '</strong><p style="font-size:12px;color:#666">Dispatch origin</p></div>');
        infoWindow.open(map, this);
    });

    ORDERS.forEach(order => {
        const color = order.status === 'DISPATCHED' ? '#60A5FA' : '#FBBF24';
        const marker = new google.maps.Marker({
            position: { lat: order.lat, lng: order.lng },
            map, title: order.facility_name,
            icon: { path: google.maps.SymbolPath.CIRCLE, scale: 8, fillColor: color, fillOpacity: 0.9, strokeColor: '#fff', strokeWeight: 2 },
        });
        marker.addListener('click', () => {
            infoWindow.setContent(
                '<div style="color:#111;padding:8px;min-width:200px">' +
                '<strong>' + order.facility_name + '</strong>' +
                '<p style="font-size:12px">' + order.county + '</p>' +
                '<p style="font-size:12px">Ref: <code>' + order.ulid.slice(-8).toUpperCase() + '</code></p>' +
                '<p style="font-size:12px">KES ' + Number(order.amount).toLocaleString('en-KE', {minimumFractionDigits:2}) + '</p>' +
                '<p style="font-size:11px;color:#666">' + (order.has_gps ? 'GPS confirmed' : 'County centroid') + '</p>' +
                '<span style="background:' + color + ';color:#fff;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:600">' + order.status + '</span></div>'
            );
            infoWindow.open(map, marker);
        });
    });

    if (ORDERS.length === 0) {
        const el = document.getElementById('delivery-map');
        el.style.position = 'relative';
        const d = document.createElement('div');
        d.style.cssText = 'position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(31,41,55,0.9);border:1px solid #374151;border-radius:12px;padding:20px 32px;text-align:center;color:#9CA3AF;font-size:14px';
        d.innerHTML = '<div style="font-size:24px;margin-bottom:8px">📦</div>No active deliveries to map';
        el.appendChild(d);
    }
}
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&callback=initMap"></script>
@endsection