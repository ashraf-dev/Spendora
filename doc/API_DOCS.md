# Spendora API Documentation

Version **v1** REST API for the Spendora expense tracker. Authentication uses **Laravel Passport** personal access tokens (Bearer).

| Item | Value |
|------|--------|
| Base URL | `{APP_URL}/api/v1` |
| Local example | `http://localhost:8000/api/v1` |
| Content type | `application/json` (except avatar upload) |
| Auth scheme | `Authorization: Bearer {access_token}` |
| Locales | `en`, `ar` |

A Postman collection is available at [`doc/postman/Spendora_API.postman_collection.json`](postman/Spendora_API.postman_collection.json).

---

## Table of contents

1. [Getting started](#1-getting-started)
2. [Authentication](#2-authentication)
3. [Headers & localization](#3-headers--localization)
4. [Response format](#4-response-format)
5. [HTTP status codes](#5-http-status-codes)
6. [Data models](#6-data-models)
7. [Auth endpoints](#7-auth-endpoints)
8. [Profile endpoints](#8-profile-endpoints)
9. [Category endpoints](#9-category-endpoints)
10. [Expense endpoints](#10-expense-endpoints)
11. [Dashboard](#11-dashboard)
12. [Statistics endpoints](#12-statistics-endpoints)
13. [Rate limiting](#13-rate-limiting)
14. [Error reference](#14-error-reference)
15. [Quick endpoint index](#15-quick-endpoint-index)

---

## 1. Getting started

### Local setup

```bash
php artisan migrate
php artisan db:seed --class=CategorySeeder
php artisan passport:client --personal --name=Spendora
php artisan storage:link
```

Register or login via the API to receive a personal access token. Use that token on all protected routes.

### Typical client flow

1. `POST /auth/register` or `POST /auth/login` → receive `access_token`
2. Call protected endpoints with `Authorization: Bearer {access_token}`
3. `POST /auth/logout` when done (revokes **only** the current token)

---

## 2. Authentication

Spendora issues **Passport personal access tokens** named `flutter` on register/login.

- Send the token as: `Authorization: Bearer {access_token}`
- Missing or invalid tokens return **401**
- Logout revokes the **current** access token only (other devices/sessions keep working)

Protected routes use the `auth:api` middleware (Passport guard).

---

## 3. Headers & localization

### Required / recommended headers

```http
Accept: application/json
Accept-Language: en
Authorization: Bearer ACCESS_TOKEN
Content-Type: application/json
```

| Header | Required | Notes |
|--------|----------|--------|
| `Accept` | Recommended | Use `application/json` |
| `Accept-Language` | Optional | `en` or `ar`. First language tag is used (e.g. `ar-SA` → `ar`) |
| `Authorization` | On protected routes | `Bearer {token}` |
| `Content-Type` | On JSON bodies | `application/json` |
| `Content-Type` | Avatar upload | `multipart/form-data` |

### Locale resolution order

1. `Accept-Language` header (if supported: `en` / `ar`)
2. Authenticated user’s `language` preference
3. App default locale (`APP_LOCALE`, typically `en`)

API success/error **messages** are translated via `lang/{locale}/api.php`. Category `name` is localized the same way; `name_en` and `name_ar` are always returned.

---

## 4. Response format

All JSON responses (except `204 No Content`) use a consistent envelope.

### Success

```json
{
  "success": true,
  "message": "Expense created successfully.",
  "data": {}
}
```

`data` may be an object, array, or `null` (e.g. password update, logout).

### Validation error (`422`)

```json
{
  "success": false,
  "message": "The provided data is invalid.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

### Unauthenticated (`401`)

```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

### Invalid credentials on login (`422`)

```json
{
  "success": false,
  "message": "These credentials do not match our records."
}
```

No field-level `errors` object — credentials are not enumerated.

### Not found (`404`)

Laravel’s JSON 404 response. Used for missing resources and when a user tries to access **another user’s expense** (intentional; does not leak ownership).

### Deleted (`204`)

Expense delete returns an empty body with status **204**.

---

## 5. HTTP status codes

| Code | Meaning |
|------|---------|
| `200` | Success |
| `201` | Created (register, create expense) |
| `204` | Deleted — no body (delete expense) |
| `401` | Unauthenticated |
| `404` | Not found (or unauthorized expense access) |
| `422` | Validation failure or invalid login credentials |
| `429` | Too many requests (auth throttle) |

---

## 6. Data models

### User

| Field | Type | Notes |
|-------|------|--------|
| `id` | integer | |
| `name` | string | max 255 |
| `email` | string | unique, normalized to lowercase |
| `avatar` | string\|null | Public URL when set |
| `language` | string | `en` or `ar` |
| `email_verified_at` | datetime\|null | Cleared if email changes via profile update |
| `created_at` | datetime | |
| `updated_at` | datetime | |

Example:

```json
{
  "id": 1,
  "name": "Jane Doe",
  "email": "jane@example.com",
  "avatar": null,
  "language": "en",
  "email_verified_at": null,
  "created_at": "2026-08-01T10:00:00.000000Z",
  "updated_at": "2026-08-01T10:00:00.000000Z"
}
```

### Category

Categories are **predefined** (seeded). Clients cannot create, update, or delete them.

| Field | Type | Notes |
|-------|------|--------|
| `id` | integer | |
| `name` | string | Localized for current locale |
| `name_en` | string | Always English |
| `name_ar` | string | Always Arabic |
| `icon` | string | Icon key (e.g. `food`) |
| `is_active` | boolean | Inactive categories are hidden / 404 |

Seeded defaults: Food, Transportation, Shopping, Bills, Health, Education, Entertainment, Travel, Other.

### Expense

| Field | Type | Notes |
|-------|------|--------|
| `id` | integer | |
| `category_id` | integer | Must reference an **active** category on create/update |
| `expense_date` | string | `YYYY-MM-DD` |
| `description` | string\|null | max 1000 |
| `amount` | string | Decimal string, e.g. `"25.50"` (always formatted to 2 places in stats) |
| `category` | object\|omitted | Present when loaded |
| `created_at` | datetime | |
| `updated_at` | datetime | |

`user_id` is **never** accepted from the client; it is set from the authenticated user.

### Paginated expenses

List and some statistics endpoints wrap expenses as:

```json
{
  "data": [ /* Expense objects */ ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
  }
}
```

When nested under the API envelope, the full path is `data.data` / `data.meta` (e.g. `GET /expenses`).

### Amounts & percentages

- Money fields in dashboard/statistics are **strings** with two decimal places (`"0.00"`, `"50.00"`).
- Percentage fields are **numbers** (floats), e.g. `40.0`, `12.5`.
- Percentage change rules:
  - Both zero → `0`
  - Previous zero, current &gt; 0 → `100`
  - Otherwise → `((current - previous) / previous) * 100`, rounded to 2 decimals

---

## 7. Auth endpoints

### Register

```http
POST /api/v1/auth/register
```

**Auth:** Public  
**Throttle:** 5 requests per minute  

**Body**

| Field | Rules |
|-------|--------|
| `name` | required, string, max 255 |
| `email` | required, email, max 255, unique |
| `password` | required, confirmed, `Password::default()` |
| `password_confirmation` | required with password |
| `language` | optional, `en` or `ar` (default `en`) |

Password rules:

- **Non-production:** Laravel default (minimum length 8)
- **Production:** min 12, mixed case, letters, numbers, symbols, not compromised

**Example request**

```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "password",
  "password_confirmation": "password",
  "language": "en"
}
```

**Response `201`**

```json
{
  "success": true,
  "message": "Registration successful.",
  "data": {
    "user": { /* User */ },
    "access_token": "...",
    "token_type": "Bearer"
  }
}
```

---

### Login

```http
POST /api/v1/auth/login
```

**Auth:** Public  
**Throttle:** 5 requests per minute  

**Body**

| Field | Rules |
|-------|--------|
| `email` | required, email |
| `password` | required, string |

**Example request**

```json
{
  "email": "jane@example.com",
  "password": "password"
}
```

**Response `200`** — same shape as register (`user`, `access_token`, `token_type`).

**Invalid credentials `422`** — generic message only (no email enumeration).

---

### Logout

```http
POST /api/v1/auth/logout
```

**Auth:** Required  

Revokes the current access token.

**Response `200`**

```json
{
  "success": true,
  "message": "Logged out successfully.",
  "data": null
}
```

---

### Current user

```http
GET /api/v1/auth/user
```

**Auth:** Required  

**Response `200`** — `data` is a User object. Message: `Profile retrieved successfully.`

---

## 8. Profile endpoints

All profile routes require authentication.

### Get profile

```http
GET /api/v1/profile
```

**Response `200`** — User object in `data`.

---

### Update profile

```http
PUT /api/v1/profile
```

**Body**

| Field | Rules |
|-------|--------|
| `name` | required, string, max 255 |
| `email` | required, email, max 255, unique (ignores current user) |

Changing email sets `email_verified_at` to `null`.

**Response `200`** — updated User.

---

### Update password

```http
PUT /api/v1/profile/password
```

**Body**

| Field | Rules |
|-------|--------|
| `current_password` | required, must match current password |
| `password` | required, confirmed, `Password::default()` |
| `password_confirmation` | required with password |

**Response `200`**

```json
{
  "success": true,
  "message": "Password updated successfully.",
  "data": null
}
```

---

### Upload avatar

```http
POST /api/v1/profile/avatar
```

**Content-Type:** `multipart/form-data`

| Field | Rules |
|-------|--------|
| `avatar` | required, image, `jpeg`/`jpg`/`png`/`webp`, max **2048** KB |

Previous avatar file is deleted when replaced. Response includes User with public `avatar` URL.

Requires `php artisan storage:link`.

---

### Delete avatar

```http
DELETE /api/v1/profile/avatar
```

Removes the stored file (if any) and sets `avatar` to `null`.

**Response `200`** — User object.

---

### Update language

```http
PUT /api/v1/profile/language
```

**Body**

| Field | Rules |
|-------|--------|
| `language` | required, `en` or `ar` |

Updates preference and sets the request locale for the response message.

**Response `200`** — User object.

---

## 9. Category endpoints

**Auth:** Required  
Read-only. Only **active** categories are returned.

### List categories

```http
GET /api/v1/categories
```

Ordered by `id` ascending.

**Response `200`**

```json
{
  "success": true,
  "message": "Categories retrieved successfully.",
  "data": [
    {
      "id": 1,
      "name": "Food",
      "name_en": "Food",
      "name_ar": "طعام",
      "icon": "food",
      "is_active": true
    }
  ]
}
```

---

### Show category

```http
GET /api/v1/categories/{category}
```

Inactive or missing → **404**.

---

## 10. Expense endpoints

**Auth:** Required  
All operations are scoped to the authenticated user. Accessing another user’s expense ID returns **404**.

### List expenses

```http
GET /api/v1/expenses
```

**Query parameters**

| Param | Rules | Default / notes |
|-------|--------|------------------|
| `page` | integer ≥ 1 | 1 |
| `per_page` | integer 1–100 | 15 |
| `month` | integer 1–12 | Filter by month of `expense_date` |
| `year` | integer 2000–2100 | Filter by year; can combine with `month` |
| `category_id` | integer, exists | |
| `date_from` | date | Inclusive |
| `date_to` | date, ≥ `date_from` | Inclusive |
| `search` | string, max 255 | Case-insensitive `LIKE` on `description` |
| `sort` | `expense_date`, `amount`, `created_at` | Default `expense_date` |
| `direction` | `asc`, `desc` | Default `desc` |

Secondary sort is always `id` descending.

**Response `200`**

```json
{
  "success": true,
  "message": "Expenses retrieved successfully.",
  "data": {
    "data": [ /* Expense[] with category */ ],
    "meta": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 2
    }
  }
}
```

**Example**

```http
GET /api/v1/expenses?month=8&year=2026&category_id=1&search=lunch&sort=amount&direction=desc&per_page=20
```

---

### Create expense

```http
POST /api/v1/expenses
```

**Body**

| Field | Rules |
|-------|--------|
| `category_id` | required, integer, must exist and be **active** |
| `expense_date` | required, date |
| `amount` | required, numeric, &gt; 0, max 2 decimal places |
| `description` | optional, string, max 1000 |

**Example**

```json
{
  "category_id": 1,
  "expense_date": "2026-08-06",
  "amount": "25.50",
  "description": "Lunch"
}
```

**Response `201`** — Expense with `category` loaded.

---

### Show expense

```http
GET /api/v1/expenses/{expense}
```

**Response `200`** — Expense with category.

---

### Update expense

```http
PUT /api/v1/expenses/{expense}
```

All fields are optional (`sometimes`), but if present must satisfy the same rules as create (`category_id` must be active when provided).

**Response `200`** — Updated Expense with category.

---

### Delete expense

```http
DELETE /api/v1/expenses/{expense}
```

**Response `204`** — empty body.

---

## 11. Dashboard

```http
GET /api/v1/dashboard
```

**Auth:** Required  

Aggregates for the authenticated user only (other users’ expenses are never included).

**Response `200` — `data` shape**

| Key | Description |
|-----|-------------|
| `latest_expenses` | Latest **5** expenses by `expense_date` then `id` (with category) |
| `totals.today` | Sum for today |
| `totals.current_month` | Sum for current calendar month |
| `totals.previous_month` | Sum for previous calendar month |
| `totals.current_year` | Sum for current calendar year |
| `totals.same_day_last_year` | Sum for the same calendar day last year |
| `comparisons.month_over_month_percentage` | Current month vs previous month |
| `comparisons.today_vs_same_day_last_year_percentage` | Today vs same day last year |
| `current_month_by_category` | Every **active** category with `total_amount` and `expense_count` for the current month (zeros included) |

**Example**

```json
{
  "success": true,
  "message": "Dashboard retrieved successfully.",
  "data": {
    "latest_expenses": [],
    "totals": {
      "today": "50.00",
      "current_month": "50.00",
      "previous_month": "0.00",
      "current_year": "50.00",
      "same_day_last_year": "0.00"
    },
    "comparisons": {
      "month_over_month_percentage": 100,
      "today_vs_same_day_last_year_percentage": 100
    },
    "current_month_by_category": [
      {
        "category": { /* Category */ },
        "total_amount": "50.00",
        "expense_count": 1
      }
    ]
  }
}
```

---

## 12. Statistics endpoints

**Auth:** Required  
All stats are scoped to the authenticated user.

Shared query parameters (unless noted):

| Param | Rules | Default |
|-------|--------|---------|
| `month` | integer 1–12 | Current month |
| `year` | integer 2000–2100 | Current year |
| `page` | integer ≥ 1 | 1 (category detail only) |
| `per_page` | integer 1–100 | 15 (category detail only) |

---

### Monthly statistics

```http
GET /api/v1/statistics/monthly
```

**Example:** `GET /api/v1/statistics/monthly?month=8&year=2026`

**Response `data`**

| Field | Description |
|-------|-------------|
| `month` / `year` | Selected period |
| `selected_month_total` | Sum for selected month |
| `previous_month_total` | Sum for previous month |
| `current_year_total` | Sum for the selected **year** (Jan–Dec of `year`) |
| `expense_count` | Count in selected month |
| `highest_expense` | Max amount in month (`"0.00"` if none) |
| `average_expense` | Average amount in month |
| `daily_totals` | One entry per day in the month (`date`, `total`), including zero days |
| `category_totals` | Active categories with `total_amount` and `expense_count` for the month |

---

### Categories overview

```http
GET /api/v1/statistics/categories
```

**Example:** `GET /api/v1/statistics/categories?month=8&year=2026`

**Response `data`**

| Field | Description |
|-------|-------------|
| `month` / `year` | Selected period |
| `month_total` | Total spend in month |
| `categories` | All **active** categories (including zero spend) |

Each category row:

| Field | Description |
|-------|-------------|
| `category` | Category resource |
| `total_amount` | Sum for category in month |
| `expense_count` | Count |
| `percentage` | Share of `month_total` (0 if month total is 0) |
| `recent_expenses` | Up to **5** latest expenses in that category for the month |

---

### Category detail

```http
GET /api/v1/statistics/categories/{category}
```

**Example:** `GET /api/v1/statistics/categories/1?month=8&year=2026&page=1&per_page=15`

Inactive category → **404**.

**Response `data`**

| Field | Description |
|-------|-------------|
| `category` | Category resource |
| `month` / `year` | Selected period |
| `selected_month_total` | Sum for this category in month |
| `expense_count` | Count |
| `expenses` | Paginated expenses (`data` + `meta`) for this category in the month |
| `navigation.previous_month` | `{ "month": N, "year": Y }` |
| `navigation.next_month` | `{ "month": N, "year": Y }` |

---

## 13. Rate limiting

| Endpoint | Limit |
|----------|--------|
| `POST /auth/register` | 5 requests / minute |
| `POST /auth/login` | 5 requests / minute |

Other endpoints use Laravel’s default API throttling (if configured globally). Exceeding limits returns **429**.

---

## 14. Error reference

| Situation | Status | `message` (en) |
|-----------|--------|----------------|
| Validation failed | 422 | The provided data is invalid. (+ `errors`) |
| Bad login credentials | 422 | These credentials do not match our records. |
| Missing/invalid token | 401 | Unauthenticated. |
| Inactive/missing category | 404 | — |
| Other user’s expense | 404 | — |
| Expense deleted | 204 | (no body) |

Arabic messages are returned when locale resolves to `ar` (see [Headers & localization](#3-headers--localization)).

---

## 15. Quick endpoint index

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| `POST` | `/auth/register` | No | Register + token |
| `POST` | `/auth/login` | No | Login + token |
| `POST` | `/auth/logout` | Yes | Revoke current token |
| `GET` | `/auth/user` | Yes | Current user |
| `GET` | `/profile` | Yes | Get profile |
| `PUT` | `/profile` | Yes | Update name/email |
| `PUT` | `/profile/password` | Yes | Change password |
| `POST` | `/profile/avatar` | Yes | Upload avatar |
| `DELETE` | `/profile/avatar` | Yes | Delete avatar |
| `PUT` | `/profile/language` | Yes | Set `en` / `ar` |
| `GET` | `/categories` | Yes | List active categories |
| `GET` | `/categories/{id}` | Yes | Show active category |
| `GET` | `/expenses` | Yes | Paginated expenses + filters |
| `POST` | `/expenses` | Yes | Create expense |
| `GET` | `/expenses/{id}` | Yes | Show own expense |
| `PUT` | `/expenses/{id}` | Yes | Update own expense |
| `DELETE` | `/expenses/{id}` | Yes | Delete own expense |
| `GET` | `/dashboard` | Yes | Dashboard aggregates |
| `GET` | `/statistics/monthly` | Yes | Monthly statistics |
| `GET` | `/statistics/categories` | Yes | Category statistics overview |
| `GET` | `/statistics/categories/{id}` | Yes | Single category statistics |

All paths are relative to `/api/v1`.

---

## Notes for mobile / Flutter clients

- Store `access_token` securely; send it on every protected request.
- Prefer `Accept-Language` matching the device/UI language, or sync via `PUT /profile/language`.
- Parse money as decimal strings — do not assume floating-point JSON numbers.
- Treat expense **404** as “not available” (missing or not owned).
- After avatar upload, use the returned absolute `avatar` URL.
- Delete expense expects **204** with an empty body — do not parse JSON.
