const CACHE  = 'jaanahai-v1';
const STATIC = [
    '/css/bootstrap.min.css',
    '/css/common.css',
    '/js/bootstrap.min.js',
    '/js/map.js',
    '/img/logo.jpg'
];

self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE).then(c => c.addAll(STATIC))
    );
    self.skipWaiting();
});

self.addEventListener('activate', e => {
    e.waitUntil(clients.claim());
});

self.addEventListener('fetch', e => {
    // Never intercept navigation (HTML page) requests — let them go straight to server
    if (e.request.mode === 'navigate') {
        return;
    }

    // Cache-first for static assets only
    e.respondWith(
        caches.match(e.request).then(cached => cached || fetch(e.request))
    );
});
