// Breaking News Push Notification System
class PushNotificationManager {
    constructor() {
        this.subscription = null;
        this.isSupported = 'serviceWorker' in navigator && 'PushManager' in window;
        this.publicKey = 'BLbWq1qKjA9zL-9nHrN5vX7Y8T2uF3gH6jK9mP1qR4sT7wX2zV5bN8mQ3pL6kS9jW2'; // Replace with your VAPID public key
        this.apiEndpoint = 'api/breaking-news-alerts.php';
        this.init();
    }

    async init() {
        if (!this.isSupported) {
            console.warn('Push notifications are not supported in this browser');
            return;
        }

        try {
            // Only register service worker on HTTPS or localhost
            const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
            const isSecure = window.location.protocol === 'https:' || isLocalhost;
            
            if (!isSecure) {
                console.warn('Service Worker registration skipped: requires HTTPS or localhost');
                return;
            }
            
            // Register service worker
            const registration = await navigator.serviceWorker.register('/service-worker.js');
            console.log('Service Worker registered:', registration);

            // Check existing subscription
            this.subscription = await registration.pushManager.getSubscription();
            
            if (this.subscription) {
                console.log('Existing subscription found:', this.subscription);
                this.updateSubscriptionUI(true);
            }

            // Request notification permission
            const permission = await Notification.requestPermission();
            console.log('Notification permission:', permission);

            if (permission === 'granted') {
                this.setupMessageListener();
            }

        } catch (error) {
            console.error('Error initializing push notifications:', error);
        }
    }

