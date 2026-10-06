# FeeTrack HRMS App — API Documentation

**Base URL**: `http://your-domain.com/api/hrms`
**Content-Type**: `application/json`
**Auth**: JWT Bearer Token — `Authorization: Bearer <token>`

> **Important**: The HRMS API operates on a separate authentication guard (`hrms_api`) from the Customer and Partner APIs to prevent token crossover. All routes documented here automatically fall under the `/api/hrms` prefix.

All endpoints are wrapped in the `force.json` middleware.

---

## Table of Contents

1. [Authentication](#1-authentication)
2. [Attendance (Employee)](#2-attendance-employee)
3. [Team Attendance (Managers)](#3-team-attendance-managers)
4. [Staff & Organization](#4-staff--organization)
5. [Products](#5-products)
6. [CRM & Sales](#6-crm--sales)
7. [Targets & Commissions](#7-targets--commissions)
8. [Payroll & Advances](#8-payroll--advances)
9. [Job Postings & Referrals](#9-job-postings--referrals)
   - [9.1 Get All Job Templates](#91-get-all-job-templates)
   - [9.2 Get Single Template with Dynamic Fields](#92-get-single-template-with-dynamic-fields)
   - [9.3 Get Job Plans](#93-get-job-plans)
   - [9.4 Create Draft Job Post](#94-create-draft-job-post)
   - [9.5 Publish Draft Job Post](#95-publish-draft-job-post)
   - [9.6 List Job Posts](#96-list-job-posts)
   - [9.7 Create Active Job Post (Direct)](#97-create-active-job-post-direct)
   - [9.8 Get Single Job Post](#98-get-single-job-post)
   - [9.9 Update Job Post](#99-update-job-post)
   - [9.10 Delete Job Post](#910-delete-job-post)
   - [9.11 Close Job Post](#911-close-job-post)
   - [9.12 Get Job Posts History](#912-get-job-posts-history)
   - [9.13 Get Job Billing History](#913-get-job-billing-history)
   - [9.14 Get Job Applications](#914-get-job-applications-for-a-post)
   - [9.15 Update Application Status](#915-update-application-status)
   - [Dynamic Fields Flow (Mobile Guide)](#dynamic-fields-flow-mobile-app-guide)

---


## 1. Authentication

### Data Authorization & Scoping (View Permissions)
The HRMS API natively supports complex role-based scoping using the `HasHrmsApiFilters` trait.
When fetching arrays of data (e.g., Team Attendance, Leaves, Tasks), the API enforces the following hierarchical data visibility limits for managers based on their permissions:

1. **`viewAny`**: Returns data for ALL employees registered under the partner instance.
2. **`viewBranch`**: Returns data for ALL employees registered in the same branch as the authenticated user.
3. **`viewTeam`**: Returns data exclusively for the user's direct subordinates and departmental hierarchy.
4. **`viewOwn`** (Fallback): Returns data exclusively for the authenticated user.

*Note: The API infers the required module prefix dynamically (e.g., calling the leave endpoints automatically applies `leave_viewAny`, `leave_viewBranch`, etc).*

### Employee Login (Public)
`POST /login`

Authenticates an employee using either their email or mobile number along with their password. Only users with the `employee` role can log in via this endpoint.

**Body**:
```json
{
  "login": "employee@example.com", 
  "password": "Password123"
}
```
*(Note: The `login` field can accept either a valid email address or a mobile number string).*

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Logged in successfully.",
  "data": {
    "user": {
      "id": "uuid",
      "name": "John Employee",
      "email": "employee@example.com",
      "mobile": "9876543210",
      "role": "employee",
      "status": "active"
    },
    "token": "eyJhbG...",
    "token_type": "bearer"
  }
}
```

### Get Profile (Protected)
`GET /profile`

Returns the currently authenticated employee's profile, including their roles and permissions arrays.

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": {
    "id": "uuid",
    "name": "John Employee",
    "email": "employee@example.com",
    "role": "employee",
    "roles": ["employee"],
    "permissions": ["mark_attendance", "view_own_attendance"]
  }
}
```

### Update Profile (Protected)
`PUT` or `POST /profile`

Updates the authenticated employee's profile information. Allows updating basic details and profile image.

**Content-Type**: `multipart/form-data` or `application/json`

**Form Data / JSON Body** (All fields optional):
- `name` (string, max 100)
- `email` (string, unique email)
- `mobile` (string, unique mobile)
- `profile_image` (file/image, max 2MB)
- `fcm_token` (string, for push notifications)

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Profile updated successfully.",
  "data": {
    "id": "uuid",
    "name": "Jane Employee",
    "email": "jane@example.com",
    "mobile": "9876543210",
    "profile_image": "profile_images/xyz.jpg"
  }
}
```

### Logout (Protected)
`POST /logout`

Invalidates the current session token.

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Logged out successfully"
}
```

### Dashboard
`GET /dashboard`

Returns an overview of the employee's current state: today's attendance, pending leave count, open task count, latest global notices, and (for managers) a summary of their team's attendance for the day.

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": {
    "attendance_status": "punch_in",
    "punch_in_time": "10:00:00",
    "punch_out_time": null,
    "pending_leaves_count": 1,
    "open_tasks_count": 3,
    "latest_notices": [
      {
        "id": 1,
        "title": "Office closed on Friday",
        "type": "global"
      }
    ],
    "team_summary": {
      "punch_in": 5,
      "punch_out": 2,
      "absent": 1,
      "leave": 1,
      "half_day": 0,
      "holiday": 0,
      "weekOff": 0,
      "late": 0
    }
  }
}
```

---

## 2. Attendance (Employee)

### Attendance Status Glossary

The API can return various attendance statuses for an employee on a given day. App developers should handle these statuses in the UI:

- **`notPunchIn`**: The employee has not punched in today, and the current time is still **before** the company's auto-absent cutoff time.
- **`punch_in`**: The employee has successfully punched in for the day but has not yet punched out.
- **`punch_out`**: The employee punched out, and their total punch_in minutes met or exceeded the `min_punch_out_mins` threshold (usually 8 hours).
- **`short_leave`**: The employee punched out, and their total punch_in minutes fell slightly short of a full day but met the `min_short_leave_mins` threshold (e.g., 7 hours).
- **`half_day`**: The employee punched out, and their total punch_in minutes only met the `min_half_day_mins` threshold (e.g., 4 hours).
- **`absent`**: The employee either did not punch in by the auto-absent cutoff time, or they punched out with fewer punch_in minutes than the half-day minimum.
- **`leave`**: The employee is on an approved leave for that day (e.g., Casual Leave, Sick Leave).
- **`holiday`**: The day is a designated company holiday.
- **`weekOff`**: The day is a standard weekly day off (e.g., Sunday).

*(Note: "Late" is not a primary status string. Instead, check if `late_minutes` is greater than `0` on a `punch_in` or `punch_out` record).*

### Get Checklists
`GET /attendance/checklists`

Fetches the required checklist questions for the user before they punch in or out.

**Query Parameters**:
- `mode` (string): `punch_in` or `punch_out`. Defaults to `punch_in`.

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "partner_id": 1,
      "question": "Are you wearing safety gear?",
      "mode": "punch_in",
      "is_active": 1,
      "is_checked": false,
      "created_at": "2026-07-14T00:00:00.000000Z",
      "updated_at": "2026-07-14T00:00:00.000000Z"
    }
  ]
}
```

### Submit Checklist
`POST /attendance/submit-checklist`

Submits the checklist answers independently before calling punch-in or punch-out. 
- If `mode` is `punch_in`, verifies the employee has NOT punched in yet.
- If `mode` is `punch_out`, verifies the employee HAS punched in but NOT punched out yet.

**Content-Type**: `application/json`

**JSON Body**:
```json
{
  "mode": "punch_in",
  "checklistAnswers": [
    {
      "id": 1,
      "is_checked": true
    }
  ]
}
```

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Checklist submitted successfully."
}
```

### Punch In
`POST /attendance/punch-in`

Marks the employee's attendance for the day. Requires a selfie upload and geographical coordinates. 
**Note:** This endpoint will return a `422` error if the user is on an approved leave, or if today is a company holiday. It automatically calculates `late_minutes` and sets the status to `punch_in`.

**Content-Type**: `multipart/form-data`

**Form Data**:
- `lat` (numeric, required): Latitude of check-in.
- `lng` (numeric, required): Longitude of check-in.
- `selfie` (file/image, required): Image file for facial verification.

**Response** `201 Created`:
```json
{
  "status": "success",
  "message": "Punched in successfully.",
  "data": {
    "id": 1,
    "employee_id": "uuid",
    "date": "2026-07-09",
    "check_in": "2026-07-09T10:00:00.000000Z",
    "check_in_lat": 28.7041,
    "check_in_lng": 77.1025,
    "check_in_selfie": "attendance_selfies/xyz.jpg",
    "status": "punch_out"
  }
}
```

### Punch Out
`POST /attendance/punch-out`

Marks the employee's check-out time. Requires an active punch-in for the current day.
**Note:** This endpoint calculates total `punch_in_minutes` and automatically updates the final status (e.g., `punch_out`, `half_day`, `short_leave`) based on HRMS settings.

**Content-Type**: `multipart/form-data`

**Form Data**:
- `lat` (numeric, required): Latitude of check-out.
- `lng` (numeric, required): Longitude of check-out.
- `selfie` (file/image, required): Image file for facial verification.

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Punched out successfully.",
  "data": {
    "id": 1,
    "employee_id": "uuid",
    "date": "2026-07-09",
    "check_in": "2026-07-09T10:00:00.000000Z",
    "check_out": "2026-07-09T18:00:00.000000Z",
    "check_out_lat": 28.7041,
    "check_out_lng": 77.1025,
    "check_out_selfie": "attendance_selfies/abc.jpg",
    "status": "punch_out"
  }
}
```

### Today's Attendance
`GET /attendance/today`

Fetches the employee's attendance record for the current day.
**Note:** If the employee has not punched in today, the `data` object will NOT be `null`. Instead, it will be a complete attendance entity structure with all values set to `null` (except for `employee_id` and `date`).

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "date": "2026-07-09",
    "check_in": "2026-07-09T10:00:00.000000Z",
    "check_out": null,
    "status": "punch_out",
    "checklist_responses": null,
    "punch_in_minutes": 480
  }
}
```

### Attendance Details
`GET /attendance/{id}`

Fetches the complete details of a specific attendance record by its ID. 
**Note:** The user can only view their own attendance records, or records belonging to their team members if they are a manager.

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Attendance details fetched successfully.",
  "data": {
    "id": 1,
    "employee_id": "uuid",
    "date": "2026-07-09",
    "check_in": "10:00:00",
    "check_out": "18:00:00",
    "check_in_lat": 28.7041,
    "check_in_lng": 77.1025,
    "check_in_photo": null,
    "check_in_selfie": "attendance_selfies/xyz.jpg",
    "check_out_lat": 28.7041,
    "check_out_lng": 77.1025,
    "check_out_photo": null,
    "check_out_selfie": "attendance_selfies/abc.jpg",
    "status": "punch_out",
    "punch_in_minutes": 480,
    "late_minutes": null,
    "checklist_responses": [
      {
        "question": "Are you wearing safety gear?",
        "answer": true
      }
    ],
    "check_out_checklist_responses": null,
    "created_at": "2026-07-09T10:00:00.000000Z",
    "updated_at": "2026-07-09T18:00:00.000000Z",
    "check_in_selfie_url": "http://domain.com/storage/attendance_selfies/xyz.jpg",
    "check_out_selfie_url": "http://domain.com/storage/attendance_selfies/abc.jpg",
    "check_in_photo_url": null,
    "check_out_photo_url": null
  }
}
```

### Attendance History
`GET /attendance/history`

Fetches the employee's past attendances for a specific month, including a summary of their performance (punch_out, absent, leaves).

**Query Parameters** (Optional):
- `month` (integer): e.g., `7` for July. Defaults to current month.
- `year` (integer): e.g., `2026`. Defaults to current year.
- `status` (string): Filter by `punch_out`, `absent`, `half_day`, `leave`, `holiday`, `weekOff`, `late`, or `all status`.

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Attendance history fetched successfully.",
  "data": {
    "summary": {
      "punch_out": 20,
      "absent": 4,
      "half_day": 1,
      "punch_in": 0,
      "leave": 2,
      "holiday": 1,
      "weekOff": 4,
      "late": 2
    },
    "history": [
      {
        "id": 5,
        "date": "2026-07-09",
        "status": "punch_out",
        "status_reason": "Present",
        "check_in_time": "10:00:00",
        "check_out_time": "18:00:00",
        "punch_in_hours": "8h 0m"
      }
    ]
  }
}
```

---

## 3. Team Attendance (Manager/Partner)

### Team Employees List
`GET /team-attendance/employees`

Fetches a paginated list of team members with their basic info and today's attendance status.

**Query Parameters** (Optional):
- `search` (string): Filter by name, email, or employee ID.

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": "uuid",
        "name": "Amit Kumar",
        "email": "amit.kumar@gmail.com",
        "profile_image_url": "https://...",
        "today_status": "absent"
      }
    ],
    "total": 1
  }
}
```

### Today's Team Attendance
`GET /team-attendance/today`

Fetches today's team attendance summary counts and the detailed list of team members.

**Query Parameters** (Optional):
- `search` (string): Filter by name, email, or employee ID.
- `status` (string): Filter by `punch_out`, `absent`, `half_day`, `leave`, `holiday`, `weekOff`, `late`, or `all status`.

**Response** `200 OK`:
```json
{
  "status": "success",
  "summary": {
    "punch_out": 18,
    "absent": 2,
    "half_day": 1,
    "punch_in": 4,
    "leave": 3,
    "holiday": 0,
    "weekOff": 0,
    "late": 1
  },
  "data": [
    {
      "id": "uuid",
      "name": "Priya Patel",
      "email": "priya.patel@gmail.com",
      "profile_image_url": "https://...",
      "today_status": "leave",
      "status_reason": "Casual Leave Full Day",
      "check_in_time": null,
      "check_out_time": null
    }
  ]
}
```

### Employee Monthly History (Team View)
`GET /team-attendance/history`

Fetches a specific employee's attendance history for a given month, including a summary of their performance.

**Query Parameters**:
- `employee_id` (string, required): The UUID of the employee.
- `month` (integer, optional): The month number (e.g., `7` for July). Defaults to current month.
- `year` (integer, optional): The year (e.g., `2026`). Defaults to current year.
- `status` (string, optional): Filter by `punch_out`, `absent`, `half_day`, `leave`, `holiday`, `weekOff`, `late`, or `all status`.

**Response** `200 OK`:
```json
{
  "status": "success",
  "summary": {
    "punch_out": 18,
    "absent": 2,
    "half_day": 0,
    "punch_in": 0,
    "leave": 1,
    "holiday": 0,
    "weekOff": 4,
    "late": 1
  },
  "employee": {
    "id": "uuid",
    "name": "Venkatesh Rathod",
    "email": "venkatesh@gmail.com",
    "profile_image_url": "https://..."
  },
  "data": [
    {
      "id": 14,
      "date": "2026-07-10",
      "status": "punch_out",
      "status_reason": "Present",
      "check_in_time": "09:12:00",
      "check_out_time": "18:30:00",
      "punch_in_hours": "9h 18m"
    }
  ]
}
```

---

## 4. Salary Management (Manager/Partner)

### Get Employee Salary Structure
`GET /employees/{id}/salary-structure`

Fetches the defined salary structure (basic, allowances, deductions) for a specific employee.

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "employee_id": "uuid",
    "basic_salary": "50000.00",
    "allowances": {
      "hra": 10000,
      "da": 5000,
      "medical": 2000
    },
    "deductions": {
      "pf": 1800,
      "pt": 200
    },
    "gross_salary": "67000.00",
    "net_salary": "65000.00",
    "created_at": "...",
    "updated_at": "..."
  }
}
```

### Update Employee Salary Structure
`POST /employees/{id}/salary-structure`

Updates or creates the salary structure for a specific employee. The gross and net salary will be auto-calculated.

**Request Body**:
```json
{
  "salary_type": "base_plus_target",
  "monthly_target": 100000,
  "commission_percent": 5.5,
  "recovery_percent": 2.0,
  "commission_level_id": null,
  "basic_salary": 50000,
  "allowances": {
    "hra": 10000,
    "da": 5000,
    "medical": 2000
  },
  "deductions": {
    "pf": 1800,
    "pt": 200
  }
}
```

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Salary structure updated successfully.",
  "data": { ... }
}
```

---

## 5. Leaves

### Get Leave Categories & Balances
`GET /leaves/categories`
Fetches all configured leave categories for the company along with the employee's allocated, used, pending, and remaining balances for the current year.

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Leave categories fetched successfully.",
  "data": [
    {
      "id": 1,
      "name": "Paid Leave",
      "total_days": 6,
      "used_days": 2,
      "pending_days": 1,
      "remaining_days": 3
    },
    {
      "id": 2,
      "name": "Unpaid Leave",
      "total_days": 12,
      "used_days": 0,
      "pending_days": 0,
      "remaining_days": 12
    }
  ]
}
```

### Get Own Leaves
`GET /leaves`
Fetches the employee's own leave history including the related leave category details.

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "employee_id": "uuid",
      "leave_category_id": 1,
      "start_date": "2026-07-20",
      "end_date": "2026-07-22",
      "type": "Paid Leave",
      "reason": "Family function",
      "status": "pending",
      "created_at": "2026-07-10T10:00:00.000000Z",
      "leave_category": {
        "id": 1,
        "name": "Paid Leave",
        "days": 6
      }
    }
  ]
}
```

### Apply for Leave
`POST /leaves/apply`
Applies for leave under a specified leave category. Checks whether the requested duration exceeds the employee's remaining leave balance.

**Body**:
```json
{
  "leave_category_id": 1,
  "start_date": "2026-07-20",
  "end_date": "2026-07-22",
  "reason": "Family function"
}
```
*Note: `type` (category name string) is also accepted as a fallback if `leave_category_id` is omitted.*

**Response** `201 Created` (Success):
```json
{
  "status": "success",
  "message": "Leave applied successfully.",
  "data": {
    "id": 1,
    "employee_id": "uuid",
    "leave_category_id": 1,
    "start_date": "2026-07-20",
    "end_date": "2026-07-22",
    "type": "Paid Leave",
    "reason": "Family function",
    "status": "pending"
  }
}
```

**Response** `422 Unprocessable Entity` (Insufficient Balance):
```json
{
  "status": "error",
  "message": "Insufficient leave balance for Paid Leave. Available: 2 day(s), Requested: 3 day(s)."
}
```

### Team Leaves (Manager)
`GET /team-leaves`
**Query Params**: `employee_id`, `status` (pending, approved, rejected)

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "employee_id": "uuid",
        "start_date": "2026-07-20",
        "end_date": "2026-07-22",
        "type": "casual",
        "status": "pending",
        "employee": {
          "id": "uuid",
          "name": "Jane Doe"
        }
      }
    ],
    "total": 1
  }
}
```

### Approve/Reject Team Leave
`POST /team-leaves/{id}/status`
**Body**:
```json
{
  "status": "approved"
}
```

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Leave status updated to approved.",
  "data": {
    "id": 1,
    "status": "approved"
  }
}
```

---

## 6. Tasks

### Get Assigned Tasks
`GET /tasks`
Fetches tasks assigned to the employee.

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "title": "Prepare Monthly Report",
      "description": "Gather metrics for July",
      "status": "pending",
      "due_date": "2026-07-31",
      "assigner": {
        "id": "uuid",
        "name": "Manager Name"
      }
    }
  ]
}
```

### Update Task Status
`POST /tasks/{id}/status`
**Body**:
```json
{
  "status": "in_progress"
}
```
*(Options: pending, in_progress, completed)*

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Task status updated.",
  "data": {
    "id": 1,
    "status": "in_progress"
  }
}
```

### Update Task (Manager)
`PUT /tasks/{id}`
Updates a task's details.

### Delete Task (Manager)
`DELETE /tasks/{id}`
Deletes a task.

### Team Tasks (Manager)
`GET /team-tasks`
**Query Params**: `employee_id`, `status`

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "title": "Prepare Monthly Report",
        "status": "pending",
        "employee": {
          "id": "uuid",
          "name": "Employee Name"
        }
      }
    ]
  }
}
```

### Create Task for Team Member (Manager)
`POST /team-tasks`
**Body**:
```json
{
  "employee_id": 2,
  "title": "Prepare Monthly Report",
  "description": "Gather metrics for July",
  "due_date": "2026-07-31"
}
```

**Response** `201 Created`:
```json
{
  "status": "success",
  "message": "Task assigned successfully.",
  "data": {
    "id": 1,
    "employee_id": 2,
    "title": "Prepare Monthly Report",
    "status": "pending"
  }
}
```

---

## 7. Notices (Notice Board)

### Get Active Notices
`GET /notices` *(or `GET /notice-board`)*

Fetches the list of active notices/announcements for the employee. Both endpoints return the exact same data.

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Notices fetched successfully.",
  "data": [
    {
      "id": 1,
      "title": "Office Closed on Friday",
      "content": "Due to national holiday.",
      "type": "global",
      "user_id": "uuid",
      "start_date": "2026-07-13 00:00:00",
      "end_date": "2026-07-16 00:00:00",
      "created_at": "2026-07-13T10:00:00.000000Z",
      "updated_at": "2026-07-13T10:00:00.000000Z"
    }
  ]
}
```

### Get Notice Details
`GET /notices/{id}`

Fetches the complete details of a specific notice by its ID.

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Notice details fetched successfully.",
  "data": {
    "id": 1,
    "title": "Office Closed on Friday",
    "content": "Due to national holiday.",
    "type": "global",
    "user_id": "uuid",
    "start_date": "2026-07-13 00:00:00",
    "end_date": "2026-07-16 00:00:00",
    "created_at": "2026-07-13T10:00:00.000000Z",
    "updated_at": "2026-07-13T10:00:00.000000Z"
  }
}
```

### Create Notice (Manager)
`POST /notices`
Creates a new notice. 

**Request Body**:
```json
{
  "title": "Office closed on Friday",
  "content": "Due to a national holiday.",
  "type": "global", 
  "user_ids": [1, 2],
  "branch_ids": [1],
  "department_ids": [2],
  "start_date": "2026-07-13",
  "end_date": "2026-07-16",
  "action_link": "https://example.com",
  "action_text": "Read more"
}
```
**Fields**:
- `title` (required): string.
- `content` (required): string.
- `type` (required): `global`, `personal`, `employee`, `branch`, or `department`.
- `user_ids` (array): required if type is `personal` or `employee`.
- `branch_ids` (array): required if type is `branch`.
- `department_ids` (array): required if type is `department`.
- `start_date`, `end_date` (optional): dates to show the notice.
- `action_link`, `action_text` (optional): button on the notice.

### Update Notice (Manager)
`PUT /notices/{id}`
Updates an existing notice. Accepts the exact same fields as `POST /notices`.

### Delete Notice (Manager)
`DELETE /notices/{id}`
Deletes a notice.

---

## 8. Expenses

### Get Own Expenses
`GET /expenses`

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "amount": 1500.50,
      "category": "Travel",
      "date": "2026-07-10",
      "status": "pending"
    }
  ]
}
```

### Submit Expense
`POST /expenses`
**Body**:
```json
{
  "amount": 1500.50,
  "category": "Travel",
  "date": "2026-07-10",
  "description": "Client meeting taxi"
}
```

**Response** `201 Created`:
```json
{
  "status": "success",
  "message": "Expense submitted successfully.",
  "data": {
    "id": 1,
    "status": "pending"
  }
}
```

### Update Expense
`PUT /expenses/{id}`
Updates an expense.

### Delete Expense
`DELETE /expenses/{id}`
Deletes an expense.

### Team Expenses (Manager)
`GET /team-expenses`
**Query Params**: `employee_id`, `status`

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "amount": 1500.50,
        "status": "pending",
        "employee": {
          "name": "Jane Doe"
        }
      }
    ]
  }
}
```

### Approve/Reject Team Expense
`POST /team-expenses/{id}/status`
**Body**:
```json
{
  "status": "approved"
}
```

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Expense status updated to approved.",
  "data": {
    "id": 1,
    "status": "approved"
  }
}
```

