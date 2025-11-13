# Telegram WebApp (Mini App) Sozlash Qo'llanmasi

## 📱 Telegram WebApp nima?

Telegram WebApp - bu Telegram ichida ochiluvchi to'liq funksional web ilova. Foydalanuvchilar brauzerda saytga kirishning o'rniga, Telegram botini ochib, **Menu** tugmasini bosib, ilovani Telegram ichida ishlatishlari mumkin.

### Afzalliklari:
- ✅ Qulay - brauzerga o'tish kerak emas
- ✅ Tez - Telegram ichida darhol ochiladi
- ✅ Native tajriba - Telegram theme, haptic feedback
- ✅ Mobil optimizatsiya - touch-friendly interfeys
- ✅ Avtomatik autentifikatsiya - kirish kerak emas

---

## 🚀 1-qadam: Telegram Bot Yaratish

### 1.1. BotFather orqali bot yaratish

1. Telegram'da **@BotFather** ni toping va ochng
2. `/newbot` buyrug'ini yuboring
3. Bot nomini kiriting (masalan: **eSuv Admin Panel**)
4. Bot username'ini kiriting (masalan: **esuv_admin_bot**)
   - Username bot bilan tugashi kerak
   - Masalan: `esuv_admin_bot`, `esuv_webapp_bot`

5. BotFather sizga **Bot Token** beradi:
   ```
   1234567890:ABCdefGHIjklMNOpqrsTUVwxyz
   ```
   **Bu tokenni saqlang!** Keyinroq kerak bo'ladi.

### 1.2. Menu Button (WebApp URL) sozlash

1. BotFather'da `/mybots` buyrug'ini yuboring
2. Yaratgan botingizni tanlang (masalan: **eSuv Admin Panel**)
3. **Menu Button** → **Edit Menu Button URL** ni tanlang
4. WebApp URL'ni kiriting:
   ```
   https://your-domain.uz/telegram-webapp
   ```
   **HTTPS majburiy!** Telegram faqat HTTPS URL'larni qabul qiladi.

5. Menu Button Text'ni sozlang (ixtiyoriy):
   - **Menu Button** → **Edit Menu Button Text**
   - Matn: `Admin Panel` yoki `Ochish`

---

## 🔧 2-qadam: Laravel Loyihasini Sozlash

### 2.1. Environment variablelarni sozlash

`.env` faylingizni oching va quyidagilarni qo'shing:

```env
# Notification Bot (mijozlarga bildirishnomalar) - ESKI BOT
TELEGRAM_BOT_TOKEN=your_notification_bot_token

# WebApp Bot (xodimlar uchun admin panel) - YANGI BOT
TELEGRAM_WEBAPP_BOT_TOKEN=1234567890:ABCdefGHIjklMNOpqrsTUVwxyz
TELEGRAM_WEBAPP_URL=https://your-domain.uz
```

**Eslatma:**
- `TELEGRAM_BOT_TOKEN` - eski notification bot (mijozlarga xabar yuborish uchun)
- `TELEGRAM_WEBAPP_BOT_TOKEN` - yangi WebApp bot (admin panel uchun)
- Ikki bot **turli** bo'lishi kerak!

### 2.2. Database migratsiyani ishga tushirish

Telegram foydalanuvchilarni saqlash uchun yangi columnlar qo'shilgan:

```bash
php artisan migrate
```

Bu `users` jadvaliga quyidagi columnlarni qo'shadi:
- `telegram_username` - Telegram username (@username)
- `telegram_user_id` - Telegram user ID (unique)
- `phone` - Telefon raqami

### 2.3. Cache tozalash

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

Yoki:

```bash
clear-cache.bat
```

---

## 👥 3-qadam: Foydalanuvchilarni Sozlash

### 3.1. Xodimlarni tizimga qo'shish

Telegram WebApp faqat **tizimda mavjud** xodimlar uchun ishlaydi. Yangi foydalanuvchilarni avtomatik yaratmaydi (security uchun).

**Admin panel orqali:**

1. Tizimga admin sifatida kiring
2. **Foydalanuvchilar** bo'limiga o'ting
3. Yangi xodimni qo'shing:
   - Ism, email, telefon, va hokazo
   - **Telegram Username** ni kiriting (masalan: `@john_doe`)
   - Yoki **Telegram User ID** ni kiriting (agar bilsangiz)

