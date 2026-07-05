// Service Worker for PK Live News - Android Optimized
const CACHE_NAME = 'pk-live-news-v2';
const STATIC_CACHE = 'pk-live-static-v2';
const DYNAMIC_CACHE = 'pk-live-dynamic-v2';
const IMAGE_CACHE = 'pk-live-images-v2';

// Core app files that should always be cached
const STATIC_FILES = [
    '/',
    '/index.php',
    '/category.php',
    '/news.php',
    '/live-tv.php',
    '/manifest.json',
    '/assets/css/style.css',
    '/assets/css/bootstrap-local.css',
    '/assets/css/live-tv.css',
    '/assets/js/main.js',
    '/assets/js/config.js',
    '/assets/js/push-notifications.js',
    '/assets/js/heatmap.js',
    '/assets/js/image-lightbox.js',
    '/assets/js/video-lightbox.js',
    '/assets/images/breaking-news-icon.png',
    '/assets/images/badge.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// API endpoints that should be cached with network-first strategy
const API_ENDPOINTS = [
    '/api/breaking-news.php',
    '/api/live-status.php',
    '/api/weather.php'
];

// Install event - cache resources
self.addEventListener('install', (event) => {
    console.log('Service Worker: Installing...');
    event.waitUntil(
        Promise.all([
            // Cache static files
            caches.open(STATIC_CACHE)
                .then((cache) => {
                    console.log('Service Worker: Caching static files');
                    return cache.addAll(STATIC_FILES);
                }),
            // Cache images
            caches.open(IMAGE_CACHE)
                .then((cache) => {
                    console.log('Service Worker: Preparing image cache');
                    return cache.addAll([
                        '/assets/images/logo.png',
                        '/assets/images/breaking-news-icon.png',
                        '/assets/images/badge.png'
                    ]);
                })
        ])
            .then(() => {
                console.log('Service Worker: Installation complete');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('Service Worker: Installation failed:', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('Service Worker: Activating...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            const currentCaches = [STATIC_CACHE, DYNAMIC_CACHE, IMAGE_CACHE];
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (!currentCaches.includes(cacheName) && cacheName !== CACHE_NAME) {
                        console.log('Service Worker: Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            console.log('Service Worker: Activation complete');
            return self.clients.claim();
        })
    );
});

// Fetch event - advanced caching strategies for Android
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Strategy 1: Cache first for static files
    if (STATIC_FILES.includes(url.pathname) || 
        url.pathname.startsWith('/assets/css/') || 
        url.pathname.startsWith('/assets/js/')) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Strategy 2: Cache first for images with expiration
    if (url.pathname.startsWith('/uploads/') || 
        url.pathname.startsWith('/assets/images/') ||
        url.pathname.match(/\.(jpg|jpeg|png|gif|webp|svg)$/i)) {
        event.respondWith(cacheFirstWithExpiration(request, 7 * 24 * 60 * 60 * 1000)); // 7 days
        return;
    }

    // Strategy 3: Network only for POST requests and API endpoints that modify data
    if (request.method === 'POST' || 
        (url.pathname.startsWith('/api/') && 
         (url.pathname.includes('toggle') || url.pathname.includes('like') || 
          url.pathname.includes('comment') || url.pathname.includes('vote')))) {
        event.respondWith(networkOnly(request));
        return;
    }

    // Strategy 4: Network first for read-only API endpoints
    if (API_ENDPOINTS.some(endpoint => url.pathname.includes(endpoint)) ||
        url.pathname.startsWith('/api/')) {
        event.respondWith(networkFirst(request, 5 * 60 * 1000)); // 5 minutes cache
        return;
    }

    // Strategy 4: Stale while revalidate for HTML pages
    if (url.pathname.endsWith('.php') || url.pathname === '/') {
        event.respondWith(staleWhileRevalidate(request));
        return;
    }

    // Strategy 5: Network only for external resources
    if (url.origin !== self.location.origin) {
        event.respondWith(networkOnly(request));
        return;
    }

    // Default: Network first
    event.respondWith(networkFirst(request, 60 * 60 * 1000)); // 1 hour cache
});

// Cache First Strategy
function cacheFirst(request) {
    return caches.match(request)
        .then(response => {
            if (response) {
                return response;
            }
            return fetch(request).then(response => {
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }
                const responseToCache = response.clone();
                caches.open(DYNAMIC_CACHE).then(cache => {
                    cache.put(request, responseToCache);
                });
                return response;
            });
        });
}

// Cache First with Expiration
function cacheFirstWithExpiration(request, maxAge) {
    return caches.match(request)
        .then(response => {
            if (response) {
                const dateHeader = response.headers.get('date');
                if (dateHeader) {
                    const responseDate = new Date(dateHeader).getTime();
                    const now = Date.now();
                    if (now - responseDate < maxAge) {
                        return response;
                    }
                    // Cache expired, delete it
                    caches.delete(request);
                }
            }
            return fetch(request).then(response => {
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }
                const responseToCache = response.clone();
                caches.open(IMAGE_CACHE).then(cache => {
                    cache.put(request, responseToCache);
                });
                return response;
            });
        });
}

// Network First Strategy
function networkFirst(request, maxAge = 60 * 60 * 1000) {
    return fetch(request)
        .then(response => {
            // Check if response is valid
            if (!response || response.status === 0 || response.type === 'opaque') {
                throw new Error('Invalid response');
            }
            // Only cache GET requests, not POST requests
            if (request.method === 'GET') {
                const responseToCache = response.clone();
                caches.open(DYNAMIC_CACHE).then(cache => {
                    cache.put(request, responseToCache);
                });
            }
            return response;
        })
        .catch(error => {
            console.log('Network failed, trying cache:', error);
            return caches.match(request).then(cachedResponse => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                // Return a proper error response instead of undefined
                return new Response(JSON.stringify({error: 'Network failed and no cache available'}), {
                    status: 503,
                    statusText: 'Service Unavailable',
                    headers: {'Content-Type': 'application/json'}
                });
            });
        })
        .catch(error => {
            // Final error handler to prevent uncaught promises
            console.error('Service Worker: Critical error in networkFirst:', error);
            return new Response(JSON.stringify({error: 'Service worker error'}), {
                status: 500,
                statusText: 'Internal Server Error',
                headers: {'Content-Type': 'application/json'}
            });
        });
}

