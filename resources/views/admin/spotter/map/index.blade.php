@extends('layouts.admin')

@section('title', 'Spotter Map — Dawa Mtaani')

@section('content')

<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-white">Pharmacy Map</h1>
        <p class="text-sm text-gray-400 mt-1">{{ $submissions->count() }} submissions with GPS coordinates</p>
    </div>

    <div id="spotter-map" style="height: 600px;" class="rounded-2xl overflow-hidden border border-gray-700"></div>

    {{-- Legend --}}
    <div class="flex items-center gap-6 text-sm text-gray-400">
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-full bg-green-400"></div>
            <span>High potential</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
            <span>Medium</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-full bg-red-400"></div>
            <span>Low</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-full bg-gray-400"></div>
            <span>Held</span>
        </div>
    </div>

</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('spotter-map').setView([-1.2921, 36.8219], 7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const submissions = @json($submissions);
    const colours = { high: '#22c55e', medium: '#facc15', low: '#ef4444' };
    const bounds = [];

    submissions.forEach(function(sub) {
        if (!sub.lat || !sub.lng) return;
        const potential = sub.potential?.value || sub.potential || 'medium';
        const colour = sub.status === 'held' ? '#9ca3af' : (colours[potential] || colours.medium);
        const marker = L.circleMarker([parseFloat(sub.lat), parseFloat(sub.lng)], {
            radius: 8,
            fillColor: colour,
            color: '#1f2937',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(map);
        marker.bindPopup(
            '<div style="min-width:180px">' +
                '<strong>' + (sub.pharmacy || '') + '</strong><br>' +
                '<span style="color:#9ca3af;font-size:12px">' + (sub.ward || '') + ' &middot; ' + (sub.county || '') + '</span><br>' +
                '<span style="font-size:12px">Potential: ' + potential + '</span><br>' +
                '<span style="font-size:12px">Status: ' + (sub.status || '') + '</span>' +
            '</div>'
        );
        bounds.push([parseFloat(sub.lat), parseFloat(sub.lng)]);
    });

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [30, 30] });
    }
});
</script>

@endsection