**Muhim:**
- Xodim avval Telegram'da @BotFather orqali yaratgan botingizni boshlashi kerak (`/start`)
- Keyin tizimga telegram_username yoki telegram_user_id ni kiritishingiz mumkin

### 3.2. Telegram Username va ID'ni qanday topish mumkin?

**Telegram Username:**
- Telegram profilida ko'rinadi (masalan: @john_doe)
- Odamning @username'i

**Telegram User ID:**
- @userinfobot botiga foydalanuvchini forward qiling
- Yoki quyidagi koddan foydalaning (bot'da):

```php
// Telegram bot orqali user ID olish
$telegram = Telegram::bot('webapp');
$update = $telegram->getWebhookUpdate();
$userId = $update->getMessage()->getFrom()->getId();
```

---

## 🧪 4-qadam: Test Qilish

### 4.1. Local testlash (ngrok bilan)

Agar local'da test qilmoqchi bo'lsangiz, HTTPS kerak. Ngrok ishlatishingiz mumkin:

```bash
ngrok http 8000
```

Ngrok sizga HTTPS URL beradi:
```
https://abc123.ngrok.io
```

Keyin BotFather'da Menu Button URL'ni o'zgartiring:
```
https://abc123.ngrok.io/telegram-webapp
```

`.env`'da:
```env
APP_URL=https://abc123.ngrok.io
TELEGRAM_WEBAPP_URL=https://abc123.ngrok.io
```

### 4.2. Production testlash

1. `.env` faylingizda `APP_URL` va `TELEGRAM_WEBAPP_URL` to'g'ri sozlanganligini tekshiring:
   ```env
   APP_URL=https://esuv.uz
   TELEGRAM_WEBAPP_URL=https://esuv.uz
   ```

2. HTTPS ishlayotganligini tekshiring:
   ```bash
   curl -I https://your-domain.uz
   ```

3. Telegram botni toping va `/start` yuboring

4. Pastki qismdagi **Menu** tugmasini bosing

5. Sayt Telegram ichida ochilishi kerak!

---

## 📝 5-qadam: Ishlatish

### 5.1. Xodimlar uchun qo'llanma

1. Telegram'da botni toping (masalan: @esuv_admin_bot)
2. `/start` bosing
3. Pastki qismda **Menu** tugmasi paydo bo'ladi
4. **Menu** tugmasini bosing
5. Admin panel Telegram ichida ochiladi!
6. Avtomatik login bo'lasiz (telegram orqali)
7. Barcha funksiyalardan foydalanishingiz mumkin:
   - Dashboard
   - Mijozlar
   - To'lovlar
   - Fakturalar
   - va boshqalar

### 5.2. Mobile navigation

Telegram WebApp'da **bottom navigation** mavjud:
- 🏠 Bosh sahifa - Dashboard
- 👥 Mijozlar - Mijozlar ro'yxati
- 💰 To'lovlar - To'lovlar
- 📄 Fakturalar - Fakturalar
- 🚪 Chiqish - Telegram'ni yopish

### 5.3. Xususiyatlar

- **Back button** - Telegram'ning native back button ishlaydi
- **Theme** - Telegram theme (dark/light) avtomatik qo'llanadi
- **Haptic feedback** - Tugmalarga bosganda vibratsiya
- **Close confirmation** - Yopishdan oldin tasdiqlash

---

## 🔒 Xavfsizlik

### InitData Validation

Telegram har safar WebApp ochilganda `initData` yuboradi. Laravel middleware uni validate qiladi:

1. **Hash verification** - Telegram'dan kelganligini tekshiradi
2. **User authentication** - Foydalanuvchi tizimda mavjudligini tekshiradi
3. **Auto-login** - Avtomatik login qiladi

### Faqat tizimda mavjud xodimlar

Telegram WebApp **yangi foydalanuvchi yaratmaydi**. Faqat tizimda mavjud xodimlar kirishi mumkin.

Admin avval xodimni yaratib, telegram_username yoki telegram_user_id ni o'rnatishi kerak.

---

## 🐛 Muammolarni Bartaraf Etish

### 1. "Invalid Telegram authentication data"

**Sabab:** Telegram InitData noto'g'ri yoki bot token xato

**Yechim:**
- `.env` da `TELEGRAM_WEBAPP_BOT_TOKEN` to'g'riligini tekshiring
- Cache tozalang: `php artisan config:clear`
- Bot tokenni BotFather'dan qayta oling

### 2. "User not found"

**Sabab:** Xodim tizimda mavjud emas yoki telegram_username xato

**Yechim:**
- Admin panel orqali xodimni qo'shing
- `telegram_username` yoki `telegram_user_id` to'g'ri kiritilganligini tekshiring
- Database'da tekshiring:
  ```sql
  SELECT * FROM users WHERE telegram_username = '@john_doe';
  ```

### 3. WebApp ochilmaydi

**Sabab:** URL xato yoki HTTPS yo'q

**Yechim:**
- BotFather'da Menu Button URL to'g'riligini tekshiring
- HTTPS ishlayotganligini tekshiring
- URL oxiriga `/telegram-webapp` qo'shilganligini tasdiqlang

### 4. Layout xato ko'rsatiladi

**Sabab:** Session'da `is_telegram_webapp` flag yo'q

**Yechim:**
- Cache tozalang: `php artisan view:clear`
- Middleware to'g'ri ishlayotganligini tekshiring
- Session driver'ni tekshiring (`.env`: `SESSION_DRIVER=file`)

### 5. Bottom navigation ko'rinmaydi

**Sabab:** Layout Telegram WebApp layoutini ishlatmayapti

**Yechim:**
- AppServiceProvider'dagi View Composer ishlayotganligini tekshiring
- `telegram-webapp.blade.php` layout mavjudligini tasdiqlang
- Browser cache tozalang

---

## 📊 Statistika va Monitoring

### Log'larni tekshirish

Telegram WebApp authentication log'lari:

```bash
tail -f storage/logs/laravel.log
```

Qidiruv:
```bash
grep "Telegram WebApp" storage/logs/laravel.log
```

### Database'ni tekshirish

Qaysi xodimlar Telegram orqali kirishgan:

```sql
SELECT id, name, email, telegram_username, telegram_user_id
FROM users
WHERE telegram_user_id IS NOT NULL;
```

---

## 🎨 Customization

### Bottom Navigation o'zgartirish

`resources/views/layouts/telegram-webapp.blade.php` faylida:

```html
<div class="webapp-bottom-nav">
    <!-- Yangi item qo'shish -->
    <a href="{{ route('your-route') }}" class="{{ request()->routeIs('your-route') ? 'active' : '' }}">
        <svg>...</svg>
        <span>Your Text</span>
    </a>
</div>
```

### Theme colors

Telegram theme'ni override qilish uchun CSS:

```css
:root {
    --tg-theme-bg-color: #ffffff;
    --tg-theme-text-color: #000000;
    /* va boshqalar */
}
```

### JavaScript events

Telegram WebApp SDK events:

```javascript
// Main button
tg.MainButton.setText('Save');
tg.MainButton.show();
tg.MainButton.onClick(function() {
    // Your code
});

// Back button
tg.BackButton.onClick(function() {
    window.history.back();
});
```

---

## 📚 Qo'shimcha Resurslar

- [Telegram WebApp Documentation](https://core.telegram.org/bots/webapps)
- [Telegram Bot API](https://core.telegram.org/bots/api)
- [Laravel Documentation](https://laravel.com/docs)

---

## ✅ To Do List

Ishga tushirishdan oldin:

- [ ] Telegram bot yaratdingizmi? (BotFather)
- [ ] Bot token oldingizmi?
- [ ] Menu Button URL sozladingizmi?
- [ ] `.env` da tokenni qo'shdingizmi?
- [ ] Migration ishga tushdimi?
- [ ] Cache tozaladingizmi?
- [ ] Xodimlarni qo'shdingizmi?
- [ ] Telegram username'larni kiritdingizmi?
- [ ] HTTPS ishlayaptimi?
- [ ] Test qildingizmi?

---

## 💡 Maslahat

1. **Ikki bot ishlatish** - Notification bot va WebApp bot alohida bo'lishi kerak
2. **HTTPS majburiy** - Telegram faqat HTTPS qabul qiladi
3. **Test qiling** - Local'da ngrok ishlatib test qiling
4. **Log'larni kuzating** - Xatoliklarni topish oson bo'ladi
5. **Session'ni tekshiring** - `is_telegram_webapp` flag to'g'ri o'rnatilganligini tasdiqlang

---

**Muammoga duch kelsangiz, log'larni tekshiring va yuqoridagi troubleshooting bo'limiga qarang!**

Good luck! 🚀
