const CACHE  = 'jaanahai-v2';
const STATIC = [
    '/css/app.css',
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
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        ).then(() => clients.claim())
    );
});

self.addEventListener('fetch', e => {
    if (e.request.mode === 'navigate') return;
    e.respondWith(
        caches.match(e.request).then(cached => cached || fetch(e.request))
    );
});

self.addEventListener('push', e => {
    const d = e.data ? e.data.json() : { title: 'JaanaHai', body: 'New notification' };
    e.waitUntil(
        self.registration.showNotification(d.title, {
            body:  d.body,
            icon:  '/img/logo.jpg',
            badge: '/img/logo.jpg',
        })
    );
});

self.addEventListener('notificationclick', e => {
    e.notification.close();
    e.waitUntil(clients.openWindow('/notifications'));
});
