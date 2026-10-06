importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

// To receive background messages on the web, you must initialize Firebase here
// WARNING: Since this is a static file, you must HARDCODE your firebase config keys here!
// Replace the values below with your actual Firebase Project config.

const firebaseConfig = {
    apiKey: "AIzaSyC4PAuEsCFKp55h2ZDeUqnrjJz_BZ_cOoQ",
    authDomain: "feetrack-bb7af.firebaseapp.com",
    projectId: "feetrack-bb7af",
    storageBucket: "feetrack-bb7af.firebasestorage.app",
    messagingSenderId: "271741735160",
    appId: "1:271741735160:web:98c7232cf280515af62b0d",
    measurementId: "G-W0B8YKFGPL"
};

// Check if config is updated, otherwise don't initialize to prevent console errors
if (firebaseConfig.apiKey !== 'YOUR_API_KEY') {
    firebase.initializeApp(firebaseConfig);

    const messaging = firebase.messaging();

    messaging.onBackgroundMessage((payload) => {
        console.log('[firebase-messaging-sw.js] Received background message ', payload);
        const notificationTitle = payload.notification.title;
        const notificationOptions = {
            body: payload.notification.body,
            icon: '/images/logo.png', // Update with your actual logo path
            image: payload.notification.image || null,
            data: payload.data
        };

        self.registration.showNotification(notificationTitle, notificationOptions);
    });
}
