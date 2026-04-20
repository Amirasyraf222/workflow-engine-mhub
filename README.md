### Setup Instruction
- Clone project using `git clone https://github.com/Amirasyraf222/workflow-engine-mhub.git`
- Run `composer install` 
- Rename `.env.example` to `.env`
- Create database named "mhub" or based on your database name set in ENV
- Run `php artisan migrate --seed` to migrate database and seed data
- Run `php artisan key:generate` to generate key if required (if needed)
- Run `php artisan serve` to run the backend system

- Flow :
    1. Serve `http://127.0.0.1:8000/workflow-ui/` to test on UI


### Additional Info :
- I added UI/UX for an easy testing on the API


# Workflow Engine API Documentation 

This document outlines all available APIs for the Workflow Engine system, including endpoints, methods, request bodies, and example usage.

---

## Overview

The Workflow Engine supports:

* Creating workflow templates
* Triggering workflow instances
* Multi-step approval process
* Role-based and user-based approvals
* Viewing workflow progress

---

# API Endpoints

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


## Part 3 — Code review findings

Problems in the provided Node.js snippet:
1. SQL injection in all queries
2. Wrong condition logic: it rejects when status *is* `awaiting_action`, which is inverted
3. No check that the step belongs to the instance id in the URL
4. No authorization check
5. No validation
6. No transaction
7. Race condition on concurrent approvals
8. Missing not-found handling
9. Missing proper status codes
10. No audit trail
11. No callback execution after final approval
12. Raw DB calls mixed directly in route handler

### Corrected version
```js
app.post("/api/workflow-instances/:id/steps/:stepId/approve", async (req, res) => {
  const instanceId = Number(req.params.id);
  const stepId = Number(req.params.stepId);
  const userId = Number(req.body.user_id);
  const comment = req.body.comment ?? null;

  if (!Number.isInteger(instanceId) || !Number.isInteger(stepId) || !Number.isInteger(userId)) {
    return res.status(422).json({ message: "Invalid input." });
  }

  const client = await db.connect();

  try {
    await client.query("BEGIN");

    const stepResult = await client.query(
      `SELECT *
       FROM workflow_instance_steps
       WHERE id = $1 AND workflow_instance_id = $2
       FOR UPDATE`,
      [stepId, instanceId]
    );

    if (stepResult.rows.length === 0) {
      await client.query("ROLLBACK");
      return res.status(404).json({ message: "Workflow step not found." });
    }

    const step = stepResult.rows[0];

    if (step.status !== "awaiting_action") {
      await client.query("ROLLBACK");
      return res.status(409).json({ message: "Step is not awaiting action." });
    }

    const authResult = await client.query(
      `SELECT 1
       FROM workflow_instance_steps s
       LEFT JOIN role_user ru ON ru.role_id = s.assigned_role_id AND ru.user_id = $2
       WHERE s.id = $1
         AND (s.assigned_user_id = $2 OR ru.user_id IS NOT NULL)
       LIMIT 1`,
      [stepId, userId]
    );

    if (authResult.rows.length === 0) {
      await client.query("ROLLBACK");
      return res.status(403).json({ message: "You are not allowed to approve this step." });
    }

    await client.query(
      `UPDATE workflow_instance_steps
       SET status = 'approved',
           actioned_by = $2,
           decision = 'approved',
           comment = $3,
           actioned_at = NOW(),
           updated_at = NOW()
       WHERE id = $1`,
      [stepId, userId, comment]
    );

    await client.query(
      `INSERT INTO workflow_step_actions
       (workflow_instance_step_id, workflow_instance_id, acted_by, decision, comment, acted_at, created_at, updated_at)
       VALUES ($1, $2, $3, 'approved', $4, NOW(), NOW(), NOW())`,
      [stepId, instanceId, userId, comment]
    );

    const nextStepResult = await client.query(
      `SELECT *
       FROM workflow_instance_steps
       WHERE workflow_instance_id = $1 AND sequence > $2
       ORDER BY sequence ASC
       LIMIT 1
       FOR UPDATE`,
      [instanceId, step.sequence]
    );

    if (nextStepResult.rows.length > 0) {
      await client.query(
        `UPDATE workflow_instance_steps
         SET status = 'awaiting_action', updated_at = NOW()
         WHERE id = $1`,
        [nextStepResult.rows[0].id]
      );

      await client.query(
        `UPDATE workflow_instances
         SET status = 'in_progress', updated_at = NOW()
         WHERE id = $1`,
        [instanceId]
      );
    } else {
      await client.query(
        `UPDATE workflow_instances
         SET status = 'approved', completed_at = NOW(), updated_at = NOW()
         WHERE id = $1`,
        [instanceId]
      );
    }

    await client.query("COMMIT");
    return res.status(200).json({ message: "Step approved successfully." });
  } catch (error) {
    await client.query("ROLLBACK");
    return res.status(500).json({ message: "Unexpected server error." });
  } finally {
    client.release();
  }
});
```
