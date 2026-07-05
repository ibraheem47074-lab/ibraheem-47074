/**
 * Website Performance Tracking Script
 * This script tracks user activity for performance monitoring
 */

class PerformanceTracker {
    constructor() {
        this.sessionId = this.getSessionId();
        this.startTime = Date.now();
        this.currentPage = window.location.pathname;
        this.trackingData = {
            sessionId: this.sessionId,
            pageViews: [],
            events: [],
            timeOnPage: 0
        };
        
        this.init();
    }

    init() {
        // Track page view
        this.trackPageView();
        
        // Track user interactions
        this.trackInteractions();
        
        // Track time on page
        this.trackTimeOnPage();
        
        // Track page unload
        this.trackPageUnload();
        
        // Track scroll depth
        this.trackScrollDepth();
    }

    getSessionId() {
        let sessionId = sessionStorage.getItem('pk_session_id');
        if (!sessionId) {
            sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem('pk_session_id', sessionId);
        }
        return sessionId;
    }

    async trackPageView() {
        const data = {
            action: 'view',
            session_id: this.sessionId,
            page_url: window.location.href,
            page_title: document.title,
            referrer: document.referrer,
            user_agent: navigator.userAgent,
            screen_resolution: screen.width + 'x' + screen.height,
            timestamp: new Date().toISOString()
        };

        // Get user ID if logged in
        const userId = this.getUserId();
        if (userId) {
            data.user_id = userId;
        }

        // Get news ID if on news page
        const newsId = this.getNewsId();
        if (newsId) {
            data.news_id = newsId;
        }

        this.trackingData.pageViews.push(data);
        await this.sendData(data);
    }

    trackInteractions() {
        // Track clicks on news articles
        document.addEventListener('click', (e) => {
            const target = e.target.closest('a[href*="news.php"]');
            if (target) {
                this.trackEvent('article_click', {
                    article_url: target.href,
                    article_title: target.textContent.trim()
                });
            }
        });

        // Track social shares
        document.addEventListener('click', (e) => {
            const target = e.target.closest('[data-social-share]');
            if (target) {
                this.trackEvent('social_share', {
                    platform: target.dataset.socialShare,
                    url: window.location.href
                });
            }
        });

        // Track search queries
        const searchForms = document.querySelectorAll('form[action*="search"], input[name="search"], input[name="query"]');
        searchForms.forEach(form => {
            form.addEventListener('submit', (e) => {
                const query = form.querySelector('input[name="search"], input[name="query"]')?.value;
                if (query) {
                    this.trackEvent('search', {
                        query: query
                    });
                }
            });
        });

        // Track category clicks
        document.addEventListener('click', (e) => {
            const target = e.target.closest('a[href*="category"]');
            if (target) {
                this.trackEvent('category_click', {
                    category_url: target.href,
                    category_name: target.textContent.trim()
                });
            }
        });
    }

    trackTimeOnPage() {
        setInterval(() => {
            this.trackingData.timeOnPage = Math.floor((Date.now() - this.startTime) / 1000);
        }, 5000);
    }

    trackPageUnload() {
        window.addEventListener('beforeunload', () => {
            const data = {
                action: 'page_leave',
                session_id: this.sessionId,
                time_on_page: this.trackingData.timeOnPage,
                timestamp: new Date().toISOString()
            };

            // Use sendBeacon for reliable delivery during page unload
            navigator.sendBeacon('/admin/api/performance-tracker.php', JSON.stringify(data));
        });
    }

    trackScrollDepth() {
        let maxScroll = 0;
        const scrollThresholds = [25, 50, 75, 90];

        window.addEventListener('scroll', () => {
            const scrollPercent = Math.round(
                (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100
            );
            
            if (scrollPercent > maxScroll) {
                maxScroll = scrollPercent;
                
                // Track when user reaches certain scroll depths
                scrollThresholds.forEach(threshold => {
                    if (scrollPercent >= threshold && !this[`tracked_${threshold}`]) {
                        this[`tracked_${threshold}`] = true;
                        this.trackEvent('scroll_depth', {
                            depth: threshold
                        });
                    }
                });
            }
        });
    }

    trackEvent(action, data = {}) {
        const eventData = {
            action: action,
            session_id: this.sessionId,
            page_url: window.location.href,
            timestamp: new Date().toISOString(),
            ...data
        };

        const userId = this.getUserId();
        if (userId) {
            eventData.user_id = userId;
        }

        const newsId = this.getNewsId();
        if (newsId) {
            eventData.news_id = newsId;
        }

        this.trackingData.events.push(eventData);
        this.sendData(eventData);
    }

    getUserId() {
        // Try to get user ID from various sources
        const metaTag = document.querySelector('meta[name="user-id"]');
        if (metaTag) return metaTag.content;
        
        const globalVar = window.pkUserId || window.userId;
        if (globalVar) return globalVar;
        
        // Check for cookie
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === 'pk_user_id') return value;
        }
        
        return null;
    }

    getNewsId() {
        // Try to get news ID from various sources
        const metaTag = document.querySelector('meta[name="news-id"]');
        if (metaTag) return metaTag.content;
        
        const globalVar = window.pkNewsId || window.newsId;
        if (globalVar) return globalVar;
        
        // Extract from URL
        const urlMatch = window.location.pathname.match(/news\.php\?id=(\d+)/);
        if (urlMatch) return urlMatch[1];
        
        // Extract from slug
        const slugMatch = window.location.pathname.match(/\/([^\/]+)\/?$/);
        if (slugMatch) {
            // This would need backend lookup to get ID from slug
            return null;
        }
        
        return null;
    }

    async sendData(data) {
        try {
            const response = await fetch('/admin/api/performance-tracker.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // Optional: Check response for success confirmation
            const result = await response.json();
            if (!result || !result.success) {
                console.warn('Tracking data not saved successfully:', result?.message);
            }
        } catch (error) {
            console.error('Tracking error:', error);
            // Silently fail for tracking to not disrupt user experience
        }
    }
}

// Initialize tracker when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.pkTracker = new PerformanceTracker();
    });
} else {
    window.pkTracker = new PerformanceTracker();
}

// Export for manual tracking if needed
window.trackEvent = function(action, data) {
    if (window.pkTracker) {
        window.pkTracker.trackEvent(action, data);
    }
};