// Stale While Revalidate Strategy
function staleWhileRevalidate(request) {
    return caches.match(request)
        .then(response => {
            const fetchPromise = fetch(request)
                .then(networkResponse => {
                    if (networkResponse && networkResponse.status === 200 && request.method !== 'POST') {
                        const networkResponseToCache = networkResponse.clone();
                        caches.open(DYNAMIC_CACHE).then(cache => {
                            cache.put(request, networkResponseToCache);
                        });
                    }
                    return networkResponse;
                })
                .catch(error => {
                    console.log('Network failed in staleWhileRevalidate:', error);
                    return response; // Return cached response if network fails
                });
            return response || fetchPromise;
        })
        .catch(error => {
            console.error('Service Worker: Critical error in staleWhileRevalidate:', error);
            return new Response(JSON.stringify({error: 'Cache error'}), {
                status: 500,
                statusText: 'Internal Server Error',
                headers: {'Content-Type': 'application/json'}
            });
        });
}

// Network Only Strategy
function networkOnly(request) {
    return fetch(request);
}

// Push event - handle incoming push notifications
self.addEventListener('push', (event) => {
    console.log('Service Worker: Push received');
    
    let notificationData = {
        title: '🚨 Breaking News',
        body: 'A new breaking news story has been published',
        icon: '/assets/images/breaking-news-icon.png',
        badge: '/assets/images/badge.png',
        tag: 'breaking-news',
        data: {
            url: '/',
            timestamp: Date.now()
        },
        actions: [
            {
                action: 'view',
                title: 'View News'
            },
            {
                action: 'dismiss',
                title: 'Dismiss'
            }
        ],
        requireInteraction: true,
        silent: false
    };

    if (event.data) {
        try {
            const payload = event.data.json();
            notificationData = {
                ...notificationData,
                ...payload
            };
        } catch (error) {
            console.error('Error parsing push data:', error);
        }
    }

    const options = {
        body: notificationData.body,
        icon: notificationData.icon,
        badge: notificationData.badge,
        tag: notificationData.tag,
        data: notificationData.data,
        actions: notificationData.actions,
        requireInteraction: notificationData.requireInteraction,
        silent: notificationData.silent,
        vibrate: [200, 100, 200],
        sound: '/assets/sounds/breaking-news-alert.mp3'
    };

    event.waitUntil(
        self.registration.showNotification(notificationData.title, options)
            .then(() => {
                console.log('Service Worker: Notification displayed');
                
                // Track notification delivery
                trackNotificationDelivery(notificationData);
            })
            .catch((error) => {
                console.error('Service Worker: Error showing notification:', error);
            })
    );
});

