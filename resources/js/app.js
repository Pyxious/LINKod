
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import './echo';
import { initRealtimeNotifications, showNotificationToast, updateNotificationBadges, updateMessagesBadge, prependNotificationToDropdown } from './supabase';

window.Chart = Chart;

if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}

// Expose helpers globally if needed by blade templates
window.LINKodRealtime = {
    showNotificationToast,
    updateNotificationBadges,
    updateMessagesBadge,
    prependNotificationToDropdown,
};

document.addEventListener('DOMContentLoaded', () => {
    const userIdMeta = document.querySelector('meta[name="user-id"]');
    if (userIdMeta) {
        const userId = userIdMeta.getAttribute('content');
        
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
                });
        }
    }
});

// Prevent rapid duplicate clicks and show loading animation on forms with auto-reset safety
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

        const originalHtml = submitBtn.innerHTML;
        const originalDisabled = submitBtn.disabled;
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

        // Safety guard: Limit animation to 2.5 - 3 seconds so buttons never get permanently stuck
        setTimeout(() => {
            if (submitBtn && submitBtn.isConnected) {
                submitBtn.disabled = originalDisabled;
                submitBtn.classList.remove('opacity-75', 'cursor-not-allowed', 'pointer-events-none');
                submitBtn.innerHTML = originalHtml;
            }
        }, 3000);
    }, 15);
});

// Reset any disabled/loading buttons when navigating or returning to page
window.addEventListener('pageshow', () => {
    document.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(btn => {
        btn.disabled = false;
        btn.classList.remove('opacity-75', 'cursor-not-allowed', 'pointer-events-none');
    });
});
