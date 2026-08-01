# ApnaNest Flutter API v1

Base URL: `/api/v1`

Send `Accept: application/json` on every request. Authenticated requests also send:

```http
Authorization: Bearer YOUR_SANCTUM_TOKEN
```

Successful JSON responses use:

```json
{"status":"success","success":true,"message":"...","data":{}}
```

Failed requests consistently use:

```json
{"status":"error","success":false,"message":"Readable error message","data":null,"errors":{}}
```

For HTTP `422`, `errors` contains field-wise message arrays. For other errors
it is an empty object. Display `message` as the general error and map `errors`
beside individual Flutter form fields.

Business modules paused by admin return HTTP `503`:

```json
{"status":"unavailable","success":false,"message":"...","reason":"payments","data":null,"errors":{}}
```

Possible reasons: `maintenance`, `registration`, `listings`, `payments`, `owner_panel`, `user_panel`.

## Public and authentication

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/settings` | App name, fees, branding and public configuration |
| POST | `/auth/send-otp` | Send login/registration OTP |
| POST | `/auth/register` | Register; include `role=user` or `role=owner` |
| POST | `/auth/login` | OTP login and Sanctum token |
| GET | `/rooms` | Filtered, paginated public listings |
| GET | `/rooms/{id-or-slug}` | Room details |
| GET | `/rooms/{id}/similar` | Similar rooms |
| GET | `/cities` | Admin-enabled cities, in configured display order |
| GET | `/room-options` | Room, furnishing and tenant options |
| GET | `/offers` | Active mobile/public offers |
| GET | `/blogs`, `/blogs/{slug}` | Blog listing and detail |
| GET | `/pages/{slug}` | CMS page |
| GET | `/faq` | FAQ content |
| POST | `/contact` | Contact enquiry |
| POST | `/newsletter/subscribe` | Newsletter subscription |

## Authenticated user and owner shared APIs

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/auth/me` | Current account and role |
| POST | `/auth/logout` | Revoke tokens |
| GET | `/profile` | Profile detail |
| POST | `/profile/update` | Update name, phone and avatar (multipart) |
| POST | `/profile/delete-otp` | Account deletion OTP |
| DELETE | `/profile` | Delete account with `otp` |
| GET | `/dashboard` | Role-aware dashboard stats |
| GET | `/wallet` | Wallet and ledger data |
| POST | `/wallet/convert` | Convert eligible points |
| GET | `/payments` | Payment history |
| GET | `/plans` | Available plans |
| GET | `/subscriptions` | Subscription history/current plan |
| POST | `/subscriptions/purchase` | Purchase plan |
| GET | `/referral-stats` | Referral code, rewards and referrals |
| GET | `/complaint-options` | Complaint categories/statuses/priorities |
| GET/POST | `/complaints` | List/create support complaints |
| GET | `/complaints/{id}` | Complaint conversation and activity |
| POST | `/complaints/{id}/replies` | Reply with optional attachment |

All paginated endpoints accept `page` and `limit` (`limit` maximum 50 where supported).

Every paginated response exposes the same Flutter-facing keys:

```json
{
  "status": "success",
  "success": true,
  "message": "...",
  "data": {
    "items": [],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 0,
      "from": null,
      "to": null,
      "next_page_url": null,
      "prev_page_url": null
    }
  }
}
```

Legacy Laravel pagination fields remain in the response for backward
compatibility. New Flutter code should read `items` and `pagination`.

