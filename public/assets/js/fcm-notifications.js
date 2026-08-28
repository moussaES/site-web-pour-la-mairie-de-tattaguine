// ====================================================================
// SCRIPT DE GESTION DES NOTIFICATIONS PUSH WEB FIREBASE (FCM)
// ====================================================================

(function() {
    // Vérifier le support des Service Workers et Notifications
    if (!('serviceWorker' in navigator) || !('Notification' in window)) {
        console.log('[FCM] Les notifications Web Push ne sont pas supportées par ce navigateur.');
        return;
    }

    // Charger dynamiquement le SDK Firebase Compat si nécessaire
    function loadScript(src) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) {
                resolve();
                return;
            }
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    Promise.all([
        loadScript('https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js'),
        loadScript('https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js')
    ]).then(() => {
        initFirebaseMessaging();
    }).catch(err => {
        console.error('[FCM] Erreur de chargement des SDK Firebase:', err);
    });

    function initFirebaseMessaging() {
        if (typeof firebase === 'undefined') return;

        // Initialisation de l'application Firebase
        if (!firebase.apps.length) {
            firebase.initializeApp({
                apiKey: window.FCM_CONFIG?.apiKey || "AIzaSyA1LDS0_Be7TARajRi4gHMWsZETDaaKf3A",
                projectId: window.FCM_CONFIG?.projectId || "site-web-mairie-506810",
                messagingSenderId: window.FCM_CONFIG?.senderId || "254091229105",
                appId: window.FCM_CONFIG?.appId || "1:254091229105:web:1b7679669e5636cacc6f90"
            });
        }

        const messaging = firebase.messaging();

        // Demander la permission et enregistrer le jeton FCM
        Notification.requestPermission().then((permission) => {
            if (permission === 'granted') {
                console.log('[FCM] Permission accordée pour les notifications.');

                // Enregistrer le Service Worker
                navigator.serviceWorker.register(window.FCM_CONFIG?.swUrl || '/site%20web%20mairie/public/firebase-messaging-sw.js')
                    .then((registration) => {
                        return messaging.getToken({
                            vapidKey: window.FCM_CONFIG?.vapidKey || 'VOTRE_VAPID_KEY_FIREBASE',
                            serviceWorkerRegistration: registration
                        });
                    })
                    .then((currentToken) => {
                        if (currentToken) {
                            console.log('[FCM Jeton Obtenu]:', currentToken);
                            sendTokenToServer(currentToken);
                        } else {
                            console.log('[FCM] Aucun jeton disponible. Demander l\'autorisation à l\'utilisateur.');
                        }
                    })
                    .catch((err) => {
                        console.error('[FCM] Erreur d\'obtention du jeton FCM:', err);
                    });
            } else {
                console.log('[FCM] Permission de notification refusée par l\'utilisateur.');
            }
        });

        // Écouter les messages reçus au premier plan (Foreground)
        messaging.onMessage((payload) => {
            console.log('[FCM Message Premier Plan]:', payload);
            triggerNativeOSNotification(
                payload.notification?.title || payload.data?.title || '🏛️ Mairie de Tattaguine',
                payload.notification?.body || payload.data?.body || 'Vous avez reçu une nouvelle réponse officielle !',
                payload.data?.url || ((window.FCM_CONFIG?.baseUrl || '') + '/mon-espace')
            );
        });
    }

    // Transmettre le jeton au serveur PHP
    function sendTokenToServer(token) {
        fetch((window.FCM_CONFIG?.baseUrl || '') + '/auth/save-fcm-token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ token: token })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('[FCM] Jeton d\'appareil enregistré avec succès sur le serveur.');
            }
        })
        .catch(err => console.error('[FCM] Erreur lors de l\'envoi du jeton au serveur:', err));
    }

    // Déclencher une vraie notification système native OS (Windows / Mac)
    window.triggerNativeOSNotification = function(title, body, url) {
        if (!('Notification' in window)) return;

        if (Notification.permission === 'granted') {
            const options = {
                body: body,
                icon: (window.FCM_CONFIG?.baseUrl || '') + '/assets/img/icon-192.png',
                badge: (window.FCM_CONFIG?.baseUrl || '') + '/assets/img/icon-192.png',
                tag: 'sunu-tattaguine-notif-' + Date.now(),
                requireInteraction: true, // Conserve la notification sur l'écran Windows jusqu'à interaction
                data: {
                    url: url || ((window.FCM_CONFIG?.baseUrl || '') + '/mon-espace')
                }
            };

            // Essayer d'abord via le Service Worker pour une notification système Windows native
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.ready.then(function(registration) {
                    registration.showNotification(title, options);
                }).catch(function() {
                    const n = new Notification(title, options);
                    n.onclick = function() {
                        window.focus();
                        window.location.href = options.data.url;
                    };
                });
            } else {
                const n = new Notification(title, options);
                n.onclick = function() {
                    window.focus();
                    window.location.href = options.data.url;
                };
            }
        }
    };
})();
