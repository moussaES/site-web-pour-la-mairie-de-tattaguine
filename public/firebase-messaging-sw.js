// ====================================================================
// FIREBASE CLOUD MESSAGING (FCM) SERVICE WORKER - SUNU TATTAGUINE
// ====================================================================

importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js');

// Configuration Firebase
firebase.initializeApp({
    apiKey: "AIzaSyA1LDS0_Be7TARajRi4gHMWsZETDaaKf3A",
    projectId: "site-web-mairie-506810",
    messagingSenderId: "254091229105",
    appId: "1:254091229105:web:1b7679669e5636cacc6f90"
});

const messaging = firebase.messaging();

// Gestion de la réception de notifications en arrière-plan
messaging.onBackgroundMessage((payload) => {
    console.log('[FCM Service Worker] Message reçu en arrière-plan:', payload);

    const notificationTitle = payload.notification?.title || payload.data?.title || 'Sunu Tattaguine — Mairie';
    const notificationOptions = {
        body: payload.notification?.body || payload.data?.body || 'Nouvelle notification municipale.',
        icon: payload.notification?.icon || '/site%20web%20mairie/public/assets/img/icon-192.png',
        badge: '/site%20web%20mairie/public/assets/img/icon-192.png',
        data: {
            url: payload.data?.url || '/site%20web%20mairie/public/mon-espace'
        }
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Clic sur la notification
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = event.notification.data?.url || '/site%20web%20mairie/public/mon-espace';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (let client of windowClients) {
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
