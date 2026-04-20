# 🚀 Workflow Engine API Documentation

This document outlines all available APIs for the Workflow Engine system, including endpoints, methods, request bodies, and example usage.

---

## 📌 Overview

The Workflow Engine supports:

* Creating workflow templates
* Triggering workflow instances
* Multi-step approval process
* Role-based and user-based approvals
* Viewing workflow progress

---

# 🧩 API Endpoints

---

## 1️⃣ Create Workflow Template

**POST** `/api/workflow-templates`

### Request Body

```json
{
  "name": "Booking Cancellation Approval",
  "description": "Approval flow for booking cancellation requests.",
  "trigger_event": "booking.cancellation_requested",
  "is_active": 1,
  "steps": [
    {
      "sequence": 1,
      "approver_type": "role",
      "role_id": 1,
      "user_id": null
    },
    {
      "sequence": 2,
      "approver_type": "user",
      "role_id": null,
      "user_id": 2
    }
  ]
}
```

### Description

Creates a reusable workflow template with ordered approval steps.

---

## 2️⃣ Get Workflow Template

**GET** `/api/workflow-templates/{id}`

### Example

```http
GET /api/workflow-templates/1
```

### Description

Retrieve template details including approval steps.

---

## 3️⃣ Update Workflow Template

**PUT** `/api/workflow-templates/{id}`

### Description

Update template metadata and steps.

---

## 4️⃣ Activate Template

**PATCH** `/api/workflow-templates/{id}/activate`

## 5️⃣ Deactivate Template

**PATCH** `/api/workflow-templates/{id}/deactivate`

### Description

Enable or disable a workflow template.

---

## 6️⃣ Trigger Workflow Instance

**POST** `/api/workflow-instances/trigger`

### Request Body

```json
{
  "event_name": "booking.cancellation_requested",
  "source_type": "Booking",
  "source_id": 1,
  "requested_by_user_id": 3
}
```

### Description

Triggers a workflow instance based on a predefined event.

---

## 7️⃣ Get Workflow Instance

**GET** `/api/workflow-instances/{id}`

### Example

```http
GET /api/workflow-instances/1
```

### Description

Retrieve workflow instance details including status and steps.

---

## 8️⃣ Get Approver Inbox

**GET** `/api/approver-inbox`

### Query Parameters

```http
/api/approver-inbox?user_id=1&role_ids[]=1
```

### Description

Fetch all steps awaiting action for a user or role.

---

## 9️⃣ Approve Step

**POST** `/api/workflow-instance-steps/{stepId}/approve`

### Request Body

```json
{
  "user_id": 1,
  "comment": "Approved by Sales Manager"
}
```

---

## 🔟 Reject Step

**POST** `/api/workflow-instance-steps/{stepId}/reject`

### Request Body

```json
{
  "user_id": 2,
  "comment": "Rejected due to missing information"
}
```

---

# 🔄 Recommended Demo Flow

### Step 1: Create Template

```http
POST /api/workflow-templates
```

### Step 2: Trigger Workflow

```http
POST /api/workflow-instances/trigger
```

### Step 3: Load Inbox (Step 1)

```http
GET /api/approver-inbox?user_id=1&role_ids[]=1
```

### Step 4: Approve Step 1

```http
POST /api/workflow-instance-steps/1/approve
```

### Step 5: Load Inbox (Step 2)

```http
GET /api/approver-inbox?user_id=2
```

### Step 6: Approve Step 2

```http
POST /api/workflow-instance-steps/2/approve
```

### Step 7: View Final Status

```http
GET /api/workflow-instances/1
```

---

# 👥 Seeded Users

| ID | Name                  |
| -- | --------------------- |
| 1  | Sarah Sales Manager   |
| 2  | Farid Finance Manager |
| 3  | Cindy Coordinator     |
| 4  | Backup Sales Manager  |

---

# 🏷️ Roles

| ID | Role              |
| -- | ----------------- |
| 1  | Sales Manager     |
| 2  | Finance Manager   |
| 3  | Sales Coordinator |

---

# 📌 Notes

* Events are **predefined system values**
* Each workflow template supports:

  * Multiple steps
  * Role or user assignment
  * Ordered sequence execution
* Only one step is active at a time
* Workflow completes when all steps are approved

---

# 🧠 HTTP Methods Summary

| Method | Usage           |
| ------ | --------------- |
| GET    | Retrieve data   |
| POST   | Create / Action |
| PUT    | Full update     |
| PATCH  | Partial update  |

---

# ✅ Final Output

At the end of the workflow:

* All steps = `approved`
* Workflow status = `approved`

---

# 🎯 Summary

This Workflow Engine demonstrates:

* Event-driven workflow triggering
* Multi-step approval pipeline
* Role-based and user-based assignment
* Real-time workflow state tracking

---

🚀 Ready for demo / assessment submission
