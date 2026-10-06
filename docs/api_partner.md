# FeeTrack Partner App — API Documentation

**Base URL**: `http://your-domain.com/api/partner`
**Content-Type**: `application/json`
**Auth**: JWT Bearer Token — `Authorization: Bearer <token>`

> **Important**: The Partner API operates on a separate authentication guard (`partner_api`) from the Customer API to prevent token crossover. All routes documented here automatically fall under the `/api/partner` prefix.

All endpoints are wrapped in the `force.json` middleware (always returns JSON, never HTML).

---

## Table of Contents

1. [Authentication (Public & Protected)](#1-authentication-public--protected)
2. [Partner Subscription Management (Auth Required)](#2-partner-subscription-management-auth-required)
3. [Partner Wallet (Auth Required)](#3-partner-wallet-auth-required)
4. [Partner Listings API (Auth Required)](#4-partner-listings-api-auth-required)

---

## 1. Authentication (Public & Protected)

### Partner Register (Public)
`POST /register`

Creates a new partner account and automatically assigns the default free package.

**Body**:
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "mobile": "9876543210",
  "password": "SecurePassword123",
  "password_confirmation": "SecurePassword123"
}
```

**Response** `201 Created`:
```json
{
  "status": "success",
  "message": "Account created successfully! Please complete your KYC and wait for admin approval.",
  "data": {
    "user": {
      "id": "uuid",
      "name": "Jane Doe",
      "email": "jane@example.com",
      "mobile": "9876543210",
      "role": "partner",
      "status": "active"
    },
    "token": "eyJ0eXAiOiJKV1...",
    "token_type": "bearer"
  }
}
```

---

### Partner Login (Public)
`POST /login`

Authenticates a partner and returns a JWT token under the `partner_api` guard.

**Body**:
```json
{
  "email": "jane@example.com",
  "password": "SecurePassword123"
}
```

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Logged in successfully.",
  "data": {
    "user": {
      "id": "uuid",
      "name": "Jane Doe",
      "email": "jane@example.com",
      "role": "partner"
    },
    "token": "eyJ0eXAiOiJKV1...",
    "token_type": "bearer"
  }
}
```

---

### Partner Logout (Auth Required)
`POST /logout`

Invalidates the partner's current JWT token.

**Header**: `Authorization: Bearer <partner_token>`

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Successfully logged out."
}
```

---

### Partner Profile (Auth Required)
`GET /profile`

Returns the currently authenticated partner's profile information.

**Header**: `Authorization: Bearer <partner_token>`

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": {
    "user": {
      "id": "uuid",
      "name": "Jane Doe",
      "email": "jane@example.com",
      "mobile": "9876543210",
      "role": "partner",
      "status": "active"
    }
  }
}
```

---

### Update Partner Profile (Auth Required)
`POST /profile` (or `PUT /profile`)

Updates the partner's profile information. Use `POST` with `multipart/form-data` if you are uploading a profile image.

**Content-Type**: `multipart/form-data` or `application/json`

**Body**: 
- `name`: "Jane Doe"
- `email`: "jane@example.com"
- `mobile`: "9876543210"
- `profile_image`: (File upload - optional)

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Profile updated successfully.",
  "data": {
    "id": "uuid",
    "name": "Jane Doe",
    "email": "jane@example.com",
    "mobile": "9876543210",
    "profile_image": "profile_images/xyz.jpg"
  }
}
```

---

### Update FCM Token (Auth Required)
`POST /fcm-token`

Updates the partner's FCM (Firebase Cloud Messaging) token for push notifications.

**Body**:
```json
{
  "fcm_token": "your_fcm_device_token_here"
}
```

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "FCM token updated successfully."
}
```

---

### Delete Partner Account (Auth Required)
`DELETE /account/delete`

Permanently deletes the currently authenticated partner's account and logs them out.

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Account deleted successfully."
}
```

---

## 2. Partner KYC API (Auth Required)

### Get KYC Requirements
`GET /kyc/requirements`

Returns the dynamic list of requirements needed for partner KYC submission.

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": [
    {
      "label": "Aadhaar Card",
      "key": "aadhaar_card",
      "field_type": "document",
      "document_mode": "front_back",
      "is_required": true
    }
  ]
}
```

