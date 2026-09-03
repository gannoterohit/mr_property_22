# RoomRental — Live Server Deployment Plan (Hinglish)

> **Note:** Code-side bugs aur security fixes ho chuke hain.  
> Neeche wala checklist **sirf woh kaam hai jo aapko live server pe manually karna hoga.**

---

## Quick Overview

```
Local (Done ✅)          →    Live Server (Aap karo 🔲)
─────────────────────────────────────────────────────
Bug fixes                →    Server + Domain + SSL
Security hardening       →    .env production values
Payment logic fix        →    Razorpay LIVE keys
Webhook config ready     →    SMTP mail setup
DB indexes migration     →    Cron + Queue worker
```

---

## STEP 1 — Server Setup (Pehle ye lo)

### Minimum Requirements
| Item | Requirement |
|------|-------------|
| PHP | 8.2+ (mbstring, pdo_mysql, gd, curl, zip, bcmath, openssl) |
| Database | MySQL 8 / MariaDB |
| Web Server | Nginx (recommended) ya Apache |
| SSL | Let's Encrypt (free HTTPS) |
| RAM | Minimum 2GB (4GB better) |

### Document Root (Bahut Important!)
Server ka document root **`public/` folder** pe point hona chahiye:

```
❌ Galat:  /var/www/roomrental/
✅ Sahi:   /var/www/roomrental/public/
```

---

## STEP 2 — Code Upload & Install

```bash
# Server pe project folder me jao
cd /var/www/roomrental

# Dependencies (production — dev packages nahi)
composer install --no-dev --optimize-autoloader

# Frontend assets build
npm ci
npm run build

# .env file banao (niche template dekho)
cp .env.example .env
nano .env

# App key generate
php artisan key:generate

# Database tables
php artisan migrate --force

# Storage link (images ke liye)
php artisan storage:link

# Production cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## STEP 3 — Production `.env` (Zaroori Values)

```env
APP_NAME="Room Rental"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_live_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_strong_db_password

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_LIFETIME=120

CACHE_STORE=database
QUEUE_CONNECTION=database

# Optional: Redis agar install kiya ho
# CACHE_STORE=redis
# QUEUE_CONNECTION=redis
# REDIS_HOST=127.0.0.1

# Sanctum — mobile app token 30 din valid (minutes)
SANCTUM_TOKEN_EXPIRATION=43200
SANCTUM_STATEFUL_DOMAINS=yourdomain.com

