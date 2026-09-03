# Admin Business Settings - Complete Guide

## ✅ Features Implemented

### **1. Business Settings Page in Admin Panel**
- Admin can manage ALL settings from admin panel
- No need to edit `.env` file
- Everything is database-driven

### **2. Settings Categories:**

#### **Business Fees:**
- Listing Fee (₹199 default)
- Featured Fee (₹99 default)
- Unlock Fee (₹49 default)

#### **Commission Settings:**
- Commission Percentage (10% default)
- Service Charge (₹200 default)

#### **Website Appearance:**
- Website Logo (upload image)
- Website Name
- Contact Email
- Contact Phone

#### **Payment Gateway:**
- Razorpay Key ID
- Razorpay Secret

---

## 📁 Files Created

### **Models:**
- ✅ `app/Models/Setting.php` - Settings model with helper methods

### **Controllers:**
- ✅ `app/Http/Controllers/Admin/BusinessSettingsController.php` - Settings management

### **Migrations:**
- ✅ `database/migrations/2025_11_05_170431_create_settings_table.php` - Settings table

### **Seeders:**
- ✅ `database/seeders/SettingsSeeder.php` - Default settings
- ✅ `database/seeders/DummyDataSeeder.php` - Dummy data (users, rooms, plans)

### **Views:**
- ✅ `resources/views/admin/business-settings.blade.php` - Settings page

---

## 🚀 Setup Instructions

### **1. Run Migrations:**
```bash
php artisan migrate
```

### **2. Run Seeders:**
```bash
php artisan db:seed
```

This will:
- Create default settings (fees, commission, etc.)
- Create admin user: `admin@roomrental.com` / `password`
- Create 5 owner users: `owner1@test.com` to `owner5@test.com` / `password`
- Create 10 regular users: `user1@test.com` to `user10@test.com` / `password`
- Create 15 sample rooms (3 per owner)
- Create 3 sample plans

### **3. Access Admin Panel:**
1. Login as admin: `admin@roomrental.com` / `password`
2. Go to Admin Dashboard
3. Click "Business Settings" button
4. Manage all settings from there

---

## ⚙️ How It Works

### **Settings Storage:**
- All settings stored in `settings` table
- Key-value pairs
- Grouped by category (business, appearance, payment)

### **Usage in Code:**
```php
// Get setting value
$listingFee = Setting::get('listing_fee', 199); // Returns value or default

// Set setting value
Setting::set('listing_fee', 250);
```

### **Usage in Views:**
```blade
{{ \App\Models\Setting::get('listing_fee', 199) }}
```

---

## 🎯 Admin Panel Features

### **Business Settings Page:**
1. **Business Fees Section:**
   - Listing Fee input
   - Featured Fee input
   - Unlock Fee input

2. **Commission Settings:**
   - Commission Percentage input
   - Service Charge input

3. **Website Appearance:**
   - Logo upload (with preview)
   - Website Name input
   - Contact Email input
   - Contact Phone input

4. **Payment Gateway:**
   - Razorpay Key ID input
   - Razorpay Secret input (password field)

### **Save Settings:**
- Click "Save Settings" button
- All settings saved to database
- Changes take effect immediately

---

## 📊 Dummy Data Created

### **Users:**
- 1 Admin user
- 5 Owner users
- 10 Regular users

### **Rooms:**
- 15 sample rooms (3 per owner)
- Mix of featured and regular rooms
- Different cities (Mumbai, Delhi, Bangalore, Pune, Hyderabad)
- Different rent ranges (₹5,000 - ₹50,000)

### **Plans:**
- Basic Plan: ₹499 (30 days, 3 listings)
- Pro Plan: ₹999 (90 days, 10 listings)
- Premium Plan: ₹1,999 (180 days, unlimited)

---

## 🔧 Default Settings

After running seeder, default settings are:

| Setting | Value | Description |
|---------|-------|-------------|
| listing_fee | 199 | Room listing fee |
| featured_fee | 99 | Featured room fee |
| unlock_fee | 49 | Contact unlock fee |
| commission_percent | 10 | Commission percentage |
| service_charge | 200 | Service charge |
| website_name | RoomRental | Website name |
| contact_email | support@roomrental.com | Contact email |
| contact_phone | +91 1234567890 | Contact phone |

---

## ✅ All Controllers Updated

All controllers now use `Setting::get()` instead of `config()`:
- ✅ `RoomController.php` - Uses settings for fees
- ✅ `UnlockController.php` - Uses settings for unlock fee
- ✅ `AdminController.php` - Uses settings for display
- ✅ All views updated to use settings

---

## 🎨 Logo Upload

1. Go to Admin → Business Settings
2. Scroll to "Website Appearance" section
3. Click "Choose File" under "Website Logo"
4. Select image (PNG, JPG - Max 2MB)
5. Click "Save Settings"
6. Logo will appear in navigation bar

---

## 📝 Notes

- **No .env changes needed** - Everything managed from admin panel
- **Settings are database-driven** - Easy to update without code changes
- **Logo stored in storage** - Uploaded to `storage/app/public/settings/`
- **All fees configurable** - Admin can change any time
- **Commission settings** - For future booking commission feature

---

## 🚀 Next Steps

1. Run migrations and seeders
2. Login as admin
3. Go to Business Settings
4. Configure all settings
5. Upload website logo
6. Set payment gateway credentials
7. Start using the system!

**Everything is ready!** 🎉