---

## 9. Leads

### Get Assigned Leads
`GET /leads`

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Acme Corp Lead",
        "status": "new"
      }
    ]
  }
}
```

### Create Lead
`POST /leads`
**Body**:
```json
{
  "customer_name": "Acme Corp Lead",
  "customer_mobile": "9876543210",
  "notes": "Looking for HRMS software",
  "assigned_to": "uuid-of-employee",
  "status": "new"
}
```

**Response** `201 Created`:
```json
{
  "status": "success",
  "data": {
    "id": "uuid-here",
    "customer_name": "Acme Corp Lead",
    "status": "new"
  }
}
```

### Update Lead
`PUT /leads/{id}`
**Body**: Same as Create Lead.

### Update Lead Status
`PUT /leads/{id}/status`
**Body**:
```json
{
  "status": "interested"
}
```

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Lead status updated successfully.",
  "data": {
    "id": 1,
    "status": "interested"
  }
}
```

---

## 4. Staff & Organization

### Get Staff
`GET /staff`
Fetches a list of staff members for the current partner. Returns basic relationships including roles, department, manager, branch, and shift.

**Query Parameters (Filters)**:
| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Search by name, email, mobile, or employee code |
| `status` | string | Filter by employment_status (e.g., active, resigned) |
| `department_id` | integer | Filter by department ID |
| `branch_id` | integer | Filter by branch ID |
| `role_id` | integer | Filter by role ID |
| `shift_id` | integer | Filter by work shift ID |

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Staff fetched successfully.",
  "data": [
    {
      "id": "uuid",
      "name": "John Doe",
      "email": "john@example.com",
      "mobile": "1234567890",
      "employee_code": "EMP001",
      "basic_salary": 50000,
      "employment_status": "active",
      "roles": [],
      "department": {},
      "manager": {},
      "branch": {},
      "shift": {}
    }
  ]
}
```

### Get Staff Profile
`GET /staff/{id}`
Fetches the detailed profile of a specific staff member.

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Staff profile fetched successfully.",
  "data": {
    "id": "uuid",
    "name": "John Doe"
  }
}
```

