# Waterfall Delivery Mobile API

Base URL: `https://bdwaterfall.com/api`

All protected endpoints use Bearer token authentication:

```http
Authorization: Bearer TOKEN_HERE
Accept: application/json
```

## Test Credentials

Use real delivery staff or delivery manager users from the admin panel.

```json
{
  "mobile": "01700000001",
  "password": "123456"
}
```

## Response Format

Success:

```json
{
  "success": true,
  "message": "Success message",
  "data": {}
}
```

Validation error:

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {}
}
```

Forbidden:

```json
{
  "success": false,
  "message": "You do not have permission to perform this action."
}
```

## Status Values

Mobile status values:

- `pending`
- `delivered`
- `partial_delivered`
- `not_delivered`
- `customer_unavailable`
- `cancelled`

Existing web statuses `assigned`, `in_progress`, and `failed` are mapped to mobile-friendly status values in API responses.

## Auth

### Login

`POST /delivery/login`

```json
{
  "mobile": "01700000001",
  "password": "123456"
}
```

Response:

```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "token": "TOKEN_HERE",
    "user": {
      "id": 1,
      "name": "User Name",
      "mobile": "01700000001",
      "role": "delivery_staff",
      "zone_name": "Zone A",
      "line_name": null
    }
  }
}
```

### Profile

`GET /delivery/profile`

### Logout

`POST /delivery/logout`

## Delivery Staff

### Dashboard

`GET /delivery/dashboard`

Response data:

```json
{
  "today_assigned": 10,
  "delivered": 6,
  "pending": 4,
  "collection": 500,
  "total_due": 200,
  "total_jars": 15,
  "empty_jars_returned": 12
}
```

### Today Deliveries

`GET /delivery/today`

Response data:

```json
[
  {
    "id": 1,
    "order_no": "WF-ORD-000001",
    "customer_id": "WF-CUS-000001",
    "customer_name": "Customer Name",
    "mobile": "01711111111",
    "address": "Customer Address",
    "zone_name": "Zone A",
    "line_name": null,
    "jar_quantity": 2,
    "empty_jar_return": 0,
    "payable_amount": 80,
    "paid_amount": 0,
    "due_amount": 80,
    "preferred_time": "Morning",
    "status": "pending",
    "remarks": null
  }
]
```

### Update Status

`POST /delivery/update-status`

```json
{
  "delivery_id": 1,
  "status": "delivered",
  "delivered_jar_quantity": 2,
  "empty_jar_return": 2,
  "paid_amount": 80,
  "remarks": "Delivered successfully"
}
```

### Bulk Update

`POST /delivery/bulk-update`

```json
{
  "delivery_ids": [1, 2, 3],
  "status": "delivered",
  "remarks": "Delivered in route batch"
}
```

## Delivery Manager

### Dashboard

`GET /delivery-manager/dashboard`

### Staff Progress

`GET /delivery-manager/staff-progress`

### Today Deliveries

`GET /delivery-manager/today-deliveries`

### Assign

`POST /delivery-manager/assign`

```json
{
  "delivery_id": 1,
  "staff_id": 3
}
```

### Reassign

`POST /delivery-manager/reassign`

```json
{
  "delivery_id": 1,
  "staff_id": 4
}
```

## Implementation Notes

- Sanctum is used for token authentication.
- Users are authenticated from the existing `users` table using `mobile` and `password`.
- Roles come from the existing `users.role` column.
- Zone scoping uses `zones.delivery_manager_id`.
- No line/route table exists in the current database, so `line_name` is returned as `null`.
- No delivery-level columns exist for delivered jar quantity or empty jar returns. Jar quantity is computed from `order_items.quantity`; empty jar returns are written to `jar_deposits` when a jar product is available.
