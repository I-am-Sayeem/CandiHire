// notification_system.js - Modern notification system for CandiHire

class NotificationSystem {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Create notification container if it doesn't exist
        if (!document.getElementById('notification-container')) {
            this.createContainer();
        }
        this.container = document.getElementById('notification-container');
    }

    createContainer() {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 400px;
            pointer-events: none;
        `;
        document.body.appendChild(container);
    }

    show(message, type = 'info', duration = 5000) {
        const notification = this.createNotification(message, type);
        this.container.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        }, 10);

        // Auto remove after duration
        if (duration > 0) {
            setTimeout(() => {
                this.hide(notification);
            }, duration);
        }

        return notification;
    }

    createNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        // Get icon based on type
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };

        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };

        notification.style.cssText = `
            background: white;
            border-left: 4px solid ${colors[type]};
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin-bottom: 10px;
            padding: 16px;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            pointer-events: auto;
            max-width: 100%;
            word-wrap: break-word;
        `;

        notification.innerHTML = `
            <div style="display: flex; align-items: flex-start; gap: 12px;">
                <div style="font-size: 20px; flex-shrink: 0; margin-top: 2px;">${icons[type]}</div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px; font-size: 14px;">
                        ${this.getTitle(type)}
                    </div>
                    <div style="color: #6b7280; font-size: 14px; line-height: 1.4;">
                        ${message}
                    </div>
                </div>
                <button onclick="this.parentElement.parentElement.parentElement.remove()" 
                        style="background: none; border: none; color: #9ca3af; cursor: pointer; padding: 0; margin-left: 8px; font-size: 18px; line-height: 1;">
                    ×
                </button>
            </div>
        `;

        return notification;
    }

    getTitle(type) {
        const titles = {
            success: 'Success!',
            error: 'Error',
            warning: 'Warning',
            info: 'Information'
        };
        return titles[type] || 'Notification';
    }

    hide(notification) {
        if (notification && notification.parentNode) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    }

    success(message, duration = 5000) {
        return this.show(message, 'success', duration);
    }

    error(message, duration = 8000) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration = 6000) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration = 5000) {
        return this.show(message, 'info', duration);
    }

    // Method to show loading notification
    loading(message = 'Processing...') {
        const notification = this.createNotification(message, 'info');
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="
                    width: 20px;
                    height: 20px;
                    border: 2px solid #e5e7eb;
                    border-top: 2px solid #3b82f6;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    flex-shrink: 0;
                "></div>
                <div style="color: #6b7280; font-size: 14px;">${message}</div>
            </div>
            <style>
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        `;
        this.container.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        }, 10);

        return notification;
    }

    // Method to update loading notification
    updateLoading(loadingNotification, message, type = 'success') {
        if (loadingNotification && loadingNotification.parentNode) {
            loadingNotification.innerHTML = `
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <div style="font-size: 20px; flex-shrink: 0; margin-top: 2px;">${this.getIcon(type)}</div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px; font-size: 14px;">
                            ${this.getTitle(type)}
                        </div>
                        <div style="color: #6b7280; font-size: 14px; line-height: 1.4;">
                            ${message}
                        </div>
                    </div>
                    <button onclick="this.parentElement.parentElement.parentElement.remove()" 
                            style="background: none; border: none; color: #9ca3af; cursor: pointer; padding: 0; margin-left: 8px; font-size: 18px; line-height: 1;">
                        ×
                    </button>
                </div>
            `;
            
            // Auto remove success messages
            if (type === 'success') {
                setTimeout(() => {
                    this.hide(loadingNotification);
                }, 3000);
            }
        }
    }

    getIcon(type) {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        return icons[type] || 'ℹ️';
    }

    // Clear all notifications
    clear() {
        if (this.container) {
            this.container.innerHTML = '';
        }
    }
}

// Create global instance
window.notifications = new NotificationSystem();

// Convenience functions
window.showSuccess = (message, duration) => window.notifications.success(message, duration);
window.showError = (message, duration) => window.notifications.error(message, duration);
window.showWarning = (message, duration) => window.notifications.warning(message, duration);
window.showInfo = (message, duration) => window.notifications.info(message, duration);
window.showLoading = (message) => window.notifications.loading(message);
window.updateLoading = (notification, message, type) => window.notifications.updateLoading(notification, message, type);