### Create Staff
`POST /staff`
Creates a new staff member.

**Body Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | Yes | Max 255 chars |
| `email` | string | Yes | Unique email |
| `mobile` | string | Yes | Unique mobile number |
| `password` | string | Yes | Minimum 6 chars |
| `role_id` | integer | Yes | Existing role ID |
| `employee_code` | string | No | Max 50 chars, unique |
| `basic_salary` | numeric | No | Min 0 |
| `department_id` | integer | No | Existing department ID |
| `reporting_to` | string/uuid | No | Existing user ID for Manager |
| `branch_id` | integer | No | Existing branch ID |
| `shift_id` | integer | No | Existing shift ID |
| `joining_date` | date | No | |
| `resignation_date` | date | No | |
| `termination_date` | date | No | |
| `employment_status`| string | No | `active`, `resigned`, `terminated`, `on_leave` (Default: `active`) |
| `status` | string | No | `active`, `inactive` (Default: `active`) |

**Response** `201 Created`:
```json
{
  "status": "success",
  "message": "Staff created successfully.",
  "data": { ... }
}
```

### Update Staff
`PUT /staff/{id}`
Updates an existing staff member.

**Body Parameters**:
All parameters are optional. If provided, they will be updated.
| Parameter | Type | Description |
|-----------|------|-------------|
| `name` | string | Max 255 chars |
| `email` | string | Unique email (ignoring self) |
| `mobile` | string | Unique mobile number (ignoring self) |
| `password` | string | Minimum 6 chars |
| `role_id` | integer | Existing role ID |
| `employee_code` | string | Max 50 chars, unique (ignoring self) |
| `basic_salary` | numeric | Min 0 |
| `department_id` | integer | Existing department ID |
| `reporting_to` | string/uuid | Existing user ID for Manager |
| `branch_id` | integer | Existing branch ID |
| `shift_id` | integer | Existing shift ID |
| `joining_date` | date | |
| `resignation_date` | date | |
| `termination_date` | date | |
| `employment_status`| string | `active`, `resigned`, `terminated`, `on_leave` |
| `status` | string | `active`, `inactive` (System login status) |

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Staff updated successfully.",
  "data": { ... }
}
```

### Delete Staff
`DELETE /staff/{id}`
Deletes a staff member. Note: You cannot delete yourself.

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Staff deleted successfully."
}
```