## Renter APIs

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/wishlist` | Saved rooms |
| POST | `/wishlist/toggle/{room}` | Add/remove saved room |
| POST | `/unlock/{room}` | Unlock owner contact |
| GET | `/unlocks` | Unlocked contact history |
| GET/POST/DELETE | `/city-alerts` | Manage room alerts by city |
| POST | `/payments/create-order` | Create Razorpay order |
| POST | `/payments/verify` | Verify successful payment |

## Owner APIs

All owner endpoints require an authenticated account with `role=owner`.

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/owner/dashboard` | Listing, enquiry and wallet summary |
| GET | `/owner/rooms` | Owner listing history |
| GET | `/owner/rooms/{room}` | Owner listing detail including pending/rejected room |
| POST | `/owner/rooms` | Create room (multipart for photos/video) |
| PUT/POST | `/owner/rooms/{room}` | Update room; POST supports multipart uploads |
| DELETE | `/owner/rooms/{room}` | Delete owned room |
| POST | `/owner/rooms/{room}/toggle-status` | Change availability |
| POST | `/owner/rooms/{room}/feature` | Purchase/activate featured listing |
| GET | `/owner/enquiries` | People who unlocked owner listings |

When an owner marks a room as rented, its previous listing entitlement is released. Making it available again follows the same free-listing, active subscription, wallet, or pending Razorpay payment flow as the website. A paid relisting response includes `payment_id`, `amount`, `type=listing`, and `action=mark_available`.

Every owner edit sets `listing_status` back to `pending`; the app should show that the updated listing is awaiting admin approval. Photo/video replacement is atomic: existing media remains available if any new upload cannot be processed.

Room creation/update field choices must come from `/room-options`; do not hard-code database IDs in Flutter.

## Upload limits

- Avatar: image, maximum 2 MB.
- Complaint evidence/reply attachment: JPG, PNG, WebP or PDF, maximum 5 MB.
- Room upload rules are returned through validation errors and should be displayed against each Flutter form field.

## Important app behavior

- Store the Sanctum token using secure device storage.
- After login, route by `data.user.role` (`user` or `owner`).
- On HTTP `401`, clear the token and open login.
- On HTTP `403`, show the API message; do not retry automatically.
- On HTTP `422`, map `errors` to form fields.
- On HTTP `503`, display the maintenance/module message and keep unaffected app modules usable.

## Admin Flutter APIs

Admin base path: `/api/v1/admin`. Login uses the normal OTP login endpoint; the returned account must have `role=admin` and `is_staff_active=true`. Every endpoint enforces the staff member's role permissions.

### Dashboard, people and listings

- `GET /admin/dashboard`, `/admin/analytics`, `/admin/search-analytics`
- `GET /admin/users`, `GET /admin/users/{id}`, `POST /admin/users/{id}/toggle-block`
- `GET /admin/owners`, `POST /admin/owners`, `GET /admin/owners/{id}`
- `POST /admin/owners/{id}/toggle-block`
- `PUT /admin/owners/{id}/verification`
- `GET /admin/rooms`, approve/reject/delete room endpoints
- `POST /admin/rooms/bulk-action` with `room_ids[]` and action `approve`, `reject`, `suspend`, `activate` or `delete`
- Full rejection-reason and room-option CRUD endpoints

### Support

- `GET /admin/complaint-options`
- Complaint list/detail/update/reply/reopen endpoints
- Contact-message list/read/delete endpoints
- City alert and newsletter subscriber list/delete endpoints

### Finance and content

- Payment and payout history; payout processing
- Subscription plan CRUD and activation toggle
- Blog CRUD
- Offer CRUD and activation toggle
- `GET|PUT /admin/pages/{slug}` for CMS pages and FAQ
- `GET|POST /admin/settings`

### Staff security and audit

- `GET /admin/permission-catalog`
- Staff list/create/update/enable-disable endpoints
- Role list/create/update endpoints
- `GET /admin/activity-logs`

Every successful non-GET Admin API action is automatically written to the same activity log used by the web admin panel.

### Maintenance, reports and cleanup

- `GET|PUT /admin/maintenance` manages website and app availability from one place.
- `GET /admin/reports/overview?from=YYYY-MM-DD&to=YYYY-MM-DD`
- `DELETE /admin/search-logs/{id}` deletes one analytics record.
- `DELETE /admin/search-logs` accepts either `all=true` or a `from`/`to` date range.
