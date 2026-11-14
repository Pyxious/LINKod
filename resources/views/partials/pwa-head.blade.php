@php
    $pwaAuthUser = auth()->user();
    $pwaUserId   = $pwaAuthUser ? (string) $pwaAuthUser->user_id : 'guest';
    $pwaUserRole = $pwaAuthUser ? (string) $pwaAuthUser->role : 'guest';
    $pwaHomeUrl  = match ($pwaUserRole) {
        'admin'  => '/admin/dashboard',
        'worker' => '/worker/dashboard',
        'client' => '/client/dashboard',
        default  => '/',
    };
@endphp
<meta name="theme-color" content="#0033a0">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="LINKod">
<link rel="manifest" href="{{ asset('manifest.json') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/pwa-icon-192.png') }}">
<script>
    (function () {
        var currentUserId = @json($pwaUserId);
        var currentUserRole = @json($pwaUserRole);
        var currentHomeUrl = @json($pwaHomeUrl);

        try {
            var storedUserId = localStorage.getItem('linkod_pwa_user_id');
            var storedUserRole = localStorage.getItem('linkod_pwa_user_role');

            // If offline, this HTML may be served from Cache Storage — never overwrite stored user context from cached HTML
            if (navigator.onLine === false) {
                if (storedUserRole && storedUserRole !== 'worker' && window.location.pathname.indexOf('/worker') === 0) {
                    window.location.replace('/offline.html');
                }
                return;
            }

            localStorage.setItem('linkod_pwa_user_id', currentUserId);
            localStorage.setItem('linkod_pwa_user_role', currentUserRole);
            localStorage.setItem('linkod_pwa_home_url', currentHomeUrl);

            if (window.location.pathname !== '/offline.html') {
                localStorage.setItem('linkod_pwa_last_url', window.location.pathname + window.location.search);
            }

            var accountChanged = (storedUserId && storedUserId !== currentUserId) || (storedUserRole && storedUserRole !== currentUserRole);
            var shouldPurgeWorkerCache = accountChanged || currentUserRole !== 'worker';

            if (shouldPurgeWorkerCache && 'caches' in window) {
                caches.keys().then(function (cacheNames) {
                    cacheNames.forEach(function (cacheName) {
                        caches.open(cacheName).then(function (cache) {
                            cache.keys().then(function (requests) {
                                requests.forEach(function (req) {
                                    try {
                                        var u = new URL(req.url);
                                        if (u.pathname.indexOf('/worker') === 0) {
                                            cache.delete(req);
                                        }
                                    } catch (e) {}
                                });
                            });
                        });
                    });
                });
            }

            if (accountChanged && 'indexedDB' in window) {
                try {
                    indexedDB.deleteDatabase('linkod_offline_db');
                } catch (e) {}
            }
        } catch (e) {}

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js', { scope: '/' }).then(function (reg) {
                    if (currentUserRole !== 'worker' && reg.active) {
                        reg.active.postMessage({ type: 'CLEAR_WORKER_CACHE' });
                    }
                }).catch(function () {});
            });
        }

        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (form && form.action && form.action.indexOf('/logout') !== -1) {
                try {
                    localStorage.setItem('linkod_pwa_user_id', 'guest');
                    localStorage.setItem('linkod_pwa_user_role', 'guest');
                    localStorage.setItem('linkod_pwa_home_url', '/');
                    localStorage.setItem('linkod_pwa_last_url', '/');
                    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                        navigator.serviceWorker.controller.postMessage({ type: 'CLEAR_WORKER_CACHE' });
                    }
                } catch (err) {}
            }
        });
    })();
</script>
