'use strict';

self.addEventListener('install', function(event) {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', function(event) {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('message', function (e) {
    var matched = e.data.match(/^NOTIF\:(.+)$/);

    if (matched) {
        notificationProcess(JSON.parse(matched[1]));
    }
});

self.addEventListener('notificationclick', function (e) {
    e.stopImmediatePropagation();

    e.notification.close();

    if (e.notification.data && e.notification.data.url) {
        e.waitUntil(clients.openWindow(e.notification.data.url));
    }
});

importScripts('https://www.gstatic.com/firebasejs/5.7.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/5.7.1/firebase-messaging.js');

firebase.initializeApp({
    messagingSenderId: '1005454267956'
});

const SITE_NAME = 'OSECORE';
const SITE_BASE_URL = 'https://osecore.net';
const NOTIF_TAG_NAME = null;
const NOTIF_DEFAULT_ICON = SITE_BASE_URL + '/logo.png';

const messaging = firebase.messaging();

function notificationShow(payload) {
    var options = {
        body: payload.data.body,
        data: payload.data,
        icon: payload.data.icon ? payload.data.icon : NOTIF_DEFAULT_ICON
    };

    if (NOTIF_TAG_NAME) {
        options.tag = NOTIF_TAG_NAME;
    }

    return self.registration.showNotification(payload.data.title, options);
}

function notificationProcess(payload) {
    if (!NOTIF_TAG_NAME) {
        return notificationShow(payload);
    }

    if (self.registration.getNotifications) {
        return self.registration.getNotifications({tag: NOTIF_TAG_NAME}).then(function (notifs) {
            if (notifs && notifs.length > 0) {
                var counter = 1;

                for (var i = 0; i < notifs.length; i++) {
                    var notif = notifs[i];

                    if (notif.data && notif.data.notificationCount) {
                        counter += notif.data.notificationCount;
                    } else {
                        counter++;
                    }

                    notif.close();
                }

                payload.data.title = SITE_NAME + ' Notification';

                payload.data.body = "You have " + counter + " new notifications";

                payload.data.notificationCount = counter;
            }

            return notificationShow(payload);
        });
    }

    return notificationShow(payload);
}

messaging.setBackgroundMessageHandler(function (payload) {
//    console.log('Received background message ', payload);

    if (typeof payload.data.verify_code !== 'undefined') {
        return fetch(SITE_BASE_URL + '/firebase/common/verify', {
            method: 'post',
            credentials: 'include',
            headers: {
                'Content-type': 'application/json',
                'X-OSC-Cross-Request': 'OK'
            },
            body: JSON.stringify({
                verify_code: payload.data.verify_code
            })
        }).then(function (response) {
            if (response.status !== 200) {
                throw new Error("Invalid status code from notification API: " + response.status);
            }

            return response.json();
        }).then(function (response) {
            if (response.result !== 'OK') {
                throw new Error(response.message);
            }

            if (!response.data) {
                return messaging.getToken().then(function (token) {
                    fetch(SITE_BASE_URL + '/firebase/common/unregister', {
                        method: 'post',
                        credentials: 'include',
                        headers: {
                            'Content-type': 'application/json',
                            'X-OSC-Cross-Request': 'OK'
                        },
                        body: JSON.stringify({
                            token: token
                        })
                    });

                    return notificationProcess({data: {title: 'An error occured', body: 'You dont have permission to use the notification token'}});
                });
            }

            return notificationProcess(payload);
        })['catch'](function (e) {
            return notificationProcess({data: {title: 'An error occured', body: 'We were unable to verify read permission for the message'}});
        });
    }

    return notificationProcess(payload);
});