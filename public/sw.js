const CACHE_NAME = 'rwo-offline-cache-v2';
const PRECACHE_ASSETS = [
    '/mobile/rwo',
    'https://cdn.jsdelivr.net/npm/daisyui@4.12.24/dist/full.min.css',
    'https://cdn.tailwindcss.com'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    // Only intercept HTTP/HTTPS GET requests
    if (!event.request.url.startsWith('http') || event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);

    // Network-First strategy for the HTML page `/mobile/rwo`
    if (url.pathname === '/mobile/rwo' || url.pathname === '/mobile/rwo/') {
        event.respondWith(
            fetch(event.request)
                .then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, responseToCache);
                        });
                    }
                    return networkResponse;
                })
                .catch(() => {
                    // Offline or network error: return cached page
                    return caches.match(event.request);
                })
        );
    } else {
        // Stale-While-Revalidate strategy for other assets (CSS, JS from CDN, etc.)
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                const fetchPromise = fetch(event.request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, responseToCache);
                        });
                    }
                    return networkResponse;
                }).catch(() => {
                    // Ignore network error if we have cached response
                });

                return cachedResponse || fetchPromise;
            })
        );
    }
});