// Notification click event
self.addEventListener('notificationclick', (event) => {
    console.log('Service Worker: Notification clicked');
    
    const notification = event.notification;
    const action = event.action;
    const notificationData = notification.data;

    // Close the notification
    notification.close();

    // Track notification click
    trackNotificationClick(notificationData);

    if (action === 'dismiss') {
        // Just close the notification
        return;
    }

    // Open the news article
    const urlToOpen = notificationData.url || '/';
    
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then((clientList) => {
            // Check if a matching client is already open
            for (const client of clientList) {
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }
            
            // Open new window
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});

// Notification close event (when user manually closes)
self.addEventListener('notificationclose', (event) => {
    console.log('Service Worker: Notification closed');
    const notificationData = event.notification.data;
    trackNotificationDismiss(notificationData);
});

// Background sync for offline notification tracking
self.addEventListener('sync', (event) => {
    console.log('Service Worker: Background sync triggered');
    
    if (event.tag === 'notification-tracking') {
        event.waitUntil(trackPendingNotifications());
    }
});

// Track notification delivery
async function trackNotificationDelivery(notificationData) {
    try {
        const trackingData = {
            type: 'delivery',
            notification_id: notificationData.notification_id,
            timestamp: Date.now(),
            user_agent: navigator.userAgent || 'Service Worker'
        };

        // Store in IndexedDB for offline tracking
        await storeTrackingData(trackingData);
        
        // Try to send immediately
        await sendTrackingData(trackingData);
    } catch (error) {
        console.error('Error tracking notification delivery:', error);
    }
}

// Track notification click
async function trackNotificationClick(notificationData) {
    try {
        const trackingData = {
            type: 'click',
            notification_id: notificationData.notification_id,
            timestamp: Date.now(),
            user_agent: navigator.userAgent || 'Service Worker'
        };

        await storeTrackingData(trackingData);
        await sendTrackingData(trackingData);
    } catch (error) {
        console.error('Error tracking notification click:', error);
    }
}

// Track notification dismiss
async function trackNotificationDismiss(notificationData) {
    try {
        const trackingData = {
            type: 'dismiss',
            notification_id: notificationData.notification_id,
            timestamp: Date.now(),
            user_agent: navigator.userAgent || 'Service Worker'
        };

        await storeTrackingData(trackingData);
        await sendTrackingData(trackingData);
    } catch (error) {
        console.error('Error tracking notification dismiss:', error);
    }
}

// Store tracking data in IndexedDB for offline support
async function storeTrackingData(data) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('NotificationTrackingDB', 1);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => {
            const db = request.result;
            const transaction = db.transaction(['tracking'], 'readwrite');
            const store = transaction.objectStore('tracking');
            
            const addRequest = store.add(data);
            addRequest.onsuccess = () => resolve();
            addRequest.onerror = () => reject(addRequest.error);
        };
        
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('tracking')) {
                const store = db.createObjectStore('tracking', { keyPath: 'timestamp', autoIncrement: true });
                store.createIndex('type', 'type', { unique: false });
                store.createIndex('notification_id', 'notification_id', { unique: false });
            }
        };
    });
}

// Send tracking data to server
async function sendTrackingData(data) {
    try {
        const response = await fetch('/api/notification-tracking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        if (response.ok) {
            // Remove from IndexedDB if successfully sent
            await removeTrackingData(data.timestamp);
        } else {
            // Register for background sync to retry later
            await registerBackgroundSync();
        }
    } catch (error) {
        console.error('Error sending tracking data:', error);
        await registerBackgroundSync();
    }
}

// Remove tracking data from IndexedDB
async function removeTrackingData(timestamp) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('NotificationTrackingDB', 1);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => {
            const db = request.result;
            const transaction = db.transaction(['tracking'], 'readwrite');
            const store = transaction.objectStore('tracking');
            
            const deleteRequest = store.delete(timestamp);
            deleteRequest.onsuccess = () => resolve();
            deleteRequest.onerror = () => reject(deleteRequest.error);
        };
    });
}

// Send all pending tracking data
async function trackPendingNotifications() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('NotificationTrackingDB', 1);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => {
            const db = request.result;
            const transaction = db.transaction(['tracking'], 'readonly');
            const store = transaction.objectStore('tracking');
            
            const getAllRequest = store.getAll();
            getAllRequest.onsuccess = async () => {
                const trackingData = getAllRequest.result;
                
                for (const data of trackingData) {
                    try {
                        await sendTrackingData(data);
                    } catch (error) {
                        console.error('Error sending pending tracking data:', error);
                    }
                }
                
                resolve();
            };
            getAllRequest.onerror = () => reject(getAllRequest.error);
        };
    });
}

// Register background sync
async function registerBackgroundSync() {
    if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
        try {
            await self.registration.sync.register('notification-tracking');
            console.log('Background sync registered');
        } catch (error) {
            console.error('Error registering background sync:', error);
        }
    }
}

// Handle message events from main thread
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

console.log('Service Worker: Loaded');
