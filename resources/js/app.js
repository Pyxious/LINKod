
import './echo';

document.addEventListener('DOMContentLoaded', () => {
    const userIdMeta = document.querySelector('meta[name="user-id"]');
    if (userIdMeta && window.Echo) {
        const userId = userIdMeta.getAttribute('content');
        
        window.Echo.private(`App.Models.User.${userId}`)
            .listen('.NotificationSent', (e) => {
                const notification = e.notification;
                // Use alert for a simple demonstration of real-time push,
                // or if sweetalert2 is available, we could use that.
                if (window.Swal) {
                    window.Swal.fire({
                        title: notification.title,
                        text: notification.message,
                        icon: 'info',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000
                    });
                } else {
                    alert(`New Notification: ${notification.title}\n${notification.message}`);
                }
            });
    }
});
