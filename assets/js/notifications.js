// Simple Notification System for PK Live News
class NotificationManager {
    constructor() {
        this.notifications = [];
        this.unreadCount = 0;
        this.apiEndpoint = 'api/notifications.php';
        this.init();
    }

    init() {
        // Load notifications on page load
        this.loadNotifications();
        
        // Set up periodic refresh
        setInterval(() => this.loadNotifications(), 30000); // Refresh every 30 seconds
        
        // Only add notification bell if not already present in header
        if (!document.getElementById('notificationDropdownBtn')) {
            this.addNotificationBell();
        }
    }

    async loadNotifications() {
        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            if (data && typeof data === 'object') {
                this.notifications = data.notifications || [];
                this.unreadCount = data.unread_count || 0;
                this.updateNotificationBell();
                this.showNotifications();
            } else {
                console.warn('Invalid notifications data format:', data);
                this.notifications = [];
                this.unreadCount = 0;
                this.updateNotificationBell();
                this.showNotifications();
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            // Set default values on error
            this.notifications = [];
            this.unreadCount = 0;
            this.updateNotificationBell();
            const listContainer = document.getElementById('notification-list');
            if (listContainer) {
                listContainer.innerHTML = '<div class="no-notifications">Unable to load notifications</div>';
            }
        }
    }

