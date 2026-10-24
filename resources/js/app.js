
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import './echo';
import { initRealtimeNotifications, showNotificationToast, updateNotificationBadges, updateMessagesBadge, prependNotificationToDropdown } from './supabase';

window.Chart = Chart;

if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}

// Real-Time In-Place Page Sync Engine (Reverb + Supabase)
let realtimeRefreshTimer = null;
let realtimeIsFetching = false;
let realtimePendingRefresh = false;
let realtimeDeferredRetryTimer = null;

function isContainerBusy(container) {
    if (!container) return false;

    // 1. Check explicit pause attribute (e.g. open modal or active editor)
    if (container.getAttribute('data-realtime-pause') === 'true' || container.querySelector('[data-realtime-pause="true"]')) {
        return true;
    }

    // 2. Check if user is focused on an input/textarea/select inside this container
    const active = document.activeElement;
    if (active && container.contains(active)) {
        const tag = (active.tagName || '').toUpperCase();
        if (tag === 'TEXTAREA' || tag === 'SELECT' || active.isContentEditable) {
            return true;
        }
        if (tag === 'INPUT') {
            const type = (active.type || 'text').toLowerCase();
            if (!['hidden', 'submit', 'button', 'reset'].includes(type)) {
                return true;
            }
        }
    }

    // 3. Check if any file input inside this container has a selected file
    const fileInputs = container.querySelectorAll('input[type="file"]');
    for (const fi of fileInputs) {
        if (fi.files && fi.files.length > 0) {
            return true;
        }
    }

    return false;
}

function doesContainerMatchEvent(container, payload = {}) {
    if (!payload || (!payload.request_id && !payload.project_id)) {
        return true;
    }

    const filterReqId = container.getAttribute('data-realtime-request-id');
    const filterProjId = container.getAttribute('data-realtime-project-id');

    // Global containers (dashboards, index pages, header counters) have no specific ID filter
    if (!filterReqId && !filterProjId) {
        return true;
    }

    if (filterReqId && payload.request_id && parseInt(filterReqId, 10) === parseInt(payload.request_id, 10)) {
        return true;
    }

    if (filterProjId && payload.project_id && parseInt(filterProjId, 10) === parseInt(payload.project_id, 10)) {
        return true;
    }

    // If payload only had project_id and container only has request_id (or vice-versa), allow refresh
    if (filterReqId && !payload.request_id && payload.project_id && !filterProjId) {
        return true;
    }
    if (filterProjId && !payload.project_id && payload.request_id && !filterReqId) {
        return true;
    }

    return false;
}

async function performRealtimeDomRefresh() {
    const containers = Array.from(document.querySelectorAll('[data-realtime-container]'));
    if (containers.length === 0) return;

    if (realtimeIsFetching) {
        realtimePendingRefresh = true;
        return;
    }

    realtimeIsFetching = true;
    let hadBusyContainer = false;

    try {
        const response = await fetch(window.location.href, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
            credentials: 'same-origin',
            cache: 'no-store',
        });

        if (!response.ok) return;

        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        for (const currentEl of containers) {
            const key = currentEl.getAttribute('data-realtime-container');
            if (!key) continue;

            const incomingEl = doc.querySelector(`[data-realtime-container="${key}"]`);
            if (!incomingEl) continue;

            if (isContainerBusy(currentEl)) {
                hadBusyContainer = true;
                continue;
            }

            // Skip DOM mutation if nothing changed
            if (
                currentEl.innerHTML.trim() === incomingEl.innerHTML.trim() &&
                currentEl.className === incomingEl.className &&
                currentEl.getAttribute('data-initial-items') === incomingEl.getAttribute('data-initial-items')
            ) {
                continue;
            }

            // Sync attributes (class, data-*, etc.)
            Array.from(incomingEl.attributes).forEach((attr) => {
                if (currentEl.getAttribute(attr.name) !== attr.value) {
                    currentEl.setAttribute(attr.name, attr.value);
                }
            });

            currentEl.innerHTML = incomingEl.innerHTML;

            if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                try {
                    window.Alpine.initTree(currentEl);
                } catch (err) {
                    console.warn('[LINKod Realtime] Alpine initTree warning:', err);
                }
            }
        }

        // Update Admin Dashboard Donut Chart if present
        const chartDataEl = doc.querySelector('[data-chart-values]');
        if (chartDataEl && window.adminTaskStatusChart) {
            try {
                const newValues = JSON.parse(chartDataEl.getAttribute('data-chart-values') || '[]');
                if (Array.isArray(newValues) && newValues.length > 0) {
                    window.adminTaskStatusChart.data.datasets[0].data = newValues;
                    window.adminTaskStatusChart.update();
                }
            } catch (err) {
                // Ignore chart parse errors
            }
        }

        window.dispatchEvent(new CustomEvent('linkod:page-refreshed', { detail: { doc } }));
    } catch (err) {
        console.warn('[LINKod Realtime] In-place refresh error:', err);
    } finally {
        realtimeIsFetching = false;

        if (realtimePendingRefresh) {
            realtimePendingRefresh = false;
            triggerRealtimePageRefresh({}, 250);
        } else if (hadBusyContainer) {
            clearTimeout(realtimeDeferredRetryTimer);
            realtimeDeferredRetryTimer = setTimeout(() => {
                triggerRealtimePageRefresh({}, 100);
            }, 2500);
        }
    }
}

