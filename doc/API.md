# Spendora API Documentation

Base URL: `{APP_URL}/api/v1`

## Required headers

```http
Accept: application/json
Accept-Language: en
Authorization: Bearer ACCESS_TOKEN
```

- `Accept-Language` supports `en` or `ar`. Falls back to the authenticated user's `language`, then the app default.
- `Authorization` is required on all protected endpoints.

## Response envelope

Success:

```json
{
  "success": true,
  "message": "Expense created successfully.",
  "data": {}
}
```

Validation error (`422`):

```json
{
  "success": false,
  "message": "The provided data is invalid.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

Unauthenticated (`401`):

```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

## Authentication

### Register

`POST /api/v1/auth/register` — public, rate limited

Body:

```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "password",
  "password_confirmation": "password",
  "language": "en"
}
```

Response `201`:

```json
{
  "success": true,
  "message": "Registration successful.",
  "data": {
    "user": {
      "id": 1,
      "name": "Jane Doe",
      "email": "jane@example.com",
      "avatar": null,
      "language": "en"
    },
    "access_token": "...",
    "token_type": "Bearer"
  }
}
```

### Login

`POST /api/v1/auth/login` — public, rate limited

```json
{
  "email": "jane@example.com",
  "password": "password"
}
```

Invalid credentials return a generic `422` message (no email enumeration).

### Logout

`POST /api/v1/auth/logout` — protected

Revokes **only** the current access token.

### Current user

`GET /api/v1/auth/user` — protected

## Profile

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/profile` | yes | Get profile |
| PUT | `/profile` | yes | Update name/email |
| PUT | `/profile/password` | yes | Change password (`current_password`, `password`, `password_confirmation`) |
| POST | `/profile/avatar` | yes | Upload avatar (`multipart/form-data`, field `avatar`) |
| DELETE | `/profile/avatar` | yes | Delete avatar |
| PUT | `/profile/language` | yes | Set `language` to `en` or `ar` |

## Categories

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/categories` | yes | List active categories |
| GET | `/categories/{category}` | yes | Show active category |

Categories are predefined. Users cannot create, update, or delete them via the API.

Each category includes `name`, `name_en`, `name_ar`, `icon`, and `is_active`.

## Expenses

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/expenses` | yes | Paginated list with filters |
| POST | `/expenses` | yes | Create expense |
| GET | `/expenses/{expense}` | yes | Show own expense |
| PUT | `/expenses/{expense}` | yes | Update own expense |
| DELETE | `/expenses/{expense}` | yes | Delete own expense (`204`) |

### List filters

| Param | Notes |
|-------|-------|
| `page` | Page number |
| `per_page` | Max 100 |
| `month` | 1–12 |
| `year` | e.g. 2026 |
| `category_id` | Filter by category |
| `date_from` / `date_to` | Date range |
| `search` | Searches `description` |
| `sort` | `expense_date`, `amount`, `created_at` |
| `direction` | `asc` or `desc` (default newest date first) |

### Create body

```json
{
  "category_id": 1,
  "expense_date": "2026-08-06",
  "amount": "25.50",
  "description": "Lunch"
}
```

`user_id` is never accepted from the client. Accessing another user's expense returns `404`.

## Dashboard

`GET /api/v1/dashboard` — protected

Returns:

- Latest five expenses
- Totals for today, current month, previous month, current year
- Same calendar day last year total
- Month-over-month and today-vs-same-day-last-year percentage changes (safe with zero totals)
- Current-month totals grouped by category

## Statistics

### Monthly

`GET /api/v1/statistics/monthly?month=8&year=2026`

Returns selected/previous month totals, year total, expense count, highest/average expense, daily totals (including zero days), and category totals.

### Categories overview

`GET /api/v1/statistics/categories?month=8&year=2026`

Returns every active category with totals, counts, percentage of month total, and recent expenses (zeros included).

### Category detail

`GET /api/v1/statistics/categories/{category}?month=8&year=2026&page=1&per_page=15`

Returns category info, month total, count, paginated expenses, and previous/next month navigation.

## Status codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 204 | Deleted (no body) |
| 401 | Unauthenticated |
| 404 | Not found (including unauthorized expense access) |
| 422 | Validation failure |

## Local setup notes

```bash
php artisan migrate
php artisan db:seed --class=CategorySeeder
php artisan passport:client --personal --name=Spendora
php artisan storage:link
```

Use Passport personal access tokens issued by register/login.