### Get All Permissions
`GET /permissions`
Fetches all available permissions in the system.

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "role_create",
      "guard_name": "web"
    }
  ]
}
```

### Get Roles
`GET /roles`
Fetches available roles for the partner organization.

**Query Parameters (Filters)**:
| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Search by role name |

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "partnerId_RoleName",
      "guard_name": "web",
      "permissions_count": 2
    }
  ]
}
```

### Get Role Details
`GET /roles/{id}`
Fetches the details of a specific role, including its attached permissions.

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "name": "partnerId_RoleName",
    "guard_name": "web",
    "permissions": [
      {
        "id": 1,
        "name": "role_create",
        "guard_name": "web"
      },
      {
        "id": 2,
        "name": "role_update",
        "guard_name": "web"
      }
    ]
  }
}
```

### Create Role
`POST /roles`
Creates a new role.

**Body Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | Yes | Max 50 chars |
| `permissions`| array | No | Array of permission names or IDs |

**Example Request Body**:
```json
{
  "name": "HR Manager",
  "permissions": [
    "role_create",
    "role_update",
    "department_create"
  ]
}
```

**Response** `201 Created`:
```json
{
  "status": "success",
  "data": {
    "id": 2,
    "name": "partnerId_RoleName"
  }
}
```

### Update Role
`PUT /roles/{id}`
Updates an existing role.

**Body Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | Yes | Max 50 chars |
| `permissions`| array | No | Array of permission names or IDs |

**Example Request Body**:
```json
{
  "name": "HR Manager",
  "permissions": [
    "role_create",
    "role_update"
  ]
}
```

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": { ... }
}
```

