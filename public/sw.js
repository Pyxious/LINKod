const CACHE_NAME = 'linkod-worker-v9';
const STATIC_ASSETS = [
    '/offline.html',
    '/manifest.json',
    '/images/LINKOD logo.png',
    '/images/BUlogo.png',
    '/images/DASHBOARD LOGO.png',
    '/images/REQUESTS LOGO.png',
    '/images/UNITS Logo.png',
    '/images/MESSAGES LOGO.png',
    '/images/NOTIFICATIONS LOGO.png',
    '/favicon.ico'
];

// Inline Fallback Response if cache is somehow empty
const INLINE_OFFLINE_HTML = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No Internet Connection — LINKod</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f0f4f8; color: #1e293b; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1.5rem; }
        .card { background: #fff; border-radius: 1.25rem; border: 1px solid #e2e8f0; max-width: 440px; width: 100%; padding: 2.5rem 2rem; text-align: center; box-shadow: 0 10px 25px -5px rgba(0,51,160,0.08); }
        .icon { width: 64px; height: 64px; border-radius: 50%; background: #fee2e2; color: #ef4444; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1.25rem; }
        h1 { font-size: 1.35rem; color: #0033a0; margin-bottom: 0.5rem; font-weight: 800; }
        p { font-size: 0.9rem; color: #64748b; margin-bottom: 1.5rem; line-height: 1.5; }
        .actions { display: flex; flex-direction: column; gap: 0.65rem; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.75rem 1.25rem; border-radius: 0.65rem; font-size: 0.875rem; font-weight: 700; text-decoration: none; cursor: pointer; border: none; }
        .btn-primary { background: #0033a0; color: #fff; }
        .btn-secondary { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
        .notice { margin-top: 1.25rem; padding: 0.75rem; background: #eff6ff; border: 1px dashed #93c5fd; border-radius: 0.5rem; font-size: 0.75rem; color: #1e40af; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="1" y1="1" x2="23" y2="23"></line><path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"></path><path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path><path d="M10.71 5.05A16 16 0 0 1 22.58 9"></path><path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"></path><path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>
        </div>
        <h1>No Internet Connection</h1>
        <p>This page requires an active connection. You can continue viewing and updating your assigned tasks offline.</p>
        <div class="actions">
            <a href="/worker/job-orders" class="btn btn-primary">Open Job Orders</a>
            <a href="/worker/dashboard" class="btn btn-secondary">Go to Worker Dashboard</a>
            <button onclick="window.location.reload()" class="btn btn-secondary">Try Reconnecting</button>
        </div>
        <div class="notice">
            💡 <strong>Offline Mode:</strong> Task updates and photos taken offline are saved and will auto-sync when online.
        </div>
    </div>
</body>
</html>`;

// Install Event
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(async (cache) => {
            await cache.addAll(STATIC_ASSETS).catch(() => {});

            // Pre-cache compiled Vite assets from build manifest
            try {
                const manifestRes = await fetch('/build/manifest.json');
                if (manifestRes && manifestRes.ok) {
                    const manifest = await manifestRes.json();
                    for (const key in manifest) {
                        if (manifest[key].file) {
                            const assetUrl = '/build/' + manifest[key].file;
                            await cache.add(assetUrl).catch(() => {});
                        }
                    }
                }
            } catch (e) {}

            // Pre-cache core worker offline routes
            try {
                const joRes = await fetch('/worker/job-orders', { credentials: 'same-origin' });
                if (joRes && joRes.ok) {
                    await cache.put('/worker/job-orders', joRes.clone());
                    await cache.put(self.location.origin + '/worker/job-orders', joRes);
                }
            } catch (e) {}

            try {
                const dashRes = await fetch('/worker/dashboard', { credentials: 'same-origin' });
                if (dashRes && dashRes.ok) {
                    await cache.put('/worker/dashboard', dashRes.clone());
                    await cache.put(self.location.origin + '/worker/dashboard', dashRes);
                }
            } catch (e) {}
        }).then(() => self.skipWaiting())
    );
});

// Activate Event
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Event
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // 1. Pass through Vite development server requests natively
    if (url.port === '5173' || url.pathname.startsWith('/@vite') || url.pathname.startsWith('/@fs')) {
        return;
    }

    // 2. Only handle GET requests
    if (request.method !== 'GET') {
        return;
    }

    // 3. Navigation Requests (HTML Pages) — Worker routes only
    if (request.mode === 'navigate') {
        if (!url.pathname.startsWith('/worker')) {
            return;
        }

        event.respondWith(
            (async () => {
                // Online: try network first
                try {
                    const networkResponse = await fetch(request);
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        const cache = await caches.open(CACHE_NAME);
                        if (url.pathname.startsWith('/worker/job-orders') || url.pathname === '/worker/dashboard') {
                            cache.put(request, responseToCache);
                            cache.put(url.pathname, responseToCache.clone());
                        }
                    }
                    return networkResponse;
                } catch (networkError) {
                    // Offline fallback:

                    // If requesting job orders or dashboard, load cached version
                    if (url.pathname.startsWith('/worker/job-orders') || url.pathname === '/worker/dashboard') {
                        let cachedResponse = await caches.match(request, { ignoreSearch: true });
                        if (cachedResponse) return cachedResponse;

                        cachedResponse = await caches.match(url.pathname, { ignoreSearch: true });
                        if (cachedResponse) return cachedResponse;

                        if (url.pathname === '/worker/job-orders' || url.pathname.startsWith('/worker/job-orders?')) {
                            let joMatch = await caches.match('/worker/job-orders');
                            if (joMatch) return joMatch;
                        }
                    }

                    // For live-only pages (Messages, Notifications, Units, Profile) or uncached pages, return offline HTML
                    const cachedOffline = await caches.match('/offline.html');
                    if (cachedOffline) {
                        return cachedOffline;
                    }

                    // Guaranteed response fallback
                    return new Response(INLINE_OFFLINE_HTML, {
                        status: 200,
                        headers: { 'Content-Type': 'text/html; charset=utf-8' }
                    });
                }
            })()
        );
        return;
    }

    // 4. Static Assets (CSS, JS, Fonts, Images)
    const isSameOriginAsset = (url.origin === self.location.origin) && (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/images/') ||
        url.pathname.startsWith('/js/') ||
        url.pathname.startsWith('/livewire/') ||
        /\.(css|js|woff2|woff|ttf|png|jpg|jpeg|svg|gif|webp|ico)(\?.*)?$/i.test(url.pathname)
    );

    const isFontAsset = url.hostname.includes('googleapis.com') || url.hostname.includes('gstatic.com');

    if (isSameOriginAsset || isFontAsset) {
        event.respondWith(
            (async () => {
                const cachedResponse = await caches.match(request, { ignoreSearch: true });
                if (cachedResponse) {
                    return cachedResponse;
                }

                const pathMatch = await caches.match(url.pathname, { ignoreSearch: true });
                if (pathMatch) {
                    return pathMatch;
                }

                try {
                    const networkResponse = await fetch(request);
                    if (networkResponse && (networkResponse.status === 200 || networkResponse.type === 'opaque')) {
                        const cache = await caches.open(CACHE_NAME);
                        cache.put(request, networkResponse.clone());
                        cache.put(url.pathname, networkResponse.clone());
                    }
                    return networkResponse;
                } catch (err) {
                    const cache = await caches.open(CACHE_NAME);
                    const keys = await cache.keys();

                    if (request.destination === 'style' || url.pathname.endsWith('.css') || url.pathname.includes('.css')) {
                        for (const key of keys) {
                            if (key.url.includes('.css')) {
                                const match = await cache.match(key);
                                if (match) return match;
                            }
                        }
                    }

                    if (request.destination === 'script' || url.pathname.endsWith('.js') || url.pathname.includes('.js')) {
                        for (const key of keys) {
                            if (key.url.includes('.js')) {
                                const match = await cache.match(key);
                                if (match) return match;
                            }
                        }
                    }

                    return new Response('', { status: 408, statusText: 'Offline Asset Unavailable' });
                }
            })()
        );
        return;
    }
});
