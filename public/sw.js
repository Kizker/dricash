// Dricash PWA Service Worker
const CACHE_VERSION = 'dricash-v1.1';
const STATIC_CACHE = `dricash-static-${CACHE_VERSION}`;
const OFFLINE_URL = '/offline.html';

// Static assets to pre-cache on install
const PRECACHE_ASSETS = [
    '/favicon.ico',
    '/favicon-192.png',
    '/favicon-512.png',
    '/apple-touch-icon.png',
    '/site.webmanifest',
    OFFLINE_URL
];

// Install Event: pre-cache offline page & core branding icons
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        }).then(() => {
            return self.skipWaiting();
        }).catch((err) => {
            console.warn('[PWA SW] Pre-cache warning:', err);
        })
    );
});

// Activate Event: purge stale caches and claim clients immediately
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== STATIC_CACHE) {
                        return caches.delete(name);
                    }
                })
            );
        }).then(() => {
            return self.clients.claim();
        })
    );
});

// Fetch Event: handle requests with Inertia-safe navigation logic
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Only handle GET requests from same origin
    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    // Skip Vite dev server and hot-reload requests
    if (url.port === '5173' || url.pathname.includes('/@vite') || url.pathname.includes('/@fs') || url.pathname.includes('hot')) {
        return;
    }

    // -------------------------------------------------------------
    // 1. Navigation Requests (Opening PWA, Clicking Links, Page Reload)
    // -------------------------------------------------------------
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request, {
                // Ensure browser asks for HTML explicitly without X-Inertia
                headers: {
                    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
                }
            }).then((response) => {
                const contentType = response.headers.get('content-type') || '';
                const isInertiaHeader = response.headers.get('x-inertia') === 'true';

                // CRITICAL SHIELD: If a navigation request receives raw Inertia JSON
                // (due to stale CDN cache, disk cache collision, or proxy misconfiguration),
                // DO NOT allow the browser to display raw JSON with 'Pretty print'.
                // Instead, clean fetch the root URL to get the true HTML document.
                if (isInertiaHeader || (contentType.includes('application/json') && !url.pathname.startsWith('/api'))) {
                    return fetch('/', {
                        headers: {
                            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                            'Cache-Control': 'no-cache, no-store'
                        }
                    });
                }

                return response;
            }).catch(async () => {
                // Offline fallback when network is disconnected
                const cachedOffline = await caches.match(OFFLINE_URL);
                if (cachedOffline) {
                    return cachedOffline;
                }
                return new Response(
                    '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dricash - Offline</title><style>body{font-family:-apple-system,BlinkMacSystemFont,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f8fafc;color:#0f172a;text-align:center;padding:1.5rem}.card{background:#fff;border-radius:1rem;padding:2rem;box-shadow:0 4px 6px -1px rgb(0 0 0/0.1);max-width:380px;width:100%}h1{font-size:1.2rem;font-weight:700;color:#059669;margin-bottom:0.5rem}p{font-size:0.875rem;color:#64748b;margin-bottom:1.5rem;line-height:1.5}button{background:#059669;color:#fff;border:none;padding:0.75rem 1.5rem;border-radius:0.5rem;font-weight:600;cursor:pointer;width:100%}</style></head><body><div class="card"><h1>Koneksi Terputus</h1><p>Dricash tidak dapat terhubung ke server. Periksa jaringan internet Anda.</p><button onclick="window.location.reload()">Coba Lagi</button></div></body></html>',
                    { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                );
            })
        );
        return;
    }

    // -------------------------------------------------------------
    // 2. Static Build Assets (Cache-First with Network Fallback)
    // -------------------------------------------------------------
    if (url.pathname.startsWith('/build/assets/') || url.pathname.endsWith('.png') || url.pathname.endsWith('.svg') || url.pathname.endsWith('.ico') || url.pathname.endsWith('.woff2')) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        caches.open(STATIC_CACHE).then((cache) => {
                            cache.put(request, responseToCache);
                        });
                    }
                    return networkResponse;
                });
            })
        );
        return;
    }
});