### Delete Role
`DELETE /roles/{id}`
Deletes a role.

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Role deleted successfully."
}
```

### Get Departments
`GET /departments`
Fetches departments.

**Query Parameters (Filters)**:
| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Search by department name |

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "IT Department"
    }
  ]
}
```

### Create Department
`POST /departments`
Creates a department.

**Body Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | Yes | Max 255 chars |
| `description` | string | No | Department description |
| `parent_id` | integer | No | ID of the parent department |
| `branch_heads` | object | No | Key-value pairs where key is `branch_id` and value is `user_id` (head). Example: `{"1": 4, "2": 10}` |

**Response** `201 Created`:
```json
{
  "status": "success",
  "data": { ... }
}
```

### Update Department
`PUT /departments/{id}`
Updates an existing department.

**Body Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | Yes | Max 255 chars |
| `description` | string | No | Department description |
| `parent_id` | integer | No | ID of the parent department |
| `branch_heads` | object | No | Key-value pairs where key is `branch_id` and value is `user_id` (head). Example: `{"1": 4, "2": 10}` |

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": { ... }
}
```

### Delete Department
`DELETE /departments/{id}`
Deletes a department.

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Department deleted successfully."
}
```

### Get Branches
`GET /branches`
Fetches branches.

**Query Parameters (Filters)**:
| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Search by branch name or location |

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Head Office",
      "location": "New York"
    }
  ]
}
```

### Get Branches Listing
`GET /branches-listing`
Fetches branches without search filter, mainly used for simple dropdowns.

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": [ ... ]
}
```

### Create Branch
`POST /branches`
Creates a branch.

**Body Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | Yes | Max 255 chars |
| `address` | string | No | Physical address |
| `lat` | number | No | Latitude coordinate |
| `lng` | number | No | Longitude coordinate |
| `radius` | integer | No | Geofencing radius in meters (min 1, defaults to 100) |
| `manager_id` | integer | No | Valid User ID of the branch manager |
| `status` | string | No | `active` or `inactive` (defaults to active) |
| `company_logo` | file | No | Branch or Company logo image (max 2MB) |
| `gst_number` | string | No | GST Registration Number |
| `founded_year` | string | No | Year founded |
| `website` | string | No | Company Website URL |
| `company_size` | string | No | e.g., 10-50 employees |
| `company_type` | string | No | e.g., Private, Public |
| `industry` | string | No | e.g., IT, Healthcare |
| `about_company` | string | No | Bio or description of branch |
| `linkedin_url` | string | No | LinkedIn URL |
| `facebook_url` | string | No | Facebook URL |
| `instagram_url` | string | No | Instagram URL |