export function triggerRealtimePageRefresh(payload = {}, debounceMs = 250) {
    // 1. Trigger Livewire table refresh if present on page
    if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
        try {
            window.Livewire.dispatch('refreshRequests');
        } catch (err) {
            // Ignore Livewire dispatch errors if no listener
        }
    }

    // 2. Check if any [data-realtime-container] on the page matches this event
    const containers = Array.from(document.querySelectorAll('[data-realtime-container]'));
    const hasMatchingContainer = containers.some((el) => doesContainerMatchEvent(el, payload));

    if (!hasMatchingContainer) return;

    clearTimeout(realtimeRefreshTimer);
    realtimeRefreshTimer = setTimeout(() => {
        performRealtimeDomRefresh();
    }, debounceMs);
}

// Expose helpers globally if needed by blade templates
window.LINKodRealtime = {
    showNotificationToast,
    updateNotificationBadges,
    updateMessagesBadge,
    prependNotificationToDropdown,
    refreshNow: (payload = {}) => triggerRealtimePageRefresh(payload, 50),
};

document.addEventListener('DOMContentLoaded', () => {
    const userIdMeta = document.querySelector('meta[name="user-id"]');
    const userId = userIdMeta ? userIdMeta.getAttribute('content') : null;

    if (userId) {
        // Initialize Supabase Realtime Notifications
        initRealtimeNotifications(userId);

        // Fallback / Support for Laravel Echo / Reverb if configured
        if (window.Echo) {
            window.Echo.private(`App.Models.User.${userId}`)
                .listen('.NotificationSent', (e) => {
                    const notification = e.notification;
                    updateNotificationBadges(1);
                    prependNotificationToDropdown(notification);
                    showNotificationToast(notification.title, notification.message, notification.action_url);
                    triggerRealtimePageRefresh({});
                });
        }
    }

    // Listen to Reverb global updates channel for instant page & BOM refreshes across all roles
    if (window.Echo) {
        window.Echo.channel('linkod.updates')
            .listen('.RequestUpdated', (e) => {
                triggerRealtimePageRefresh(e);
            });
    }

    // Also refresh when a Supabase notification arrives for the current user
    window.addEventListener('linkod:notification-received', () => {
        triggerRealtimePageRefresh({});
    });

    // Also subscribe to Supabase table changes as a dual-transport fallback if page has realtime containers or Livewire
    if (window.supabaseClient && (document.querySelector('[data-realtime-container]') || document.querySelector('[wire\\:id]'))) {
        window.supabaseClient
            .channel('linkod-global-dom-sync')
            .on('postgres_changes', { event: '*', schema: 'public', table: 'request' }, (payload) => {
                const row = payload.new || payload.old || {};
                triggerRealtimePageRefresh({ request_id: row.request_id });
            })
            .on('postgres_changes', { event: '*', schema: 'public', table: 'request_history' }, (payload) => {
                const row = payload.new || payload.old || {};
                triggerRealtimePageRefresh({ request_id: row.request_id });
            })
            .on('postgres_changes', { event: '*', schema: 'public', table: 'project' }, (payload) => {
                const row = payload.new || payload.old || {};
                triggerRealtimePageRefresh({ request_id: row.request_id, project_id: row.project_id });
            })
            .on('postgres_changes', { event: '*', schema: 'public', table: 'project_history' }, (payload) => {
                const row = payload.new || payload.old || {};
                triggerRealtimePageRefresh({ project_id: row.project_id });
            })
            .on('postgres_changes', { event: '*', schema: 'public', table: 'project_worker' }, (payload) => {
                const row = payload.new || payload.old || {};
                triggerRealtimePageRefresh({ project_id: row.project_id });
            })
            .on('postgres_changes', { event: '*', schema: 'public', table: 'bill_of_materials' }, (payload) => {
                const row = payload.new || payload.old || {};
                triggerRealtimePageRefresh({ project_id: row.project_id });
            })
            .on('postgres_changes', { event: '*', schema: 'public', table: 'evaluation' }, (payload) => {
                const row = payload.new || payload.old || {};
                triggerRealtimePageRefresh({ request_id: row.request_id });
            })
            .subscribe();
    }
});

// Prevent rapid duplicate clicks and show loading animation on forms
document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!form || form.tagName !== 'FORM') return;

    // If submission was already prevented by client validation or cancelled confirm dialog, do nothing
    if (e.defaultPrevented) return;

    const submitBtn = form.querySelector('button[type="submit"]:not([data-no-auto-loading]), input[type="submit"]:not([data-no-auto-loading])');
    if (!submitBtn || submitBtn.disabled) return;

    // Brief timeout allows any inline or framework validation to cancel submission before altering UI
    setTimeout(() => {
        if (e.defaultPrevented) return;

        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed', 'pointer-events-none');

        if (submitBtn.tagName === 'BUTTON') {
            const text = submitBtn.innerText.trim();
            let loadingText = 'Processing...';
            if (text.toLowerCase().includes('approve')) loadingText = 'Approving...';
            else if (text.toLowerCase().includes('reject')) loadingText = 'Rejecting...';
            else if (text.toLowerCase().includes('submit')) loadingText = 'Submitting...';
            else if (text.toLowerCase().includes('update') || text.toLowerCase().includes('start')) loadingText = 'Updating...';
            else if (text.toLowerCase().includes('verify')) loadingText = 'Verifying...';

            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>${loadingText}</span>
            `;
        }
    }, 15);
});

// Reset any disabled/loading buttons when navigating or returning to page
window.addEventListener('pageshow', () => {
    document.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(btn => {
        btn.disabled = false;
        btn.classList.remove('opacity-75', 'cursor-not-allowed', 'pointer-events-none');
    });
});
