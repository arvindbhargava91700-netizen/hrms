# Firebase Push Notifications Setup Guide

This document explains how to configure Firebase Cloud Messaging (FCM) for the backend to enable push notifications for both the Mobile App and the Web platform.

## 1. Create a Firebase Project

1. Go to the [Firebase Console](https://console.firebase.google.com/).
2. Click **Add Project** and follow the on-screen instructions.
3. Once the project is created, click the **Gear Icon** (Project Settings) next to "Project Overview" in the top-left sidebar.

## 2. Generate a Service Account Key (For the Backend)

Since the backend uses the FCM HTTP v1 API, you need a Service Account JSON file to authenticate.

1. In **Project Settings**, go to the **Service accounts** tab.
2. Make sure "Firebase Admin SDK" is selected.
3. Click the **Generate new private key** button.
4. This will download a `.json` file to your computer.
5. Rename the downloaded file to exactly `firebase-credentials.json`.

## 3. Place the Credentials File in the Laravel Project

1. Move the `firebase-credentials.json` file into your Laravel project's `storage/app/` directory.
   - **Path**: `storage/app/firebase-credentials.json`
2. **Important**: Do not commit this file to public version control (like GitHub). Ensure it is added to your `.gitignore`.
3. The `FirebaseNotificationService` class in the backend is already programmed to look for the file exactly at this path to authenticate with Google.

## 4. Web Push Setup (Partners & Admins)

To enable Web Push notifications for the Partner and Admin panels, you must configure the Firebase JS SDK keys.

1. In the **Project Settings** > **General** tab, scroll down to **Your apps**.
2. Add a new **Web App**.
3. It will give you a `firebaseConfig` object.
4. Open your project's `.env` file and add these variables with the values provided:
   ```env
   FIREBASE_API_KEY="your-api-key"
   FIREBASE_AUTH_DOMAIN="your-auth-domain"
   FIREBASE_PROJECT_ID="your-project-id"
   FIREBASE_STORAGE_BUCKET="your-storage-bucket"
   FIREBASE_MESSAGING_SENDER_ID="your-sender-id"
   FIREBASE_APP_ID="your-app-id"
   ```
5. Next, in your Firebase Console, go to **Project Settings** > **Cloud Messaging** tab.
6. Scroll down to **Web configuration** and click **Generate key pair**.
7. Add this key to your `.env`:
   ```env
   FIREBASE_VAPID_KEY="your-generated-vapid-key"
   ```
8. **IMPORTANT:** Open `public/firebase-messaging-sw.js` and manually replace the `YOUR_API_KEY` etc. placeholder values at the top of the file with your actual values. (Service Workers cannot read from `.env` files automatically).

For the frontend (whether React, Vue, Flutter, or React Native), you will need the Firebase Web/App SDK configured with your **Firebase Config keys** (apiKey, authDomain, projectId, storageBucket, messagingSenderId, appId).

1. In the **Project Settings** > **General** tab, scroll down to **Your apps**.
2. Add a new App (Web for PWA/website, Android/iOS for mobile).
3. Follow the instructions to get the configuration object.
4. The frontend app must request notification permissions from the user.
5. Once permission is granted, the frontend must retrieve the **FCM Device Token**.
6. The frontend must then send this token to the backend using the API endpoint:
   - `PUT /api/fcm-token`
   - Payload: `{"fcm_token": "YOUR_DEVICE_TOKEN_HERE"}`

## 5. Rich Notifications (Images)

The backend is configured to send **Rich Notifications** (notifications with an image). 
When the admin sends a custom notification with a listing image or custom upload, the backend passes the absolute URL of the image to FCM.

### For Mobile Apps (Android / iOS)
- Ensure the Mobile App's push notification handler is set up to display the `image` field from the FCM payload. Native Firebase SDKs usually handle this out-of-the-box for Android, but iOS may require a "Notification Service Extension" to download and display the image.

### For Web (Service Worker)
- Ensure your `firebase-messaging-sw.js` file handles incoming pushes and displays the image properly. Modern browsers support the `image` property in the `showNotification` options.

## 6. Storage Linking (Required for Images)

If you are using the Custom Admin Notification sender to upload custom images, they are stored in the `storage/app/public/notifications` directory.

To ensure Firebase and the users' phones can actually download and view the image over the internet, you must run the following command on your server:

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`.

## Troubleshooting

- **No Push Notifications Arriving?** 
  - Ensure the user's `fcm_token` is saved correctly in the `users` database table.
  - Check the backend `storage/logs/laravel.log` for any FCM authentication errors.
  - Verify that `firebase-credentials.json` exists in `storage/app/` and is readable.

- **Images not showing in push notifications?**
  - Verify `php artisan storage:link` has been run.
  - Ensure the `APP_URL` in your `.env` file is set to your actual domain (e.g., `APP_URL=https://yourdomain.com`). If it is set to `localhost`, the mobile device won't be able to fetch the image.
