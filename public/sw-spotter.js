var CACHE = 'spotter-v1';
var SHELL = [
  '/spotter',
  '/spotter-assets/js/db.js',
  '/spotter-assets/js/duplicate.js',
  '/spotter-assets/js/camera.js',
  '/spotter-assets/js/gps.js',
  '/spotter-assets/js/sync.js',
  '/spotter-assets/js/app.js',
  '/spotter-assets/manifest.json',
  '/spotter-assets/icon-192.png'
];

self.addEventListener('install', function(e) {
  e.waitUntil(
    caches.open(CACHE)
      .then(function(c) { return c.addAll(SHELL); })
      .then(function() { return self.skipWaiting(); })
  );
});

self.addEventListener('activate', function(e) {
  e.waitUntil(
    caches.keys().then(function(keys) {
      return Promise.all(
        keys.filter(function(k) { return k !== CACHE; })
            .map(function(k) { return caches.delete(k); })
      );
    }).then(function() { return self.clients.claim(); })
  );
});

self.addEventListener('fetch', function(e) {
  var url = new URL(e.request.url);

  // API calls — network only, offline fallback
  if (url.pathname.startsWith('/api/')) {
    e.respondWith(
      fetch(e.request).catch(function() {
        return new Response(JSON.stringify({ error: 'Offline' }), {
          status: 503,
          headers: { 'Content-Type': 'application/json' }
        });
      })
    );
    return;
  }

  // App shell — stale-while-revalidate
  if (url.pathname.startsWith('/spotter')) {
    e.respondWith(
      caches.match(e.request).then(function(cached) {
        var network = fetch(e.request).then(function(res) {
          if (res.ok) {
            caches.open(CACHE).then(function(c) { c.put(e.request, res.clone()); });
          }
          return res;
        }).catch(function() { return cached; });
        return cached || network;
      })
    );
    return;
  }

  // Everything else — cache first
  e.respondWith(
    caches.match(e.request).then(function(cached) {
      return cached || fetch(e.request);
    })
  );
});