---

### Get KYC Profile
`GET /kyc`

Returns the partner's current KYC submission data, including full URLs for uploaded documents.

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": {
    "id": "uuid",
    "status": "pending",
    "submission_data": {
      "aadhaar_card": {
         "type": "document",
         "value": "123412341234",
         "files": {
             "front": "kyc/uuid/aadhaar_card/front/xyz.jpg",
             "front_url": "https://..."
         }
      }
    }
  }
}
```

---

### Submit / Update KYC Profile
`POST /kyc`

Submit or update KYC details. You must upload files using `multipart/form-data`.
The required fields depend on the output of `/kyc/requirements`.

**Content-Type**: `multipart/form-data`

**Body Example**:
- `documentNumbers[aadhaar_card]`: "123412341234"
- `documentFiles[aadhaar_card][front]`: (File Upload)
- `documentFiles[aadhaar_card][back]`: (File Upload)
- `textValues[bank_name]`: "HDFC Bank"

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "KYC documents submitted successfully and are pending review."
}
```

---

## Error Response Format

All errors follow this structure:
```json
{
  "status": "error",
  "message": "Human-readable error description"
}
```

Common HTTP codes:
| Code | Meaning |
|---|---|
| `200` | Success |
| `201` | Resource created |
| `401` | Unauthenticated / Invalid token |
| `403` | Forbidden (e.g. attempting to login with a customer account) |
| `404` | Resource not found |
| `422` | Validation error or business rule violation |
| `500` | Internal server error |
---

## 2. Partner Subscription Management (Auth Required)

> All endpoints require `Authorization: Bearer <partner_token>`

### Approve Cancellation (Leave Request)
`POST /subscriptions/{id}/approve-cancellation`

Approve a customer's request to cancel their active subscription. This is also where you handle the security deposit refund if applicable.

**Body**:
```json
{
  "refund_method": "wallet" // Can be "wallet" or "cash"
}
```

- If `"wallet"`, the security deposit amount is deducted from the partner's main wallet balance and credited to the customer's main wallet. A wallet transaction history is created for both.
- If `"cash"`, it means you returned the deposit via cash/UPI manually, and the system just marks it as refunded without altering the digital wallet balance.

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Cancellation request approved successfully."
}
```

### Reject Cancellation (Subscription Cancel)
`POST /subscriptions/{id}/reject-cancellation`

Reject a customer's request to cancel their subscription. This resets the cancellation status and restores auto-renew if it was disabled.

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Cancellation request rejected successfully."
}
```

---

## 3. Partner Wallet (Auth Required)

> All endpoints require `Authorization: Bearer <partner_token>`

### Get Reserve History
`GET /wallet/reserve-history`

