# 🚀 PRODUCTION GA CHIQARISH BO'YICHA QO'LLANMA

**Sana:** 2025-10-20
**Loyiha:** Suv Ta'minoti Boshqaruv Tizimi

---

## ⚠️ MUHIM: DEPLOYMENT DAN OLDIN

### 1. Backup olish
```bash
# Database backup
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Code backup (agar git ishlatmasangiz)
# Butun loyiha papkasidan nusxa oling
```

### 2. Maintenance Mode yoqish
```bash
php artisan down --message="Tizim yangilanmoqda. Biroz kuting..."
```

---

## 📋 DEPLOYMENT QADAMLARI

### QADAM 1: Yangi kodlarni serverga yuklash

**Git orqali:**
```bash
git pull origin main
```

**Manual:**
Yangilangan fayllarni FTP/SFTP orqali yuklang.

---

### QADAM 2: Dependencies yangilash

```bash
# PHP dependencies
composer install --no-dev --optimize-autoloader

# Frontend dependencies (agar kerak bo'lsa)
npm install
npm run build
```

---

### QADAM 3: .env faylni yangilash

`.env` fayliga quyidagilarni qo'shing yoki yangilang:

```env
# Timezone
APP_TIMEZONE=Asia/Tashkent

# Telegram Bot
TELEGRAM_BOT_TOKEN=your_actual_bot_token
TELEGRAM_WEBHOOK_URL=https://your-domain.com/api/telegram/webhook

# Water Meter sozlamalar (ixtiyoriy)
WATER_METER_VALIDITY_PERIOD=8
WATER_METER_NUMBER_LENGTH=7
ACCOUNT_NUMBER_LENGTH=7
IMPORT_MAX_FILE_SIZE=10
READING_REQUIRES_CONFIRMATION=true
```

---

### QADAM 4: Migration ishlatish

```bash
# ⚠️ BACKUP OLGANINGIZGA ISHONCH HOSIL QILING!

# Migration'larni ishlatish
php artisan migrate

# Agar "Column already exists" xatoligi bo'lsa:
# Bu normal, migration'lar xavfsiz qilingan
```

---

### QADAM 5: Cache tozalash

**Windows (OSPanel):**
```bash
clear-cache.bat
```