**Response** `201 Created`:
```json
{
  "status": "success",
  "data": { ... }
}
```

### Update Branch
`PUT /branches/{id}`
Updates an existing branch.

**Body Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | Yes | Max 255 chars |
| `address` | string | No | Physical address |
| `lat` | number | No | Latitude coordinate |
| `lng` | number | No | Longitude coordinate |
| `radius` | integer | No | Geofencing radius in meters (min 1, defaults to 100) |
| `manager_id` | integer | No | Valid User ID of the branch manager |
| `status` | string | No | `active` or `inactive` (defaults to active) |
| `company_logo` | file | No | Branch or Company logo image (max 2MB) |
| `gst_number` | string | No | GST Registration Number |
| `founded_year` | string | No | Year founded |
| `website` | string | No | Company Website URL |
| `company_size` | string | No | e.g., 10-50 employees |
| `company_type` | string | No | e.g., Private, Public |
| `industry` | string | No | e.g., IT, Healthcare |
| `about_company` | string | No | Bio or description of branch |
| `linkedin_url` | string | No | LinkedIn URL |
| `facebook_url` | string | No | Facebook URL |
| `instagram_url` | string | No | Instagram URL |

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": { ... }
}
```

### Delete Branch
`DELETE /branches/{id}`
Deletes a branch.

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Branch deleted successfully."
}
```

### Get Work Shifts
`GET /work-shifts`
Fetches work shifts.

**Query Parameters (Filters)**:
| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Search by shift name |

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Morning Shift",
      "start_time": "09:00:00",
      "end_time": "18:00:00"
    }
  ]
}
```

### Create Work Shift
`POST /work-shifts`
Creates a work shift.

**Body Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | Yes | Max 255 chars |
| `start_time` | time | Yes | e.g. "09:00" |
| `end_time` | time | Yes | e.g. "18:00" |

**Response** `201 Created`:
```json
{
  "status": "success",
  "data": { ... }
}
```

### Update Work Shift
`PUT /work-shifts/{id}`
Updates an existing work shift.

**Body Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | Yes | Max 255 chars |
| `start_time` | time | Yes | e.g. "09:00" |
| `end_time` | time | Yes | e.g. "18:00" |

**Response** `200 OK`:
```json
{
  "status": "success",
  "data": { ... }
}
```

### Delete Work Shift
`DELETE /work-shifts/{id}`
Deletes a work shift.

**Response** `200 OK`:
```json
{
  "status": "success",
  "message": "Work Shift deleted successfully."
}
```

---

## 5. Products

### Get Product Categories
`GET /product-categories`
Fetches product categories.

### Create Product Category
`POST /product-categories`
Creates a product category. Requires `name`.

### Update Product Category
`PUT /product-categories/{id}`
Updates an existing product category.

### Delete Product Category
`DELETE /product-categories/{id}`
Deletes a product category.

### Get Products
`GET /products`
Fetches products.

### Create Product
`POST /products`
Creates a product. Requires `name`, `category_id`, `price`.

### Update Product
`PUT /products/{id}`
Updates an existing product.

### Delete Product
`DELETE /products/{id}`
Deletes a product.

---

## 6. CRM & Sales

### Get Leads
`GET /leads`
Fetches leads assigned to the user (or all if authorized).

### Create Lead
`POST /leads`
Creates a lead. Requires `name`, `mobile`.

### Update Lead
`PUT /leads/{id}`
Updates a lead.

### Delete Lead
`DELETE /leads/{id}`
Deletes a lead.

### Update Lead Status
`PUT /leads/{id}/status`
Updates lead status. Requires `status`.

### Get Customer Visits
`GET /customer-visits`
Fetches customer visits.

### Create Customer Visit
`POST /customer-visits`
Creates a customer visit record. Requires `lead_id`, `visit_date`, `notes`.

### Update Customer Visit
`PUT /customer-visits/{id}`
Updates a customer visit.

### Delete Customer Visit
`DELETE /customer-visits/{id}`
Deletes a customer visit.

### Get Lead Orders
`GET /lead-orders`
Fetches lead orders.

### Create Lead Order
`POST /lead-orders`
Creates a lead order.

**Body**:
```json
{
  "lead_id": "uuid-here",
  "discount": 100,
  "paid_amount": 500,
  "items": [
    {
      "product_id": "uuid-here",
      "quantity": 2,
      "price": 1000
    }
  ]
}
```
*Note: The API automatically calculates `total_amount` (subtotal), `final_amount`, and `remaining_balance`. It also assigns the correct `current_stage_id` and sets `approval_status` to `pending` based on the partner's Order Pipeline.*

### Update Lead Order
`PUT /lead-orders/{id}`
Updates a lead order.

**Body**: Same as Create Lead Order (except `lead_id` is omitted).

### Delete Lead Order
`DELETE /lead-orders/{id}`
Deletes a lead order.

---

## 7. Targets & Commissions

### Get My Targets
GET /my-targets
Fetches the authenticated employee's commission targets and metrics.

### Get Commission History
GET /commissions/history
Fetches the employee's commission payout history.

---

## 8. Payroll & Advances

### Get My Payroll
GET /payroll/my
Fetches the employee's payslip history.

### Get Advance Payments
GET /advance-payments
Fetches the employee's advance payment requests.

### Request Advance Payment
POST /advance-payments
Requests an advance payment. Requires mount, month, year, eason.



---

## 9. Job Postings & Referrals

> **Base Prefix**: All routes below are under `/api/hrms/`
> **Auth**: Bearer Token (Partner or employee with relevant `jobpost_*` permissions)

---

### 9.1 Get All Job Templates
`GET /api/hrms/job-templates`

Returns all active job templates with their **dynamic field definitions** (`dynamic_fields`) that the mobile app should render as a form when creating a job post from a template.

**Response** `200 OK`:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Sales Executive",
            "category": "Sales",
            "overview": "Brief overview of the role",
            "description": "Detailed job description text",

            "job_type": "Full Time",
            "salary_type": "Fixed Only",
            "default_salary_min": 15000,
            "default_salary_max": 25000,
            "distance": "10km",
            "job_city": "Mumbai",
            "skills": ["Communication", "Negotiation"],

            "minimum_education": "12th Pass",
            "english_level": "Basic",
            "min_experience_years": 0,
            "max_experience_years": 2,
            "gender_preference": "Any",
            "joining_fee_required": false,
            "additional_perks": ["PF", "ESI"],
            "interview_information": [],

            "dynamic_fields": [
                {
                    "id": "q_abc123",
                    "question": "Do you have a two-wheeler?",
                    "type": "radio",
                    "options": ["Yes", "No"],
                    "required": true,
                    "conditional_parent": null,
                    "conditional_value": null
                },
                {
                    "id": "q_def456",
                    "question": "Years of sales experience",
                    "type": "text",
                    "options": [],
                    "required": true,
                    "conditional_parent": null,
                    "conditional_value": null
                }
            ],
            "dynamic_fields_count": 2
        }
    ]
}
```

