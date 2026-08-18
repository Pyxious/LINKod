import { createClient } from '@supabase/supabase-js';

// Dynamically resolve URL & Key from DOM meta tags (runtime from Laravel/Render) or Vite env
function getSupabaseCredentials() {
    let url = null;
    let key = null;

    if (typeof document !== 'undefined') {
        const metaUrl = document.querySelector('meta[name="supabase-url"]')?.getAttribute('content');
        const metaKey = document.querySelector('meta[name="supabase-anon-key"]')?.getAttribute('content');

        if (metaUrl && !metaUrl.startsWith('<') && metaUrl.trim() !== '') url = metaUrl.trim();
        if (metaKey && !metaKey.startsWith('<') && metaKey.trim() !== '') key = metaKey.trim();
    }

    if (!url) url = import.meta.env.VITE_SUPABASE_URL || 'https://jqybammjfhvvucmgeriv.supabase.co';
    if (!key) key = import.meta.env.VITE_SUPABASE_ANON_KEY || 'sb_publishable_3T8-Do-mEtls5KnFpeDbEg_Cw36IGaK';

    return { url, key };
}

const { url: supabaseUrl, key: supabaseAnonKey } = getSupabaseCredentials();

export const supabase = (supabaseUrl && supabaseAnonKey)
    ? createClient(supabaseUrl, supabaseAnonKey, {
        realtime: {
            params: {
                eventsPerSecond: 10,
            },
        },
    })
    : null;

if (typeof window !== 'undefined') {
    window.supabaseClient = supabase;
}

if (!supabase) {
    console.warn('[Supabase Realtime] Realtime disabled: Missing Supabase URL or Anon Key.');
} else {
    console.log('[Supabase Realtime] Initialized client for:', supabaseUrl);
}

/**
 * Play subtle notification sound if allowed
 */
function playNotificationChime() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
        osc.frequency.exponentialRampToValueAtTime(880, audioCtx.currentTime + 0.15); // A5
        gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.35);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.35);
    } catch (e) {
        // Audio context may be restricted before user gesture
    }
}

/**
 * Subtle audio notification chime (floating toast popups disabled per user preference)
 */
export function showNotificationToast(title, message, actionUrl = null) {
    playNotificationChime();
    
    // Clean up any remaining toast containers from DOM
    const oldContainer = document.getElementById('linkod-realtime-toast-container');
    if (oldContainer) oldContainer.remove();
}

/**
 * Increment or create unread badges in UI
 */
export function updateNotificationBadges(incrementBy = 1) {
    const badgeElements = document.querySelectorAll('[data-notification-badge]');
    badgeElements.forEach((badge) => {
        const currentCount = parseInt(badge.textContent.trim()) || 0;
        const newCount = currentCount + incrementBy;
        badge.textContent = newCount > 99 ? '99+' : newCount;
        badge.classList.remove('hidden');
    });

    const headerCounts = document.querySelectorAll('[data-notification-header-count]');
    headerCounts.forEach((hc) => {
        const currentCount = parseInt(hc.textContent.trim()) || 0;
        const newCount = currentCount + incrementBy;
        hc.textContent = `${newCount} New`;
        hc.classList.remove('hidden');
    });
}

/**
 * Prepend a newly received notification to dropdown lists
 */
export function prependNotificationToDropdown(notification) {
    const listContainers = document.querySelectorAll('[data-notification-list]');
    listContainers.forEach((list) => {
        const emptyState = list.querySelector('[data-notification-empty]');
        if (emptyState) emptyState.remove();

        const readUrl = notification.notification_id 
            ? `/notifications/${notification.notification_id}/read` 
            : (notification.action_url || '#');

        const item = document.createElement('a');
        item.href = notification.action_url || readUrl;
        item.className = 'block px-4 py-3 bg-blue-50/60 dark:bg-zinc-800/50 hover:bg-blue-100/60 dark:hover:bg-zinc-800/80 transition border-b border-gray-100 dark:border-zinc-800';
        item.innerHTML = `
            <div class="flex items-start gap-2.5">
                <div class="w-2 h-2 mt-1.5 rounded-full shrink-0 bg-[#0033a0] dark:bg-blue-400 animate-pulse"></div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-baseline gap-2 mb-0.5">
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">
                            ${notification.title || 'Notification'}
                        </h4>
                        <span class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold shrink-0">
                            Just now
                        </span>
                    </div>
                    <p class="text-[11px] text-gray-600 dark:text-gray-300 line-clamp-2 leading-relaxed">
                        ${notification.message || ''}
                    </p>
                </div>
            </div>
        `;
        list.prepend(item);
    });
}

