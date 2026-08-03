# Part 4: Selected Classmate's API — Testing Report

## 1. API Information

| Field | Details |
|---|---|
| **Developer** | John Vhinson Fontanos |
| **GitHub Username** | vhinsonj |
| **Repository Name** | filipino-cookbook-api-fontanos |
| **Repository Link** | https://github.com/vhinsonj/filipino-cookbook-api-fontanos |
| **Base URL (local)** | `http://localhost/filipino-cookbook-api-fontanos/public/api` |
| **Authentication Method** | Bearer token, role-based (Admin: read/write, User: read-only) |

**Available Endpoints:**
- `GET /` — Public welcome message (no token required)
- `GET /api/foods` — Retrieve all foods
- `GET /api/foods/{id}` — Retrieve a specific food
- `GET /api/foods/search/{name}` — Search foods by name
- `GET /api/categories` — Retrieve all categories
- `GET /api/ingredients` — Retrieve all ingredients
- `POST /api/foods` — Add a new food (Admin only)

**Selected Endpoints (used in the Part 5 client app):**
- `GET /api/foods`
- `GET /api/foods/{id}`
- `GET /api/categories`
- `GET /api/ingredients`

## 2. Installation & Testing Confirmation Checklist

| Requirement | Status |
|---|---|
| Repository can be downloaded/cloned | ✅ Confirmed |
| Database can be imported | ✅ Confirmed (after re-import of updated SQL file) |
| Dependencies can be installed (`composer install`) | ✅ Confirmed |
| API runs successfully | ✅ Confirmed (via Apache/XAMPP htdocs) |
| Authentication works | ✅ Confirmed (admin/user token roles both tested) |
| Endpoints return valid JSON | ✅ Confirmed |
| Error responses are understandable | ✅ Confirmed (clear JSON error messages for 400/401/403/404/429) |
| Documentation matches actual API behavior | ✅ Confirmed, after developer's README/token updates |

## 3. Installation & Setup Notes (Local Environment Only)

These are local configuration adjustments made to run the project on the tester's machine — not defects in the classmate's code:

- `setBasePath` updated to `/filipino-cookbook-api-fontanos/public` to match the local folder name under Apache/XAMPP htdocs (the classmate's original value assumed a folder named `filipino-cookbook-api`).
- Database name changed locally to `filipino_cookbook_api_fontanos` to avoid colliding with the tester's own project database (both originally used `filipino_cookbook_api`).
- Local database credentials (`root` / no password) filled in to match the tester's XAMPP setup, replacing the repo's placeholder values (`YOUR_DATABASE_USERNAME`, `YOUR_DATABASE_PASSWORD`).
- Token values filled in locally, replacing the repo's placeholder tokens (`YOUR_ACCESS_TOKEN`) — confirmed with the developer that this is expected local setup, not a hidden secret.

## 4. Testing Evidence Summary

| # | Test | Endpoint | Expected | Result |
|---|---|---|---|---|
| 1 | Public route | `GET /` | 200, welcome message | ✅ 200 OK |
| 2 | Get all foods | `GET /api/foods` (admin token) | 200, JSON array | ✅ 200 OK |
| 3 | Get food by ID | `GET /api/foods/1` | 200, single food object | ✅ 200 OK |
| 4 | Search by name | `GET /api/foods/search/adobo` | 200, matching results | ✅ 200 OK |
| 5 | Get categories | `GET /api/categories` | 200, JSON array | ✅ 200 OK |
| 6 | Get ingredients | `GET /api/ingredients` | 200, JSON array | ✅ 200 OK |
| 7 | Add food (admin) | `POST /api/foods` (admin token) | 201 Created | ✅ 201 Created |
| 8 | Add food (read-only) | `POST /api/foods` (user token) | 403 Forbidden | ✅ 403 Forbidden |
| 9 | Add food (missing fields) | `POST /api/foods` (incomplete body) | 400 Bad Request | ✅ 400 Bad Request |
| 10 | Rate limiting | 60+ rapid `GET` requests | 429 after threshold | ✅ 429 Too Many Requests |
| 11 | Input sanitization | `POST` with `<script>` payload | Tags stripped/encoded | ✅ Confirmed sanitized |

Testing was performed using Thunder Client, and later re-confirmed live through the Part 5 client application's built-in Add Food form and Rate Limit Test panel.

## 5. Problems Encountered & Resolution

### Bug 1 — AUTO_INCREMENT missing on `foods.food_id`
- **Issue:** First `POST /api/foods` succeeded, but every subsequent POST returned `500 Internal Server Error` (`PDOException: Duplicate entry '0' for key 'PRIMARY'`).
- **Cause:** `food_id` was defined as `INT PRIMARY KEY` without `AUTO_INCREMENT`; `$db->lastInsertId()` always returned `0`.
- **Status:** Reported to the developer. Developer pushed an updated SQL file with `AUTO_INCREMENT` added. Retested and confirmed fixed — new entries insert cleanly and increment correctly.

### Finding 2 — Session-based rate limiting depends on cookie persistence
- **Observation:** Rate limiting relies on PHP `$_SESSION`. A raw PowerShell request loop without explicit cookie/session handling never triggered the limit (all 200s across 65 requests), since each request appeared as a new session. Thunder Client, which persists cookies across sends, correctly triggered `429 Too Many Requests` after the threshold.
- **Conclusion:** A legitimate design limitation of session-based rate limiting for stateless/non-cookie-aware API clients, not a functional bug. Documented as a limitation rather than reported as an error.

### Note — Tokens and credentials moved to placeholders
- The developer updated the repository to replace real database credentials and tokens with placeholder values (`YOUR_DATABASE_USERNAME`, `YOUR_ACCESS_TOKEN`, etc.) in both the README and source code — a good security practice, in line with the activity's requirement not to upload real credentials to a public repository. Testing values were set locally by the tester and are not part of the public repository.

## 6. Conclusion

The Filipino Cookbook API by John Vhinson Fontanos passed all functional and security tests after the AUTO_INCREMENT fix was applied. Authentication, role-based access control, input validation, input sanitization, and rate limiting all behave as intended. The API is confirmed ready for use as the data source for the Part 5 client application, and was later exercised again live through that client app's UI (Add Food form and Rate Limit Test panel).

---
**Tested by:** Athena Bangilan
**Course/Section:** BSIT-4A, DMMMSU