# Razorpay webhook (backup — admin panel se bhi set kar sakte ho)
RAZORPAY_WEBHOOK_SECRET=
```

### ⚠️ Kabhi mat karna production me:
- `APP_DEBUG=true`
- Dummy seeder run karna live DB pe
- `.env` file Git me commit karna

---

## STEP 4 — SMTP Mail Setup (OTP ke liye zaroori)

**Admin Panel → Business Settings → Email/SMTP tab**

| Field | Example |
|-------|---------|
| Mail Host | `smtp.gmail.com` ya SendGrid/Mailgun host |
| Mail Port | `587` (TLS) ya `465` (SSL) |
| Mail Username | `your@email.com` |
| Mail Password | App password (Gmail ke liye) |
| Contact Email | Same ya support email |

### Test karo:
1. Website se login/register → OTP email aana chahiye
2. Mobile app se `/api/v1/auth/send-otp` → OTP email aana chahiye (response me OTP **nahi** aayega — ye sahi hai)

**Popular SMTP options:**
- Gmail (App Password) — chhote projects ke liye OK
- SendGrid — free tier 100 emails/day
- Mailgun — developers ke liye popular
- Amazon SES — scale ke liye best

---

## STEP 5 — Razorpay LIVE Payment Setup

### 5.1 Razorpay Dashboard
1. [dashboard.razorpay.com](https://dashboard.razorpay.com) → **Live Mode** ON karo
2. **Settings → API Keys** → Live Key ID + Secret copy karo
3. **Settings → Webhooks** → Add webhook:

```
Webhook URL:  https://yourdomain.com/api/v1/webhook/razorpay
Events:       payment.captured, payment.failed (optional)
```

4. Webhook secret copy karo

### 5.2 Admin Panel me daalo
**Admin → Business Settings → Payment tab**

| Field | Value |
|-------|-------|
| Key ID | `rzp_live_xxxxxxxx` |
| Key Secret | Live secret |
| Webhook Secret | Razorpay se copy kiya hua secret |

### 5.3 Payment Test Checklist (Live keys se small amount)
- [ ] User — room unlock payment
- [ ] Owner — listing fee payment
- [ ] Owner — featured room payment
- [ ] User — subscription plan
- [ ] User — booking payment
- [ ] Webhook se payment confirm (app band karke test — webhook backup hai)

---

## STEP 6 — Cron Job (Scheduler)

Server pe crontab kholo:
```bash
crontab -e
```

Ye line add karo:
```
* * * * * cd /var/www/roomrental && php artisan schedule:run >> /dev/null 2>&1
```

**Isse kya hoga:** Sitemap daily regenerate, future scheduled tasks.

---

## STEP 7 — Queue Worker (Email/OTP ke liye)

OTP emails queue me jaate hain. Bina worker ke delay/fail ho sakta hai.

### Option A — Supervisor (Recommended)
```ini
[program:roomrental-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/roomrental/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/roomrental/storage/logs/worker.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start roomrental-worker:*
```

### Option B — Simple cron (kam reliable)
```
* * * * * cd /var/www/roomrental && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

---

## STEP 8 — File Permissions

```bash
cd /var/www/roomrental
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## STEP 9 — Security (Launch Day)

- [ ] Strong admin password set karo (dummy `admin@roomrental.com` delete/change karo)
- [ ] Test users delete karo live DB se
- [ ] HTTPS redirect enable karo (Nginx/Apache config)
- [ ] Database backup daily schedule karo
- [ ] `storage/` aur `.env` publicly accessible nahi hona chahiye

### Database Backup (daily cron example)
```bash
0 3 * * * mysqldump -u USER -p'PASS' DB_NAME > /backups/roomrental_$(date +\%Y\%m\%d).sql
```

---

## STEP 10 — Mobile App Config

App me base URL change karo:
```
https://yourdomain.com/api/v1
```

**Important API endpoints:**
| Purpose | URL |
|---------|-----|
| Send OTP | `POST /auth/send-otp` |
| Login | `POST /auth/login` |
| Register | `POST /auth/register` |
| Rooms | `GET /rooms` |
| Payment order | `POST /payments/create-order` |
| Payment verify | `POST /payments/verify` |

Token expire: **30 din** (phir dubara login)

---

## STEP 11 — SEO & Analytics (Optional but recommended)

**Admin → Business Settings → SEO tab**
- Google Search Console verification code
- GA4 Measurement ID
- Website URL: `https://yourdomain.com`

**Google Search Console:**
1. Property add karo
2. Sitemap submit: `https://yourdomain.com/sitemap.xml`

---

## STEP 12 — Go Live Checklist (Final)

```
Pre-Launch
──────────────────────────────────────────
[ ] APP_DEBUG=false verified
[ ] APP_URL = https://yourdomain.com
[ ] SSL working (green padlock)
[ ] storage:link done — images load ho rahe
[ ] SMTP working — OTP email aa raha
[ ] Razorpay LIVE keys in admin panel
[ ] Webhook secret set + Razorpay dashboard me URL added
[ ] Dummy/test users removed
[ ] Admin password strong
[ ] Cron running (schedule:run)
[ ] Queue worker running
[ ] Database backup scheduled

Functional Tests
──────────────────────────────────────────
[ ] Homepage + room listing load
[ ] Room detail page
[ ] User OTP login (web)
[ ] User OTP login (API/mobile)
[ ] Owner room create + listing payment
[ ] User unlock contact payment
[ ] Subscription purchase
[ ] Admin dashboard + settings save
[ ] 404 page dikhta hai
[ ] /up health check returns OK

Post-Launch
──────────────────────────────────────────
[ ] Google Search Console sitemap submitted
[ ] Uptime monitoring setup (UptimeRobot free)
[ ] Error logs check daily first week
```

---

## Deploy / Update Commands (Jab bhi code update karo)

```bash
cd /var/www/roomrental
git pull                          # ya FTP se files upload
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart         # worker restart
```

---

## Troubleshooting (Common Issues)

| Problem | Solution |
|---------|----------|
| Images nahi dikh rahe | `php artisan storage:link` + folder permissions |
| OTP email nahi aa raha | SMTP settings check + queue worker chalao |
| Payment fail | Razorpay live keys + amount minimum ₹1 |
| Webhook fail | Webhook secret match karo admin + Razorpay dashboard |
| 500 error | `storage/logs/laravel.log` dekho |
| API 401 | Token expire — dubara login |
| Slow website | Redis cache enable karo + indexes (migration already hai) |

---

## Kya Code Me Fix Ho Chuka Hai (Reference)

| Fix | File |
|-----|------|
| OTP API response se hata diya | `ApiAuthController.php` |
| OTP proper email template | `OtpMail` use ho raha hai |
| Payment signature pehle verify | `RazorpayController.php` |
| Payment user/order mismatch check | Web + API payment controllers |
| Webhook secret config | `config/payment.php` + Admin panel field |
| API errors hide in production | `bootstrap/app.php` |
| Sanctum token 30-day expiry | `config/sanctum.php` |
| Rate limit contact/newsletter | `public.php` + `web.php` |
| DB performance indexes | Migration `2026_06_07_000001` |
| Custom 500 error page | `resources/views/errors/500.blade.php` |
| URL override sirf local pe | `AppServiceProvider.php` |

---

**Bas itna karo — server lo, upar ka checklist follow karo, live keys daalo. Site chal jayegi! 🚀**

Questions ho to puch lena.
