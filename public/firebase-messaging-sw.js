// Firebase Cloud Messaging Service Worker
// Handles background push notifications for web browsers
// This file MUST be served from the root: /firebase-messaging-sw.js

importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

// Firebase config is injected dynamically via query string from the web app
// when calling: serviceWorkerRegistration.update() — handled in firebase-web-push.js
// Fallback: read config from URL search params passed at registration time

self.addEventListener('message', function(event) {
    if (event.data && event.data.type === 'FIREBASE_CONFIG') {
        const config = event.data.config;
        if (!firebase.apps.length) {
            firebase.initializeApp(config);
        }
        const messaging = firebase.messaging();

        // Background message handler
        messaging.onBackgroundMessage(function(payload) {
            const notificationTitle = payload.notification?.title || 'ApnaNest';
            const notificationOptions = {
                body:    payload.notification?.body || '',
                icon:    '/assets/images/icon-192.png',
                badge:   '/assets/images/icon-72.png',
                image:   payload.notification?.image || payload.data?.image || undefined,
                data:    payload.data || {},
                requireInteraction: false,
            };
            self.registration.showNotification(notificationTitle, notificationOptions);
        });
    }
});

// Notification click — open the link or focus existing tab
self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    const link = event.notification.data?.link || '/';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url === link && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(link);
            }
        })
    );
});
