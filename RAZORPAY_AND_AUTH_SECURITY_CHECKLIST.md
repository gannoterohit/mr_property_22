# Razorpay + Auth Security Checklist

## 1) Payment security status

This project uses a standard Laravel + Razorpay secure flow:

- Web login uses Laravel session auth
- API/mobile login uses Sanctum bearer tokens
- Razorpay payment creation is server-side
- Payment verification uses Razorpay signature validation
- Webhook validation uses `X-Razorpay-Signature` with HMAC

This means the app is not relying on client-side trust alone.

Important: code-level security is okay, but production configuration must also be correct.

---

## 2) Web login flow

Web login uses the `web` guard and session cookies.

Relevant code:

- `config/auth.php`
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`

Behavior:

- Browser gets a session cookie
- Next request sends that cookie automatically
- Server reads session from server-side session storage
- Actual user data is not stored directly in the cookie

This is normal Laravel behavior.

---

## 3) API login flow

API/mobile auth uses Laravel Sanctum tokens.

Relevant code:

- `app/Http/Controllers/Api/ApiAuthController.php`
- `config/sanctum.php`

Behavior:

- Server creates a token using `createToken()`
- Token is stored in the database (`personal_access_tokens`)
- Client stores the token string locally
- Request sends `Authorization: Bearer <token>`
- Server validates the token

This is the correct API auth pattern.

---

## 4) Session cookie settings to verify in production

Relevant config:

- `config/session.php`

Required secure values:

- `SESSION_SECURE_COOKIE=true`
- `SESSION_HTTP_ONLY=true`
- `SESSION_SAME_SITE=lax` or `strict`
- HTTPS enabled on production

If these are not set correctly, cookie theft / session hijack risk increases.

---

## 5) Razorpay security checks

Relevant code:

- `app/Http/Controllers/RazorpayController.php`

The app validates:

- order ID
- payment ID
- signature
- amount matches expected payment
- currency is `INR`

It also has webhook validation with HMAC signature:

- reads `X-Razorpay-Signature`
- compares with generated HMAC using webhook secret

This is a proper server-side payment verification structure.

---

## 6) What must be configured in production

You must ensure these are true in live deployment:

1. HTTPS is enabled
2. Razorpay webhook URL is set in Razorpay dashboard
3. Webhook secret matches the app secret
4. `.env` / admin settings contain the correct keys
5. No secret/token is exposed in logs or frontend code
6. API token is not stored in URL/query strings
7. Cookies are `HttpOnly` and `Secure`

---

## 7) What to avoid

Avoid these in production:

- storing tokens in browser localStorage if avoidable
- putting token in URL/query string
- logging raw tokens or payment data
- exposing debug exceptions to frontend
- leaving webhook secret blank
- allowing plain HTTP in production

---

## 8) Final conclusion

The code is not showing a major security bug.

The important part is deployment configuration and hardening.

If production is correctly configured with HTTPS, secure cookies, valid Razorpay webhook secret, and safe token handling, then the payment and auth setup is secure enough for real use.

---

## Quick yes/no check

- Code issue? No major code issue found.
- Secure architecture? Yes.
- Production configuration required? Yes, absolutely.
- Webhook required for best security? Yes.
- Token misuse possible? Only if insecure storage/configuration is used.
