/**
 * LINKod Worker Offline Engine & Sync Manager
 * Handles IndexedDB storage, offline outbox queueing, photo blob persistence, and background sync.
 */
(function () {
    const DB_NAME = 'LINKodWorkerDB';
    const DB_VERSION = 1;
    let dbInstance = null;
    let isSyncing = false;

    // Open or upgrade IndexedDB
    function getDB() {
        return new Promise((resolve, reject) => {
            if (dbInstance) return resolve(dbInstance);

            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onupgradeneeded = (e) => {
                const db = e.target.result;
                if (!db.objectStoreNames.contains('outbox')) {
                    const outboxStore = db.createObjectStore('outbox', { keyPath: 'id', autoIncrement: true });
                    outboxStore.createIndex('projectId', 'projectId', { unique: false });
                    outboxStore.createIndex('createdAt', 'createdAt', { unique: false });
                }
                if (!db.objectStoreNames.contains('cachedJobs')) {
                    db.createObjectStore('cachedJobs', { keyPath: 'projectId' });
                }
            };

            request.onsuccess = (e) => {
                dbInstance = e.target.result;
                resolve(dbInstance);
            };

            request.onerror = (e) => {
                console.error('[LINKod Offline] IndexedDB error:', e.target.error);
                reject(e.target.error);
            };
        });
    }

    // Get CSRF Token from meta tag
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // Add item to Outbox (deduplicates by projectId and status to prevent multiple uploads)
    async function addToOutbox(item) {
        const db = await getDB();
        const pending = await getAllOutbox();

        // Check if an item for the exact same project and status is already queued
        const existing = pending.find(p => String(p.projectId) === String(item.projectId) && p.status === item.status);

        return new Promise((resolve, reject) => {
            const tx = db.transaction('outbox', 'readwrite');
            const store = tx.objectStore('outbox');
            let req;
            if (existing && existing.id) {
                item.id = existing.id;
                req = store.put(item);
            } else {
                req = store.add(item);
            }
            req.onsuccess = () => {
                resolve(req.result);
                updateUIState();
            };
            req.onerror = () => reject(req.error);
        });
    }

    // Get all pending Outbox items
    async function getAllOutbox() {
        const db = await getDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction('outbox', 'readonly');
            const store = tx.objectStore('outbox');
            const req = store.getAll();
            req.onsuccess = () => resolve(req.result || []);
            req.onerror = () => reject(req.error);
        });
    }

    // Delete item from Outbox
    async function deleteFromOutbox(id) {
        const db = await getDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction('outbox', 'readwrite');
            const store = tx.objectStore('outbox');
            const req = store.delete(id);
            req.onsuccess = () => resolve();
            req.onerror = () => reject(req.error);
        });
    }

    // Update UI Elements (Offline Banner, Sync Counter Badge, etc.)
    async function updateUIState() {
        const isOnline = navigator.onLine;
        const banner = document.getElementById('linkod-offline-banner');
        const syncBadge = document.querySelectorAll('[data-offline-sync-badge]');
        const syncBtn = document.getElementById('linkod-sync-now-btn');

        let pending = [];
        try {
            pending = await getAllOutbox();
        } catch (e) {
            console.warn('[LINKod Offline] Error getting outbox count:', e);
        }

        const count = pending.length;

        // Banner visibility
        if (banner) {
            if (!isOnline) {
                banner.classList.remove('hidden');
                const textEl = banner.querySelector('[data-offline-text]');
                if (textEl) {
                    textEl.textContent = count > 0 
                        ? `You are offline. ${count} task update(s) saved locally and ready to sync.`
                        : `You are working offline. Any changes will be saved to your device.`;
                }
            } else {
                if (count > 0) {
                    banner.classList.remove('hidden');
                    const textEl = banner.querySelector('[data-offline-text]');
                    if (textEl) {
                        textEl.textContent = `Online now. ${count} offline update(s) waiting to sync.`;
                    }
                } else {
                    banner.classList.add('hidden');
                }
            }
        }

        // Sync Pill / Badges
        syncBadge.forEach((el) => {
            if (count > 0) {
                el.classList.remove('hidden');
                el.textContent = `${count}`;
            } else {
                el.classList.add('hidden');
            }
        });

        // Sync Now Button
        if (syncBtn) {
            if (count > 0 && isOnline) {
                syncBtn.classList.remove('hidden');
            } else {
                syncBtn.classList.add('hidden');
            }
        }

        // Job orders page indicator
        const jobIndicator = document.getElementById('job-orders-sync-indicator');
        if (jobIndicator) {
            if (count > 0) {
                jobIndicator.classList.remove('hidden');
            } else {
                jobIndicator.classList.add('hidden');
            }
        }
    }

    // Trigger Outbox Sync to Laravel Backend
    async function syncOutbox() {
        if (isSyncing) return;
        if (!navigator.onLine) {
            console.log('[LINKod Offline] Sync skipped — device is offline');
            return;
        }

        const pendingItems = await getAllOutbox();
        if (pendingItems.length === 0) {
            updateUIState();
            return;
        }

        isSyncing = true;
        showSyncToast('Syncing offline updates...', 'info');

        const banner = document.getElementById('linkod-offline-banner');
        if (banner) {
            const textEl = banner.querySelector('[data-offline-text]');
            if (textEl) textEl.textContent = `🔄 Syncing ${pendingItems.length} update(s) with server...`;
        }

        let syncedCount = 0;
        let failedCount = 0;
        const csrfToken = getCsrfToken();

        for (const item of pendingItems) {
            try {
                const formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('status', item.status);
                if (item.completionType) formData.append('completion_type', item.completionType);
                if (item.natureOfWork) formData.append('nature_of_work', item.natureOfWork);
                if (item.recommendation) formData.append('recommendation', item.recommendation);
                if (item.offlinePerformedAt) formData.append('offline_performed_at', item.offlinePerformedAt);

                if (item.photoBlob) {
                    const filename = item.photoName || 'proof_' + Date.now() + '.jpg';
                    const fileObj = new File([item.photoBlob], filename, { type: item.photoBlob.type || 'image/jpeg' });
                    formData.append('proof', fileObj);
                }

                const syncUrl = item.syncUrl || `/worker/job-orders/${item.projectId}/sync-progress`;

                const response = await fetch(syncUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        await deleteFromOutbox(item.id);
                        syncedCount++;
                    } else {
                        console.error('[LINKod Offline] Server rejected sync:', result);
                        failedCount++;
                    }
                } else if (response.status === 422) {
                    // Validation failure
                    const errorJson = await response.json().catch(() => ({}));
                    console.error('[LINKod Offline] Validation error during sync:', errorJson);
                    // Remove or mark so it doesn't loop infinitely
                    await deleteFromOutbox(item.id);
                    failedCount++;
                } else {
                    console.warn(`[LINKod Offline] HTTP error ${response.status} during sync`);
                    failedCount++;
                    break; // Stop and retry later if server error or connection lost
                }
            } catch (err) {
                console.error('[LINKod Offline] Network error during sync item:', err);
                failedCount++;
                break; // Stop loop on network failure
            }
        }

        isSyncing = false;
        await updateUIState();

        if (syncedCount > 0) {
            showSyncToast(`✅ Successfully synced ${syncedCount} offline task update(s)!`, 'success');
            // If on the show page of one of the synced projects, reload to reflect fresh DB state
            setTimeout(() => {
                if (window.location.pathname.includes('/worker/job-orders/')) {
                    window.location.reload();
                }
            }, 1200);
        } else if (failedCount > 0 && navigator.onLine) {
            showSyncToast(`⚠️ Could not sync some updates. Will retry shortly.`, 'warning');
        }
    }

    // Helper to show sync toast notifications
    function showSyncToast(message, type = 'info') {
        const existingToast = document.getElementById('linkod-sync-toast');
        if (existingToast) existingToast.remove();

        const toast = document.createElement('div');
        toast.id = 'linkod-sync-toast';
        toast.className = `fixed bottom-5 right-5 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-2xl text-xs sm:text-sm font-bold text-white transition-all transform duration-300 translate-y-0 ${
            type === 'success' ? 'bg-emerald-600' : (type === 'warning' ? 'bg-amber-600' : 'bg-[#0033a0]')
        }`;
        toast.innerHTML = `
            <span>${message}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-white/80 hover:text-white text-base leading-none">&times;</button>
        `;

        document.body.appendChild(toast);
        setTimeout(() => {
            if (toast && toast.parentElement) {
                toast.classList.add('opacity-0', 'translate-y-4');
                setTimeout(() => toast.remove(), 300);
            }
        }, 4000);
    }

    // Initialize Service Worker and Listeners
    function init() {
        // 1. Register Service Worker if supported
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then((reg) => {
                    reg.update();
                    console.log('[LINKod ServiceWorker] Registered & updated scope:', reg.scope);
                })
                .catch((err) => {
                    console.warn('[LINKod ServiceWorker] Registration failed:', err);
                });
        }

        // 2. Setup network event listeners
        window.addEventListener('online', () => {
            console.log('[LINKod Network] Device is now ONLINE');
            updateUIState();
            syncOutbox();
            precacheActivePageAssets();
            precacheWorkerPages();
        });

        window.addEventListener('offline', () => {
            console.log('[LINKod Network] Device is now OFFLINE');
            updateUIState();
        });

        // 3. Initial UI update, sync check, and proactive asset & page caching
        document.addEventListener('DOMContentLoaded', () => {
            updateUIState();
            if (navigator.onLine) {
                syncOutbox();
                precacheActivePageAssets();
                precacheWorkerPages();
            }
        });
    }

    // Proactively pre-cache current stylesheets, scripts, and logos for offline use
    async function precacheActivePageAssets() {
        if (!('caches' in window) || !navigator.onLine) return;
        try {
            const cache = await caches.open('linkod-worker-v9');
            const stylesheets = Array.from(document.querySelectorAll('link[rel="stylesheet"]')).map(el => el.href).filter(Boolean);
            const scripts = Array.from(document.querySelectorAll('script[src]')).map(el => el.src).filter(Boolean);
            const images = Array.from(document.querySelectorAll('img[src]')).map(el => el.src).filter(Boolean);

            const allAssets = [...new Set([...stylesheets, ...scripts, ...images])];
            const sameOriginAssets = allAssets.filter(urlStr => {
                try {
                    const u = new URL(urlStr);
                    // Do not touch Vite dev port (5173) in pre-caching
                    if (u.port === '5173') return false;
                    return u.origin === window.location.origin || u.hostname.includes('googleapis.com') || u.hostname.includes('gstatic.com');
                } catch (e) {
                    return false;
                }
            });

            for (const assetUrl of sameOriginAssets) {
                try {
                    const match = await cache.match(assetUrl);
                    if (!match) {
                        const res = await fetch(assetUrl).catch(() => null);
                        if (res && (res.ok || res.type === 'opaque')) {
                            await cache.put(assetUrl, res);
                        }
                    }
                } catch (e) {}
            }
        } catch (e) {
            console.warn('[LINKod Offline] Asset pre-caching notice:', e);
        }
    }

    // Proactively pre-cache worker views & all assigned job order pages
    async function precacheWorkerPages() {
        if (!('caches' in window) || !navigator.onLine) return;
        try {
            const cache = await caches.open('linkod-worker-v9');

            // 1. Cache main index routes
            const mainRoutes = ['/worker/job-orders', '/worker/dashboard'];
            for (const route of mainRoutes) {
                try {
                    const res = await fetch(route, { credentials: 'same-origin' });
                    if (res && res.ok) {
                        await cache.put(route, res.clone());
                        await cache.put(window.location.origin + route, res);
                    }
                } catch (err) {}
            }

            // 2. Cache all job order show pages linked on page
            const jobLinks = Array.from(document.querySelectorAll('a[href*="/worker/job-orders/"]'))
                .map(a => a.getAttribute('href'))
                .filter(href => href && href.match(/\/worker\/job-orders\/\d+/));

            const uniqueLinks = [...new Set(jobLinks)];
            for (const link of uniqueLinks) {
                try {
                    const match = await cache.match(link);
                    if (!match) {
                        const res = await fetch(link, { credentials: 'same-origin' });
                        if (res && res.ok) {
                            await cache.put(link, res.clone());
                            await cache.put(window.location.origin + link, res);
                        }
                    }
                } catch (err) {}
            }
        } catch (e) {
            console.warn('[LINKod Offline] Page pre-caching notice:', e);
        }
    }

    // Expose global API
    window.LINKodOffline = {
        init,
        addToOutbox,
        getAllOutbox,
        syncOutbox,
        updateUIState,
        showSyncToast
    };

    // Auto-init
    init();
})();