**Field Types for `dynamic_fields[].type`:**

| Type | Render As |
|:---|:---|
| `text` | Single-line text input |
| `textarea` | Multi-line text input |
| `radio` | Radio button group (single select) |
| `select` | Dropdown (single select) |
| `checkbox` | Checkbox group (multi select) |
| `file` | File/document upload |

---

### 9.2 Get Single Template with Dynamic Fields
`GET /api/hrms/job-templates/{id}`

Returns the full detail of a single active template including all dynamic fields. Use this when the user selects a specific template in the job creation flow.

**Response** `200 OK`:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Sales Executive",
        "category": "Sales",
        "job_type": "Full Time",
        "salary_type": "Fixed Only",
        "default_salary_min": 15000,
        "default_salary_max": 25000,
        "distance": "10km",
        "job_city": "Mumbai",
        "minimum_education": "12th Pass",
        "english_level": "Basic",
        "min_experience_years": 0,
        "max_experience_years": 2,
        "gender_preference": "Any",
        "joining_fee_required": false,
        "additional_perks": ["PF", "ESI"],
        "interview_information": [],
        "dynamic_fields": [ ... ],
        "dynamic_fields_count": 2
    }
}
```

---

### 9.3 Get Job Plans
`GET /api/hrms/job-plans`

Returns all active pricing plans available for publishing job posts.

**Response** `200 OK`:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Unlimited Tier",
            "price": 5000,
            "job_limit": null,
            "validity_days": 365
        }
    ]
}
```

---

### 9.4 Create Draft Job Post
`POST /api/hrms/job-posts/draft`

Creates a blank draft job post. Optionally pass `template_id` to pre-populate all fields (including `job_city`, `dynamic_fields`, salary defaults, etc.) from the selected template. Returns the draft `id` which is used in subsequent steps.

**Body**:
```json
{
  "template_id": 1
}
```

**Response** `201 Created`:
```json
{
    "success": true,
    "message": "Draft created successfully",
    "data": {
        "id": 42,
        "status": "draft",
        "job_title": "Sales Executive",
        "job_city": "Mumbai",
        "distance": "10km",
        "salary_min": 15000,
        "salary_max": 25000,
        "salary_type": "Fixed Only",
        "minimum_education": "12th Pass",
        "english_level": "Basic",
        "gender_preference": "Any",
        "screening_questions": [ ... ]
    }
}
```

---

### 9.5 Publish Draft Job Post
`POST /api/hrms/job-posts/{id}/publish`

Publishes a draft job post. Before publishing, you must fully update the draft using **Update Job Post** (`PUT /job-posts/{id}`). This endpoint finalizes the post and handles payment if required.

**Body**:
```json
{
  "job_plan_id": 1,
  "referral_budget": 5000
}
```

**Response** `200 OK` (wallet-only payment):
```json
{
    "success": true,
    "message": "Job Post published successfully",
    "data": { ... }
}
```

**Response** `200 OK` (online payment required):
```json
{
    "success": true,
    "requires_payment": true,
    "online_payable": 4500,
    "payment_link": "https://..."
}
```

---

### 9.6 List Job Posts
`GET /api/hrms/job-posts`

Returns a paginated list of the authenticated partner's job posts.

**Query Parameters:**
| Parameter | Type | Description |
|:---|:---|:---|
| `status` | string | Filter by: `active`, `draft`, `closed` |
| `per_page` | int | Items per page (default: 10) |

