const CACHE  = 'jaanahai-v1';
const STATIC = [
    '/',
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
});

self.addEventListener('fetch', e => {
    e.respondWith(
        caches.match(e.request).then(cached => cached || fetch(e.request))
    );
});
