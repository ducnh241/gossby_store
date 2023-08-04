(function ($) {
    'use strict';

    if (typeof FIREBASE_NOTIF_TAG_NAME === 'undefined') {
        var FIREBASE_NOTIF_TAG_NAME = null;
    }

    const FIREBASE_NOTIF_DEFAULT_ICON = $.base_url + '/logo.png';

    var config = {
        apiKey: "AIzaSyBz5d6I5pjoUkKYo46_KMJp8rR1MZlyuCk",
        projectId: "tasklist-9f7c5",
        messagingSenderId: "1005454267956"
    };

    firebase.initializeApp(config);

    const messaging = firebase.messaging();

    messaging.usePublicVapidKey("BChhWdpW5Jkn-CJ_FO8f12Ukqa1mzGitaeTh0lBuoGVTV4TxjIsZ-f2DZS-0WrwfWxbNduDikE5J7hUaVEGedqo");

    function _notificationShow(payload) {        
        var options = {
            body: payload.data.body,
            data: payload.data,
            icon: payload.data.icon ? payload.data.icon : FIREBASE_NOTIF_DEFAULT_ICON
        };

        if (typeof FIREBASE_NOTIF_TAG_NAME !== 'undefined' && FIREBASE_NOTIF_TAG_NAME) {
            options.tag = FIREBASE_NOTIF_TAG_NAME;
        }
        
        if (!('Notification' in window)) {
            console.error('This browser does not support system notifications');
        } else if (Notification.permission === 'granted') {
            var notification = new Notification(payload.data.title, options);

            notification.onclick = function (e) {
                e.preventDefault();

                window.open(payload.data.url, '_blank');

                notification.close();
            }
        }
    }

    function _notificationProcess(payload) {
        try {
            navigator.serviceWorker.controller.postMessage('NOTIF:' + JSON.stringify(payload));       
        } catch (e) {
            _notificationShow(payload);
        }
    }

    function firebaseNotificationSubscribe() {
        messaging.requestPermission().then(function () {
            navigator.serviceWorker.register(OSC_TPL_JS_BASE_URL + '/firebase/service-worker.js', {scope: '/'}).then((registration) => {
                messaging.useServiceWorker(registration);

                messaging.getToken().then(function (token) {
                    if (token) {
                        fetch($.base_url + '/firebase/common/register', {
                            method: 'post',
                            credentials: 'include',
                            headers: {
                                'Content-type': 'application/json',
                                'X-OSC-Cross-Request': 'OK'
                            },
                            body: JSON.stringify({
                                token: token
                            })
                        }).then(function (response) {
                            if (response.status !== 200) {
                                throw new Error("Invalid status code from notification API: " + response.status);
                            }

                            return response.json();
                        }).then(function (response) {

                        })['catch'](function (e) {
                            console.error('An error occured', e);
                        });
                    } else {
                        console.error('No Instance ID token available. Request permission to generate one.');
                    }
                }).catch(function (err) {
                    console.error('An error occurred while retrieving token.', err);
                });

                messaging.onTokenRefresh(function () {
                    messaging.getToken().then(function (token) {
                        fetch($.base_url + '/firebase/common/register', {
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
                    }).catch(function (err) {
                        console.error('Unable to retrieve refreshed token ', err);
                    });
                });

                messaging.onMessage(function (payload) {
//                    console.log('Message received. ', payload);

                    if (typeof payload.data.verify_code !== 'undefined') {
                        fetch($.base_url + '/firebase/common/verify', {
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
                                    fetch($.base_url + '/firebase/common/unregister', {
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

                                    console.error('An error occured. You dont have permission to use the notification token');
                                });
                            } else {
                                _notificationProcess(payload);
                            }
                        })['catch'](function (e) {
                            console.error('An error occured. We were unable to verify read permission for the message', e);
                        });
                    } else {
                        _notificationProcess(payload);
                    }
                });
            });
        }).catch(function (err) {
            console.log('Unable to get permission to notify.', err);
        });
    }

    function firebaseNotificationUnsubscribe() {
        messaging.getToken().then(function (token) {
            messaging.deleteToken(token).then(function () {
                fetch($.base_url + '/firebase/common/unregister', {
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
            }).catch(function (err) {
                console.error('Unable to delete token. ', err);
            });
        }).catch(function (err) {
            console.error('Error retrieving Instance ID token. ', err);
        });

    }

    firebaseNotificationSubscribe();
})(jQuery);