**Response** `200 OK`:
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 42,
                "job_title": "Sales Executive",
                "job_code": "JOB-SAL-4567",
                "employment_type": "Full-time",
                "job_city": "Mumbai",
                "distance": "10km",
                "salary_min": 15000,
                "salary_max": 25000,
                "salary_type": "Fixed Only",
                "gender_preference": "Male",
                "minimum_education": "12th Pass",
                "english_level": "Basic",
                "joining_fee_required": false,
                "vacancies_count": 5,
                "status": "active",
                "created_at": "2026-09-28T10:00:00Z"
            }
        ],
        "total": 10
    }
}
```

---

### 9.7 Create Active Job Post (Direct)
`POST /api/hrms/job-posts`
**Content-Type:** `multipart/form-data`

Creates and immediately activates a job post (without the draft flow). Use this for direct API integrations.

**Required Fields:**

| Parameter | Type | Required | Description |
|:---|:---|:---|:---|
| `job_title` | string | ✅ | Title of the job |
| `employment_type` | string | ✅ | e.g. `Full-time`, `Part-time`, `Contract` |
| `job_city` | string | ✅ | City where the job is located |
| `distance` | string | ✅ | e.g. `10km`, `25km`, `Entire City`, `Pan India` |
| `vacancies_count` | integer | ✅ | Min: 1 |
| `referral_budget` | numeric | ✅ | Min: 0 |
| `job_description` | string | ✅ | Full job description |
| `salary_type` | string | ✅ | e.g. `Fixed Only`, `Fixed + Incentive`, `Incentive Only` |
| `salary_min` | numeric | ✅ | Minimum salary |
| `salary_max` | numeric | ✅ | Maximum salary |
| `joining_fee_required` | boolean | ✅ | `0` or `1` |
| `minimum_education` | string | ✅ | e.g. `Any`, `10th Pass`, `Graduate` |
| `english_level` | string | ✅ | e.g. `No English`, `Basic`, `Fluent` |
| `gender_preference` | string | ✅ | e.g. `Any`, `Male`, `Female` |

**Optional Fields:**

| Parameter | Type | Description |
|:---|:---|:---|
| `department_id` | integer | Department ID |
| `branch_id` | integer | Branch ID |
| `work_location_type` | string | `On-site` or `Work From Home` (maps to `is_work_from_home`) |
| `is_work_from_home` | boolean | Alternative to `work_location_type` |
| `job_type` | string | e.g. `In-office`, `Field-job`, `Remote` |
| `experience_type` | string | e.g. `Fresher Only`, `Experienced Only`, `Any` |
| `min_experience_years` | numeric | Min experience (years) |
| `max_experience_years` | numeric | Max experience (years) |
| `min_age` | numeric | Minimum candidate age |
| `max_age` | numeric | Maximum candidate age |
| `additional_perks` | array | e.g. `["PF","ESI","Health Insurance"]` |
| `interview_information` | array | Interview details |
| `template_id` | integer | Copy screening questions from this template (if screening_questions is empty) |
| `screening_questions` | array | JSON or array of questions and candidate answers |
| `application_deadline` | date | Last date to apply (`YYYY-MM-DD`) |
| `skills` | string | Comma-separated skills |
| `banner` | file (image) | Max 2MB |
| `experience` | string | Legacy experience string |
| `salary` | string | Legacy salary string |

**Success Response** `201 Created`:
```json
{
    "success": true,
    "message": "Job Post created successfully",
    "data": { ... },
    "requires_payment": false,
    "online_payable": 0
}
```
> If `online_payable > 0`, response includes `"payment_link": "https://..."` and post status stays `draft` until payment.

---

### 9.8 Get Single Job Post
`GET /api/hrms/job-posts/{id}`

Returns the full detail of a specific job post.

**Response** `200 OK`:
```json
{
    "success": true,
    "data": {
        "id": 42,
        "job_title": "Sales Executive",
        "job_code": "JOB-SAL-4567",
        "employment_type": "Full-time",
        "job_city": "Mumbai",
        "distance": "10km",
        "salary_type": "Fixed Only",
        "salary_min": 15000,
        "salary_max": 25000,
        "minimum_education": "12th Pass",
        "english_level": "Basic",
        "gender_preference": "Male",
        "joining_fee_required": false,
        "min_experience_years": 0,
        "max_experience_years": 2,
        "is_work_from_home": false,
        "additional_perks": ["PF", "ESI"],
        "screening_questions": [ ... ],
        "interview_information": [],
        "status": "active",
        "vacancies_count": 5,
        "referral_budget": 5000
    }
}
```

---

### 9.9 Update Job Post
`PUT /api/hrms/job-posts/{id}`
*(or `POST` with `_method=PUT` when uploading a banner)*

Updates an existing draft or active job post. Accepts all the same fields as **Create Job Post** (9.7), with these differences:

| Parameter | Notes |
|:---|:---|
| `status` | Optional: `draft`, `active`, or `closed`. Defaults to current status if omitted. |
| `referral_budget` | Cannot be changed via this endpoint (use publish flow). |
| `screening_questions` | Pass the complete JSON array to update dynamic screening answers. |
| `banner` | New image file if you want to replace the existing banner. |

**Success Response** `200 OK`:
```json
{
    "success": true,
    "message": "Job Post updated successfully",
    "data": { ... }
}
```

---

### 9.10 Delete Job Post
`DELETE /api/hrms/job-posts/{id}`

**Auth:** Bearer Token (Partner or user with `jobpost_delete` permission)

Soft-deletes the specified job post.

**Response** `200 OK`:
```json
{
    "success": true,
    "message": "Job Post deleted successfully"
}
```

---

### 9.11 Close Job Post
`POST /api/hrms/job-posts/{id}/close`

Closes an active job post immediately.

**Response** `200 OK`:
```json
{
    "success": true,
    "message": "Job closed successfully"
}
```

---

### 9.12 Get Job Posts History
`GET /api/hrms/job-posts-history`

Fetches a paginated list of all past and current job posts. Supports filtering by `status`.

**Query Parameters:**
| Parameter | Type | Description |
|:---|:---|:---|
| `status` | string | e.g. `active`, `draft`, `closed` |
| `per_page` | int | Items per page |

**Response** `200 OK`:
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [ ... ],
        "total": 15
    }
}
```

---

### 9.13 Get Job Billing History
`GET /api/hrms/job-posts/billing-history`

Fetches a paginated list of wallet transactions related to job post publishing.

**Query Parameters:**
| Parameter | Type | Description |
|:---|:---|:---|
| `status` | string | e.g. `success`, `pending`, `failed` |
| `branch_id` | int | Filter by branch |
| `per_page` | int | Items per page |

**Response** `200 OK`:
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "transaction_id": "JOB-1234",
                "amount": 500,
                "status": "completed",
                "type": "job_post"
            }
        ],
        "total": 5
    }
}
```

---

### 9.14 Get Job Applications (for a Post)
`GET /api/hrms/applied-jobs`

Returns a paginated list of all applications received for the partner's job posts.

**Query Parameters:**
| Parameter | Type | Description |
|:---|:---|:---|
| `post_id` | int | Filter by specific job post |
| `status` | string | Filter by: `pending`, `shortlisted`, `hired`, `rejected` |
| `per_page` | int | Items per page |

**Response** `200 OK`:
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 101,
                "post_id": 42,
                "apply_user_id": 5,
                "designation": "Sales Rep",
                "status": "pending",
                "resume": "resumes/abc.pdf",
                "screening_answers": { ... },
                "applied_at": "2026-09-28T10:00:00Z"
            }
        ],
        "total": 25
    }
}
```

---

### 9.15 Update Application Status
`PUT /api/hrms/applied-jobs/{id}/status`

Updates the status of a specific job application.

**Body**:
```json
{
  "status": "shortlisted"
}
```

**Valid statuses:** `pending`, `shortlisted`, `hired`, `rejected`, `interviewed`

**Response** `200 OK`:
```json
{
    "success": true,
    "message": "Application status updated",
    "data": { ... }
}
```

---

### Dynamic Fields Flow (Mobile App Guide)

The recommended flow for creating a job post on mobile using templates:

```
1. GET /api/hrms/job-templates
   → Show list to partner for selection

2. GET /api/hrms/job-templates/{id}
   → Load template defaults + dynamic_fields

3. Render form pre-filled with template defaults
   → Render each dynamic_field as its field type (radio/text/select/checkbox)
   → Partner fills in / adjusts values

4. POST /api/hrms/job-posts/draft  { template_id: X }
   → Create draft with template defaults

5. PUT /api/hrms/job-posts/{draft_id}
   → Submit full form data including:
      - job_city, distance, salary_type, salary_min, salary_max
      - minimum_education, english_level, gender_preference
      - joining_fee_required, experience_type
      - screening_questions (array of { id, question, type, answer })

6. GET /api/hrms/job-plans
   → Show available plans

7. POST /api/hrms/job-posts/{draft_id}/publish  { job_plan_id, referral_budget }
   → Publish and handle payment if required
```