    async subscribeToNotifications() {
        if (!this.isSupported) {
            this.showNotification('Push notifications are not supported in your browser', 'error');
            return false;
        }

        try {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                this.showNotification('Please allow notifications to receive breaking news alerts', 'warning');
                return false;
            }

            const registration = await navigator.serviceWorker.ready;
            
            // Create or update subscription
            this.subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.publicKey)
            });

            console.log('Push subscription created:', this.subscription);

            // Send subscription to server
            const response = await fetch(`${this.apiEndpoint}?action=subscribe`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(this.subscription)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            
            if (result && result.success) {
                this.showNotification('Successfully subscribed to breaking news alerts!', 'success');
                this.updateSubscriptionUI(true);
                return true;
            } else {
                throw new Error(result?.error || 'Failed to subscribe');
            }

        } catch (error) {
            console.error('Error subscribing to notifications:', error);
            this.showNotification('Failed to subscribe to notifications: ' + error.message, 'error');
            return false;
        }
    }

    async unsubscribeFromNotifications() {
        if (!this.subscription) {
            this.showNotification('No active subscription found', 'info');
            return false;
        }

        try {
            // Unsubscribe from push service
            await this.subscription.unsubscribe();

            // Remove from server
            const response = await fetch(`${this.apiEndpoint}?action=unsubscribe`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    endpoint: this.subscription.endpoint
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            
            if (result && result.success) {
                this.subscription = null;
                this.showNotification('Unsubscribed from breaking news alerts', 'success');
                this.updateSubscriptionUI(false);
                return true;
            } else {
                throw new Error(result?.error || 'Failed to unsubscribe');
            }

        } catch (error) {
            console.error('Error unsubscribing from notifications:', error);
            this.showNotification('Failed to unsubscribe: ' + error.message, 'error');
            return false;
        }
    }

    setupMessageListener() {
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data && event.data.type === 'PUSH_NOTIFICATION') {
                this.handlePushNotification(event.data.payload);
            }
        });
    }

    handlePushNotification(payload) {
        console.log('Received push notification:', payload);
        
        // Show custom notification UI
        this.showBreakingNewsAlert(payload);
        
        // Play notification sound
        this.playNotificationSound();
        
        // Update UI if needed
        this.updateBreakingNewsBadge();
    }

    showBreakingNewsAlert(payload) {
        // Create custom breaking news alert
        const alertContainer = document.createElement('div');
        alertContainer.className = 'breaking-news-alert';
        alertContainer.innerHTML = `
            <div class="alert-content">
                <div class="alert-header">
                    <span class="alert-icon">🚨</span>
                    <span class="alert-title">Breaking News</span>
                    <button class="alert-close" onclick="this.parentElement.parentElement.remove()">×</button>
                </div>
                <div class="alert-body">
                    <h4>${payload.title}</h4>
                    <p>${payload.body}</p>
                    <div class="alert-actions">
                        <button class="btn btn-primary btn-sm" onclick="window.open('${payload.data.url}', '_blank')">
                            Read More
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                            Dismiss
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Add styles if not already added
        if (!document.getElementById('breaking-news-alert-styles')) {
            const styles = document.createElement('style');
            styles.id = 'breaking-news-alert-styles';
            styles.textContent = `
                .breaking-news-alert {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    max-width: 400px;
                    background: #fff;
                    border-radius: 8px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                    border-left: 4px solid #dc3545;
                    animation: slideInRight 0.3s ease-out;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                }
                
                .alert-content {
                    padding: 0;
                }
                
                .alert-header {
                    display: flex;
                    align-items: center;
                    padding: 12px 16px;
                    background: #f8f9fa;
                    border-bottom: 1px solid #e9ecef;
                    border-radius: 8px 8px 0 0;
                }
                
                .alert-icon {
                    font-size: 18px;
                    margin-right: 8px;
                }
                
                .alert-title {
                    font-weight: 600;
                    color: #dc3545;
                    flex-grow: 1;
                }
                
                .alert-close {
                    background: none;
                    border: none;
                    font-size: 20px;
                    cursor: pointer;
                    color: #6c757d;
                    padding: 0;
                    width: 24px;
                    height: 24px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 50%;
                }
                
                .alert-close:hover {
                    background: #e9ecef;
                }
                
                .alert-body {
                    padding: 16px;
                }
                
                .alert-body h4 {
                    margin: 0 0 8px 0;
                    font-size: 16px;
                    font-weight: 600;
                    color: #212529;
                }
                
                .alert-body p {
                    margin: 0 0 16px 0;
                    font-size: 14px;
                    color: #6c757d;
                    line-height: 1.4;
                }
                
                .alert-actions {
                    display: flex;
                    gap: 8px;
                }
                
                .alert-actions .btn {
                    flex: 1;
                    padding: 6px 12px;
                    font-size: 13px;
                    border-radius: 4px;
                    border: none;
                    cursor: pointer;
                    font-weight: 500;
                    transition: all 0.2s;
                }
                
                .alert-actions .btn-primary {
                    background: #0d6efd;
                    color: white;
                }
                
                .alert-actions .btn-primary:hover {
                    background: #0b5ed7;
                }
                
                .alert-actions .btn-secondary {
                    background: #6c757d;
                    color: white;
                }
                
                .alert-actions .btn-secondary:hover {
                    background: #5c636a;
                }
                
                @keyframes slideInRight {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                
                @keyframes slideOutRight {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                }
                
                .breaking-news-alert.removing {
                    animation: slideOutRight 0.3s ease-out forwards;
                }
            `;
            document.head.appendChild(styles);
        }

        document.body.appendChild(alertContainer);

        // Auto-remove after 10 seconds
        setTimeout(() => {
            if (alertContainer.parentElement) {
                alertContainer.classList.add('removing');
                setTimeout(() => alertContainer.remove(), 300);
            }
        }, 10000);
    }

    playNotificationSound() {
        try {
            const audio = new Audio('/assets/sounds/breaking-news-alert.mp3');
            audio.volume = 0.5;
            audio.play().catch(e => console.log('Could not play notification sound:', e));
        } catch (error) {
            console.log('Notification sound not available:', error);
        }
    }

    updateBreakingNewsBadge() {
        // Update any UI elements that show breaking news count
        const badge = document.querySelector('.breaking-news-badge');
        if (badge) {
            const currentCount = parseInt(badge.textContent) || 0;
            badge.textContent = currentCount + 1;
            badge.style.display = 'inline-block';
        }
    }

    updateSubscriptionUI(isSubscribed) {
        const subscribeBtn = document.getElementById('subscribe-notifications');
        const unsubscribeBtn = document.getElementById('unsubscribe-notifications');
        const statusText = document.getElementById('notification-status');

        if (subscribeBtn) {
            subscribeBtn.style.display = isSubscribed ? 'none' : 'inline-block';
        }
        if (unsubscribeBtn) {
            unsubscribeBtn.style.display = isSubscribed ? 'inline-block' : 'none';
        }
        if (statusText) {
            statusText.textContent = isSubscribed ? 'Subscribed to breaking news alerts' : 'Not subscribed';
            statusText.className = isSubscribed ? 'text-success' : 'text-muted';
        }
    }

    showNotification(message, type = 'info') {
        // Create a simple notification for feedback
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 70px; right: 20px; z-index: 9998; max-width: 350px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    // Public methods
    isSubscribed() {
        return this.subscription !== null;
    }

    async checkPermission() {
        if ('Notification' in window) {
            return Notification.permission;
        }
        return 'denied';
    }
}

// Initialize the push notification manager
const pushManager = new PushNotificationManager();

// Global functions for button clicks
window.subscribeToNotifications = () => pushManager.subscribeToNotifications();
window.unsubscribeFromNotifications = () => pushManager.unsubscribeFromNotifications();

// Export for use in other scripts
window.PushNotificationManager = PushNotificationManager;
