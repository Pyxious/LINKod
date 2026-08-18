
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

// Prevent rapid duplicate clicks and show loading animation on forms
document.addEventListener('submit', (e) => {
    const form = e.target;
    if (form && form.tagName === 'FORM') {
        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submitBtn && !submitBtn.disabled) {
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
        }
    }
});