Returns a paginated list of all security deposits currently held or refunded, including the full breakdown of the booking total, rent, and deposit.

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1,
    "history": [
      {
        "id": 1,
        "amount": 7117.00,
        "status": "active",
        "created_at": "2026-07-22T10:00:00.000000Z",
        "booking_id": "uuid",
        "customer": {
          "id": 5,
          "name": "Jane Doe",
          "mobile": "9876543210",
          "email": "jane@example.com",
          "profile_image": null
        },
        "listing": {
          "id": 2,
          "title": "AJ VAI PG Boys and Girls"
        },
        "breakdown": {
          "total": 7117.00,
          "rent": 120.00,
          "deposit": 6997.00
        }
      }
    ]
  }
}
```

---

## 4. Partner Listings API (Auth Required)

> **Important**: This is a **Modular REST API**. You first create a base listing, then use the sub-resource endpoints (like `/images`, `/shifts`, `/trainers`, `/rooms`, `/packages`) to attach the complex relationships. Finally, you call `/submit` to send it for admin approval.

### 3.1 Basic Listing CRUD

#### List Partner Listings
`GET /listings`

Returns all listings belonging to the authenticated partner, including their category and images.

#### Create Draft Listing
`POST /listings`

**Body**:
```json
{
  "category_id": 1,
  "gender_type": "co-living",
  "title": "Elite Fitness Gym",
  "description": "Premium gym with top equipment.",
  "address": "123 Main St",
  "city": "Mumbai",
  "state": "Maharashtra",
  "pincode": "400001",
  "lat": 19.0760,
  "lng": 72.8777
}
```
**Response** `201 Created`: Returns the created listing with its ID. `status` will be "draft".

#### Get Listing Details
`GET /listings/{id}`

Returns the full listing object, including `images`, `meta`, `shifts`, `trainers`, `floors` (and `rooms`), and `packages`.

#### Update Listing
`PUT /listings/{id}`

Body: Same as Create. *Note: Updating an approved/rejected listing will automatically revert its status to `pending`.*

#### Delete Listing
`DELETE /listings/{id}`

Deletes the listing and all its associated images.

---

### 3.2 Sub-Resources

*Note: Adding or modifying any sub-resource automatically sets an approved/rejected listing back to `pending`.*

#### Upload Images
`POST /listings/{id}/images`
**Content-Type**: `multipart/form-data`
**Body**:
- `images[]`: (Multiple file uploads, max 2MB each)

#### Delete Image
`DELETE /listings/{id}/images/{imageId}`

#### Update Custom Fields (Meta)
`PUT /listings/{id}/meta`
**Body**:
```json
{
  "meta": {
    "field_id_1": "Yes",
    "field_id_2": "Free Wi-Fi"
  }
}
```

#### Add Shift
`POST /listings/{id}/shifts`
**Body**:
```json
{
  "shift_name": "morning",
  "start_time": "06:00",
  "end_time": "10:00",
  "max_members": 30,
  "fee": 500
}
```
#### Delete Shift: `DELETE /listings/{id}/shifts/{shiftId}`

#### Add Trainer
`POST /listings/{id}/trainers`
**Content-Type**: `multipart/form-data`
**Body**:
- `name`: "John Doe"
- `specialization`: "Weightlifting"
- `experience_years`: 5
- `photos[]`: (At least 3 images required)
#### Delete Trainer: `DELETE /listings/{id}/trainers/{trainerId}`

#### Add Floor
`POST /listings/{id}/floors`
**Body**:
```json
{
  "name": "Ground Floor",
  "floor_number": 0
}
```
#### Delete Floor: `DELETE /listings/{id}/floors/{floorId}`

#### Add Room (Requires Floor)
`POST /listings/{id}/rooms`
**Content-Type**: `multipart/form-data`
**Body**:
- `floor_id`: 1
- `room_number`: "G-101"
- `room_type`: "single"
- `capacity`: 1
- `security_deposit`: 5000
- `images[]`: (At least 3 images required)
#### Delete Room: `DELETE /listings/{id}/rooms/{roomId}`

#### Add Package
`POST /listings/{id}/packages`
**Body**:
```json
{
  "name": "Monthly Pro",
  "duration_days": 30,
  "price": 1500,
  "type": "monthly",
  "occupancy_type": "standard", // "single", "full_room", "per_bed", "standard", "per_site"
  "features": ["Free Diet Plan", "Locker Included"]
}
```
#### Delete Package: `DELETE /listings/{id}/packages/{packageId}`

---

### 3.3 Submit for Review
`POST /listings/{id}/submit`

Validates that the listing meets all minimum requirements (e.g., at least 3 property images, trainers must have 3 images, rooms must have 3 images). If successful, changes the listing status to `pending`.

**Response**:
```json
{
  "status": "success",
  "message": "Listing submitted for review successfully."
}
```