    addNotificationBell() {
        // Check if notification bell already exists
        if (document.getElementById('notification-bell')) {
            return;
        }

        // Find a suitable place to add the bell (header navigation)
        const header = document.querySelector('header') || document.querySelector('.navbar') || document.querySelector('nav');
        if (!header) {
            console.warn('Could not find header element for notification bell');
            return;
        }

        // Create notification bell container
        const bellContainer = document.createElement('div');
        bellContainer.className = 'notification-bell-container';
        bellContainer.innerHTML = `
            <div class="notification-bell" id="notification-bell">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notification-badge" style="display: none;">0</span>
            </div>
            <div class="notification-dropdown" id="notification-dropdown" style="display: none;">
                <div class="notification-header">
                    <h6>Notifications</h6>
                    <button class="mark-all-read-btn" onclick="notificationManager.markAllAsRead()">Mark all as read</button>
                </div>
                <div class="notification-list" id="notification-list">
                    <div class="no-notifications">No notifications</div>
                </div>
                <div class="notification-footer">
                    <a href="admin/manage-notifications.php">Manage Notifications</a>
                </div>
            </div>
        `;

        // Add styles
        const styles = document.createElement('style');
        styles.textContent = `
            .notification-bell-container {
                position: relative;
                display: inline-block;
                margin-left: 15px;
            }

            .notification-bell {
                position: relative;
                cursor: pointer;
                color: #fff;
                font-size: 18px;
                padding: 8px;
                border-radius: 50%;
                transition: all 0.3s ease;
            }

            .notification-bell:hover {
                background-color: rgba(255, 255, 255, 0.1);
            }

            .notification-badge {
                position: absolute;
                top: 0;
                right: 0;
                background-color: #dc3545;
                color: white;
                border-radius: 50%;
                width: 18px;
                height: 18px;
                font-size: 11px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                border: 2px solid #fff;
            }

            .notification-dropdown {
                position: absolute;
                top: 100%;
                right: 0;
                background: white;
                border: 1px solid #ddd;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                min-width: 350px;
                max-width: 400px;
                max-height: 400px;
                z-index: 1000;
                margin-top: 5px;
            }

            .notification-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px 16px;
                border-bottom: 1px solid #eee;
                background: #f8f9fa;
                border-radius: 8px 8px 0 0;
            }

            .notification-header h6 {
                margin: 0;
                font-weight: 600;
                color: #333;
            }

            .mark-all-read-btn {
                background: none;
                border: none;
                color: #007bff;
                cursor: pointer;
                font-size: 12px;
                padding: 4px 8px;
                border-radius: 4px;
                transition: background 0.2s;
            }

            .mark-all-read-btn:hover {
                background: #e9ecef;
            }

            .notification-list {
                max-height: 300px;
                overflow-y: auto;
            }

            .notification-item {
                padding: 12px 16px;
                border-bottom: 1px solid #f0f0f0;
                cursor: pointer;
                transition: background 0.2s;
                position: relative;
            }

            .notification-item:hover {
                background: #f8f9fa;
            }

            .notification-item.unread {
                background: #f0f8ff;
                border-left: 3px solid #007bff;
            }

            .notification-item:last-child {
                border-bottom: none;
            }

            .notification-type {
                display: inline-block;
                width: 8px;
                height: 8px;
                border-radius: 50%;
                margin-right: 8px;
            }

            .notification-type.info { background: #17a2b8; }
            .notification-type.success { background: #28a745; }
            .notification-type.warning { background: #ffc107; }
            .notification-type.error { background: #dc3545; }
            .notification-type.news { background: #007bff; }
            .notification-type.event { background: #6f42c1; }
            .notification-type.system { background: #6c757d; }

            .notification-content {
                flex: 1;
            }

            .notification-title {
                font-weight: 600;
                margin-bottom: 4px;
                color: #333;
                font-size: 14px;
            }

            .notification-message {
                color: #666;
                font-size: 13px;
                line-height: 1.4;
                margin-bottom: 4px;
            }

            .notification-time {
                color: #999;
                font-size: 11px;
            }

            .notification-footer {
                padding: 8px 16px;
                border-top: 1px solid #eee;
                background: #f8f9fa;
                border-radius: 0 0 8px 8px;
                text-align: center;
            }

            .notification-footer a {
                color: #007bff;
                text-decoration: none;
                font-size: 13px;
            }

            .notification-footer a:hover {
                text-decoration: underline;
            }

            .no-notifications {
                padding: 20px;
                text-align: center;
                color: #999;
                font-size: 14px;
            }
        `;
        document.head.appendChild(styles);

        // Add to header
        header.appendChild(bellContainer);

        // Set up click handlers
        const bell = document.getElementById('notification-bell');
        const dropdown = document.getElementById('notification-dropdown');

        bell.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', () => {
            dropdown.style.display = 'none';
        });

        dropdown.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }

    updateNotificationBell() {
        const badge = document.getElementById('notification-badge');
        if (badge) {
            if (this.unreadCount > 0) {
                badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    showNotifications() {
        const listContainer = document.getElementById('notification-list');
        if (!listContainer) return;

        if (this.notifications.length === 0) {
            listContainer.innerHTML = '<div class="no-notifications">No notifications</div>';
            return;
        }

        let html = '';
        this.notifications.forEach(notification => {
            const typeClass = notification.type || 'info';
            const unreadClass = !notification.is_read ? 'unread' : '';
            
            html += `
                <div class="notification-item ${unreadClass}" onclick="notificationManager.handleNotificationClick(${notification.id}, '${notification.url || ''}')">
                    <div class="notification-type ${typeClass}"></div>
                    <div class="notification-content">
                        <div class="notification-title">${this.escapeHtml(notification.title)}</div>
                        <div class="notification-message">${this.escapeHtml(notification.message)}</div>
                        <div class="notification-time">${notification.time_ago || 'Just now'}</div>
                    </div>
                </div>
            `;
        });

        listContainer.innerHTML = html;
    }

    async handleNotificationClick(notificationId, url) {
        // Mark as read
        await this.markAsRead(notificationId);
        
        // Navigate to URL if provided
        if (url) {
            window.location.href = url;
        }
        
        // Close dropdown
        const dropdown = document.getElementById('notification-dropdown');
        if (dropdown) {
            dropdown.style.display = 'none';
        }
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=mark_read&notification_id=${notificationId}`
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            if (data && data.success) {
                // Reload notifications to update the UI
                this.loadNotifications();
            } else {
                console.warn('Failed to mark notification as read:', data?.message);
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'action=mark_all_read'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            if (data && data.success) {
                // Reload notifications to update the UI
                this.loadNotifications();
            } else {
                console.warn('Failed to mark all notifications as read:', data?.message);
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize notification manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.notificationManager = new NotificationManager();
});