/**
 * Increment or decrement unread message badges in sidebar/menu
 */
export function updateMessagesBadge(incrementBy = 1) {
    const badgeElements = document.querySelectorAll('[data-messages-badge]');
    badgeElements.forEach((badge) => {
        const currentCount = parseInt(badge.textContent.trim()) || 0;
        const newCount = Math.max(0, currentCount + incrementBy);
        badge.textContent = newCount > 99 ? '99+' : newCount;
        if (newCount > 0) {
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    });
}

/**
 * Subscribe to realtime notifications & global messages for the authenticated user
 */
export function initRealtimeNotifications(userId) {
    if (!supabase || !userId) return null;

    // 1. Channel for request-related notifications (General Bell)
    supabase
        .channel(`user-notifications-${userId}`)
        .on(
            'postgres_changes',
            {
                event: 'INSERT',
                schema: 'public',
                table: 'notification',
                filter: `user_id=eq.${userId}`
            },
            (payload) => {
                const notif = payload.new;
                // Exclude message notifications from general notification bell
                if (notif.type === 'new_message') {
                    return;
                }
                updateNotificationBadges(1);
                prependNotificationToDropdown(notif);
                playNotificationChime();
            }
        )
        .subscribe();

    // 2. Channel for real-time unread messages badge on sidebar
    return supabase
        .channel(`user-messages-counter-${userId}`)
        .on(
            'postgres_changes',
            {
                event: 'INSERT',
                schema: 'public',
                table: 'request_messages'
            },
            (payload) => {
                const msg = payload.new;
                if (msg && parseInt(msg.sender_id) !== parseInt(userId)) {
                    // Check if user is currently viewing this exact conversation
                    const activeFeed = document.querySelector(`[data-request-id="${msg.request_id}"]`);
                    if (!activeFeed) {
                        updateMessagesBadge(1);
                        playNotificationChime();
                    }
                }
            }
        )
        .subscribe((status) => {
            if (status === 'SUBSCRIBED') {
                console.log(`[Supabase Realtime] Subscribed to real-time events for user ${userId}`);
            }
        });
}

/**
 * Subscribe to realtime messages for a specific service request
 */
export function initRealtimeMessages(requestId, currentUserId, onMessageReceived) {
    if (!supabase || !requestId) return null;

    return supabase
        .channel(`request-messages-${requestId}`)
        .on(
            'postgres_changes',
            {
                event: 'INSERT',
                schema: 'public',
                table: 'request_messages',
                filter: `request_id=eq.${requestId}`
            },
            (payload) => {
                const msg = payload.new;
                if (onMessageReceived) {
                    onMessageReceived(msg);
                }
            }
        )
        .subscribe((status) => {
            if (status === 'SUBSCRIBED') {
                console.log(`[Supabase Realtime] Subscribed to messages for request #${requestId}`);
            }
        });
}

/**
 * Subscribe to request status & timeline changes
 */
export function initRealtimeRequestStatus(requestId, onStatusUpdated) {
    if (!supabase || !requestId) return null;

    return supabase
        .channel(`request-status-${requestId}`)
        .on(
            'postgres_changes',
            {
                event: 'UPDATE',
                schema: 'public',
                table: 'request',
                filter: `request_id=eq.${requestId}`
            },
            (payload) => {
                if (onStatusUpdated) onStatusUpdated(payload.new);
            }
        )
        .subscribe((status) => {
            if (status === 'SUBSCRIBED') {
                console.log(`[Supabase Realtime] Subscribed to status updates for request #${requestId}`);
            }
        });
}
