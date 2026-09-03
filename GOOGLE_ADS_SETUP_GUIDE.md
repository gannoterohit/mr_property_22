# Google Ads Setup Guide - Production Deployment

## ✅ Code Implementation Complete!

Sab kuch implement ho gaya hai. Ab production pe sirf yeh steps follow karo:

## 📋 Production Deployment Steps

### 1. **Database Settings Seeder Run Karein**
```bash
php artisan db:seed --class=SettingsSeeder
```
Yeh Google Ads ke 4 settings add karega:
- `google_ads_enabled` (default: 0)
- `google_ads_tag_id` (default: empty)
- `google_ads_conversion_label` (default: empty)
- `ga4_measurement_id` (default: empty)

### 2. **`.env` File Me Production Settings**
```env
APP_ENV=production
APP_URL=https://your-domain.com
APP_DEBUG=false
```

### 3. **Admin Panel Me Google Ads Configuration**

1. Login as Admin
2. Go to: **Admin → Business Settings → SEO & Analytics Tab**
3. Google Ads section me:
   - ✅ **Enable Google Ads Tracking** checkbox check karein
   - **Google Ads Tag ID**: `AW-XXXXXXXXX` (Google Ads se mila)
   - **GA4 Measurement ID**: `G-XXXXXXXXXX` (Google Analytics 4 se)
   - **Conversion Label**: `abc123xyz` (Google Ads conversion action se)

4. **Save Changes** button click karein

### 4. **Google Ads Me Conversion Actions Create Karein**

#### A. Booking Conversion:
- Google Ads → Tools → Conversions
- New conversion action create karein
- Type: **Website**
- Category: **Purchase/Sale** ya **Lead**
- Name: "Room Booking"
- Value: Use different values
- Count: Every
- **Conversion Label copy karein** (e.g., `abc123`)
- Admin panel me `google_ads_conversion_label` me daal do

#### C. User Signup Conversion:
- Same steps, name: "User Signup"
- Category: **Sign-up**
- Value: 0
- **Conversion Label copy karein**
- Admin panel me `google_ads_signup_label` me daal do

#### D. Room View Conversion:
- Same steps, name: "Room View"
- Category: **Page view**
- Value: 0
- **Conversion Label copy karein**
- Admin panel me `google_ads_room_view_label` me daal do

#### B. Contact Unlock Conversion:
- Same steps, different name: "Contact Unlock"
- Alag conversion label generate hogi

### 5. **Google Analytics 4 Setup**

1. Google Analytics 4 property create karein
2. **Measurement ID** copy karein (`G-XXXXXXXXXX`)
3. Admin panel me `ga4_measurement_id` me paste karein

### 6. **Test Karein Production Pe**

#### Booking Conversion Test:
1. Test booking karein
2. Payment complete karein
3. Browser DevTools → Network tab me check karein
4. `gtag/js` request dikhni chahiye
5. Console me check karein: `gtag` function available hai ya nahi

#### Conversion Event Test:
1. Payment success ke baad
2. Browser Console me yeh run karein:
```javascript
gtag('event', 'conversion', {
    'send_to': 'AW-XXXXXXX/label',
    'value': 100,
    'currency': 'INR'
});
```

### 7. **Google Ads Me Conversion Verify Karein**

- Google Ads → Conversions
- 24-48 hours me conversions dikhne chahiye
- Agar nahi dikhte, **Tag Assistant** extension use karein

## 🔒 Security & Safety Features

✅ **Localhost/Development Protection**:
- Google Ads script sirf `APP_ENV=production` pe load hota hai
- Localhost pe kabhi load nahi hoga

✅ **Admin Toggle**:
- Admin panel se easily on/off kar sakte ho
- Bina code change ke control possible

✅ **Conditional Loading**:
- Script tabhi load hota hai jab:
  1. Production environment ho
  2. Admin ne enable kiya ho
  3. Tag ID set ho

## 📍 Conversion Tracking Points

Code me yeh conversions automatically track hongi:

1. **Room Booking** (`payment_type: booking`)
   - Amount: Booking amount
   - Currency: INR

2. **Contact Unlock** (`payment_type: unlock`)
   - Amount: Unlock fee
   - Currency: INR

3. **Subscription Purchase** (`payment_type: subscription`)
   - Amount: Plan price
   - Currency: INR

4. **Listing Fee** (`payment_type: listing`)
   - Amount: Listing fee
   - Currency: INR

5. **User Signup**
   - Amount: 0
   - Event fired after successful registration

6. **Room View**
   - Amount: 0
   - Event fired when any room detail page is opened

## 🐛 Troubleshooting

### Problem: Conversions track nahi ho rahi
**Solution**: 
- Check: `APP_ENV=production` hai `.env` me?
- Check: Admin panel me Google Ads enabled hai?
- Check: Tag ID aur Conversion Label sahi hai?
- Check: Browser Console me errors hai?

### Problem: Script load nahi ho raha
**Solution**:
- `.env` me `APP_URL` production URL hai?
- Cache clear karein: `php artisan config:clear`

### Problem: Test conversion track ho rahi hai production pe
**Solution**:
- Google Ads me "Include in conversions" off karein test conversions ke liye
- Production conversions sahi track hongi

## 📞 Support

Agar koi issue ho:
1. Browser Console check karein
2. Network tab me `gtag` requests check karein
3. Google Tag Assistant use karein
4. Google Ads → Tools → Tag Assistant use karein

## ✅ Checklist (Production Pe Deploy Se Pehle)

- [ ] Database seeder run ho gaya
- [ ] `.env` me `APP_ENV=production` set hai
- [ ] `.env` me `APP_URL` production URL hai
- [ ] Admin panel me Google Ads enabled hai
- [ ] Google Ads Tag ID set hai
- [ ] GA4 Measurement ID set hai
- [ ] Conversion Label set hai
- [ ] Test booking/transaction karke conversion verify karein
- [ ] Google Ads me conversion action create ho gaya hai

---

**Note**: Localhost pe Google Ads kabhi load nahi hoga - yeh safety feature hai taaki development me fake conversions track na ho.