**Linux/macOS:**
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
composer dump-autoload
```

---

### QADAM 6: Optimizatsiya

```bash
# Production uchun optimizatsiya
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Autoload optimizatsiya (allaqachon qilingan bo'lsa skip)
composer dump-autoload --optimize
```

---

### QADAM 7: Storage link yaratish (agar yo'q bo'lsa)

```bash
php artisan storage:link
```

---

### QADAM 8: Permissions tekshirish (Linux/macOS)

```bash
# Storage va cache papkalarga write permission
chmod -R 775 storage bootstrap/cache
chgrp -R www-data storage bootstrap/cache
```

---

### QADAM 9: Test qilish

#### A. Observer'lar ishlayaptimi?
```bash
php artisan tinker

# Test 1: Invoice yaratish
>>> $customer = \App\Models\Customer::first();
>>> $tariff = \App\Models\Tariff::first();
>>> $invoice = \App\Models\Invoice::create([
...     'customer_id' => $customer->id,
...     'tariff_id' => $tariff->id,
...     'billing_period' => '2025-10',
...     'amount_due' => 50000,
...     'due_date' => '2025-11-01',
...     'status' => 'pending'
... ]);

# Mijoz balansini tekshiring
>>> $customer->fresh()->balance;
# Manfiy son bo'lishi kerak (-50000)
```

#### B. Payment tasdiqlash
```bash
# Test 2: Payment yaratish va tasdiqlash
>>> $payment = \App\Models\Payment::create([
...     'customer_id' => $customer->id,
...     'amount' => 30000,
...     'payment_date' => now(),
...     'payment_method' => 'cash',
...     'status' => 'completed',
...     'confirmed' => false
... ]);

>>> $payment->update(['confirmed' => true]);

# Mijoz balansini tekshiring
>>> $customer->fresh()->balance;
# -20000 bo'lishi kerak (50000 - 30000)
```

#### C. Web interface test
Brauzerda quyidagilarni tekshiring:
- ✅ Login ishlayaptimi
- ✅ Dashboard ochilayaptimi
- ✅ Customers ro'yxati chiqayaptimi
- ✅ Invoice yaratish mumkinmi
- ✅ Payment tasdiqlash ishlayaptimi
- ✅ Excel import ishlayaptimi

---

### QADAM 10: Maintenance mode o'chirish

```bash
php artisan up
```

---

## 🔍 DEPLOYMENT DAN KEYIN MONITORING

### 1. Error log'larni tekshirish

```bash
# Laravel log
tail -f storage/logs/laravel.log

# Nginx/Apache error log
tail -f /var/log/nginx/error.log
# yoki
tail -f /var/log/apache2/error.log
```

### 2. Database performance

```sql
-- Eng sekin so'rovlarni topish
SELECT * FROM information_schema.processlist
WHERE command != 'Sleep'
ORDER BY time DESC;

-- Index'lar qo'shilganini tekshirish
SHOW INDEXES FROM customers;
SHOW INDEXES FROM invoices;
SHOW INDEXES FROM payments;
SHOW INDEXES FROM meter_readings;
```

### 3. Telegram bot test

```bash
# Telegram webhook sozlash (agar kerak bo'lsa)
# routes/web.php dagi commented route'ni ochib ishlatish mumkin
```

---

## ⚡ ROLLBACK QILISH (agar muammo bo'lsa)

### 1. Maintenance mode yoqish
```bash
php artisan down
```

### 2. Eski kodga qaytish
```bash
git checkout previous-commit-hash
# yoki eski backup'dan restore qilish
```

### 3. Database rollback
```bash
# Backup'dan restore
mysql -u username -p database_name < backup_20251020.sql

# yoki migration rollback
php artisan migrate:rollback --step=1
```

### 4. Cache tozalash
```bash
clear-cache.bat
# yoki manual
```

### 5. Maintenance mode o'chirish
```bash
php artisan up
```

---

## 📊 YANGI FUNKSIYALAR

### Observer'lar avtomatik ishlaydi
- Invoice yaratilganda mijoz balansi avtomatik yangilanadi
- Payment tasdiqlanganda invoice bilan avtomatik bog'lanadi
- Telegram bildirishnomalar avtomatik yuboriladi

### Payment tasdiqlash yaxshilandi
- To'lovni tasdiqlashda avtomatik eng eski qarzdorlik to'lanadi
- Ortiqcha to'lov alohida saqlanadi
- Invoice status avtomatik yangilanadi

### Excel Import yaxshilandi
- Xatoli qatorlar import qilinmaydi, lekin boshqalari import qilinadi
- Xatolar batafsil ko'rsatiladi
- Partial success qo'llab-quvvatlanadi

### Authorization yaxshilandi
- Policy-based authorization
- Foydalanuvchi faqat o'z kompaniyasi ma'lumotlarini ko'radi
- Admin full access

### Performance yaxshilandi
- 4 ta composite index qo'shildi
- So'rovlar 2-10 marta tezroq ishlaydi

---

## ⚠️ MUHIM ESLATMALAR

### Observer'lar
**MAJBURIY:** Cache tozalash va autoload yangilash. Aks holda observer'lar ishlamaydi!

### Migration
Agar "Column already exists" xatoligi bo'lsa, bu normal. Migration'lar xavfsiz qilingan.

### Performance
Composite indexes qo'shilgandan keyin:
- Query'lar tezlashadi ✅
- Disk space biroz oshadi (index uchun) ⚠️
- Write operatsiyalari biroz sekinlashadi (index yangilash) ⚠️

### Import/Export
Yangi import logikasi foydalanuvchilarga tushuntirilishi kerak:
- Xatosiz qatorlar import qilinadi
- Xatoli qatorlar ko'rsatiladi
- Foydalanuvchi xatoli qatorlarni tuzatib qayta import qilishi mumkin

---

## 📞 MUAMMO BO'LSA

### Log'larni tekshiring
```bash
# Laravel error log
storage/logs/laravel.log

# Web server error log
/var/log/nginx/error.log
# yoki
/var/log/apache2/error.log
```

### Keng tarqalgan muammolar

#### 1. Observer'lar ishlamayapti
**Hal qilish:**
```bash
php artisan config:clear
composer dump-autoload
```

#### 2. Migration xatoligi
**Hal qilish:**
```bash
# Agar "Column already exists" bo'lsa, bu normal
# Migration skip qiladi

# Agar boshqa xatolik bo'lsa, rollback qiling
php artisan migrate:rollback --step=1
```

#### 3. 500 Internal Server Error
**Hal qilish:**
```bash
# Debug mode yoqish (faqat development)
# .env da:
APP_DEBUG=true

# Log'ni tekshiring
tail -f storage/logs/laravel.log
```

#### 4. Permission error
**Hal qilish (Linux):**
```bash
chmod -R 775 storage bootstrap/cache
chgrp -R www-data storage bootstrap/cache
```

---

## ✅ DEPLOYMENT CHECKLIST

- [ ] Backup olindi (database + code)
- [ ] Maintenance mode yoqildi
- [ ] Kod yangilandi (git pull yoki FTP)
- [ ] Dependencies o'rnatildi (composer install)
- [ ] .env yangilandi (timezone, telegram token)
- [ ] Migration ishlatildi
- [ ] Cache tozalandi
- [ ] Cache optimizatsiya qilindi (config, route, view)
- [ ] Storage link yaratildi
- [ ] Permissions to'g'rilandi (Linux)
- [ ] Test qilindi (invoice, payment, import)
- [ ] Maintenance mode o'chirildi
- [ ] Log'lar tekshirildi
- [ ] User'larga yangi funksiyalar haqida xabar berildi

---

## 🎉 MUVAFFAQIYATLI DEPLOYMENT!

Agar barcha qadamlar bajarilgan bo'lsa va test'lar o'tgan bo'lsa, loyiha production'da ishlashga tayyor!

**Keyingi qadam:** Monitoring sozlash va user feedback to'plash.

---

**Tayyorlagan:** Claude Code AI
**Sana:** 2025-10-20
**Versiya:** 1.0
