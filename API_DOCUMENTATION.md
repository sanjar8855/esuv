# eSuv Mobile API Documentation

API Base URL: `https://your-domain.uz/api`

## Authentication

All API endpoints (except `/login`) require Bearer token authentication.

### Headers
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your_token}
```

---

## 🔐 Authentication Endpoints

### 1. Login
**POST** `/login`

**Request Body:**
```json
{
  "login": "user@example.com",  // email, phone, or login
  "password": "password123"
}
```

**Response:** (200 OK)
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+998901234567",
    "company_id": 1,
    "company_name": "Toshkent Suv"
  }
}
```

---

### 2. Logout
**POST** `/logout`

**Headers:** Requires authentication

**Response:** (200 OK)
```json
{
  "message": "Chiqib ketildi"
}
```

---

### 3. Get Current User
**GET** `/user`

**Headers:** Requires authentication

**Response:** (200 OK)
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+998901234567",
  "company_id": 1,
  "company_name": "Toshkent Suv",
  "telegram_username": "@johndoe"
}
```

---

## 👥 Customers Endpoints

### 1. Get Customers List
**GET** `/customers`

**Query Parameters:**
- `search` (string, optional) - Search by name, phone, account number, address
- `street_id` (integer, optional) - Filter by street
- `is_active` (boolean, optional) - Filter by active status
- `has_water_meter` (boolean, optional) - Filter by water meter
- `has_debt` (boolean, optional) - Show only customers with debt
- `sort_by` (string, optional, default: created_at) - Sort field
- `sort_order` (string, optional, default: desc) - asc or desc
- `per_page` (integer, optional, default: 20) - Items per page
- `page` (integer, optional, default: 1) - Page number

**Example:** `/customers?search=Ali&has_debt=true&per_page=10`

**Response:** (200 OK)
```json
{
  "data": [
    {
      "id": 1,
      "name": "Ali Valiyev",
      "phone": "+998901234567",
      "address": "12-uy",
      "balance": -50000,
      "account_number": "0000001",
      "has_water_meter": true,
      "family_members": 4,
      "is_active": true,
      "street_name": "Amir Temur ko'chasi",
      "neighborhood_name": "Chilonzor",
      "city_name": "Toshkent"
    }
  ],
  "pagination": {
    "total": 150,
    "per_page": 20,
    "current_page": 1,
    "last_page": 8,
    "from": 1,
    "to": 20
  }
}
```

---

### 2. Get Single Customer
**GET** `/customers/{id}`

**Response:** (200 OK)
```json
{
  "id": 1,
  "name": "Ali Valiyev",
  "phone": "+998901234567",
  "address": "12-uy",
  "balance": -50000,
  "account_number": "0000001",
  "has_water_meter": true,
  "family_members": 4,
  "is_active": true,
  "pdf_file": "storage/customer_pdfs/file.pdf",
  "street_id": 5,
  "street_name": "Amir Temur ko'chasi",
  "neighborhood_name": "Chilonzor",
  "city_name": "Toshkent",
  "region_name": "Toshkent",
  "water_meter": {
    "id": 1,
    "meter_number": "1234567",
    "installation_date": "2020-01-01",
    "expiration_date": "2028-01-01",
    "last_reading_date": "2025-11-10"
  },
  "recent_invoices": [...],
  "recent_payments": [...]
}
```

---

### 3. Create Customer
**POST** `/customers`

**Request Body:**
```json
{
  "name": "Ali Valiyev",
  "phone": "+998901234567",
  "address": "12-uy",
  "street_id": 5,
  "has_water_meter": true,
  "family_members": 4
}
```

**Response:** (201 Created)
```json
{
  "message": "Mijoz muvaffaqiyatli yaratildi",
  "customer": {
    "id": 1,
    "name": "Ali Valiyev",
    "phone": "+998901234567",
    "account_number": "0000001",
    "balance": 0
  }
}
```

---

### 4. Update Customer
**PUT** `/customers/{id}`

**Request Body:** (all fields optional)
```json
{
  "name": "Ali Valiyev",
  "phone": "+998901234567",
  "address": "12-uy",
  "street_id": 5,
  "has_water_meter": true,
  "family_members": 4,
  "is_active": true
}
```

**Response:** (200 OK)
```json
{
  "message": "Mijoz muvaffaqiyatli yangilandi",
  "customer": {...}
}
```

---

### 5. Delete Customer
**DELETE** `/customers/{id}`

**Response:** (200 OK)
```json
{
  "message": "Mijoz muvaffaqiyatli o'chirildi"
}
```

**Error Response:** (400 Bad Request) - if customer has debt
```json
{
  "message": "Mijozni o'chirish mumkin emas. Qarz mavjud."
}
```

---

## 💰 Payments Endpoints

### 1. Get Payments List
**GET** `/payments`

**Query Parameters:**
- `from_date` (date, optional) - Filter from date
- `to_date` (date, optional) - Filter to date
- `customer_id` (integer, optional) - Filter by customer
- `payment_method` (string, optional) - cash, card, transfer, online
- `status` (string, optional) - pending, completed, failed
- `confirmed` (boolean, optional) - Filter by confirmed status
- `today` (boolean, optional) - Show only today's payments
- `per_page` (integer, optional, default: 20)

**Response:** (200 OK)
```json
{
  "data": [
    {
      "id": 1,
      "customer_id": 1,
      "customer_name": "Ali Valiyev",
      "amount": 50000,
      "payment_date": "2025-11-15",
      "payment_method": "cash",
      "status": "completed",
      "confirmed": true,
      "confirmed_by": 2,
      "confirmed_at": "2025-11-15 10:30:00",
      "invoice_id": 5
    }
  ],
  "pagination": {...}
}
```

---

### 2. Get Single Payment
**GET** `/payments/{id}`

---

### 3. Create Payment
**POST** `/payments`

**Request Body:**
```json
{
  "customer_id": 1,
  "amount": 50000,
  "payment_method": "cash",
  "payment_date": "2025-11-15"  // optional, defaults to now
}
```

**Response:** (201 Created)
```json
{
  "message": "To'lov muvaffaqiyatli qo'shildi",
  "payment": {
    "id": 1,
    "amount": 50000,
    "payment_date": "2025-11-15",
    "status": "pending"
  }
}
```

---

### 4. Confirm Payment
**PATCH** `/payments/{id}/confirm`

**Response:** (200 OK)
```json
{
  "message": "To'lov tasdiqlandi",
  "payment": {
    "id": 1,
    "confirmed": true,
    "confirmed_at": "2025-11-15 10:30:00"
  }
}
```

---

## 📄 Invoices Endpoints

### 1. Get Invoices List
**GET** `/invoices`

**Query Parameters:**
- `customer_id` (integer, optional)
- `status` (string, optional) - pending, paid, overdue
- `from_date` (date, optional)
- `per_page` (integer, optional, default: 20)

---

### 2. Get Single Invoice
**GET** `/invoices/{id}`

---

### 3. Create Invoice
**POST** `/invoices`

**Request Body:**
```json
{
  "customer_id": 1,
  "tariff_id": 1,
  "amount_due": 75000,
  "billing_period": "2025-11",
  "due_date": "2025-11-30"
}
```

---

## 💧 Water Meters Endpoints

### 1. Get Water Meters List
**GET** `/water-meters`

**Query Parameters:**
- `customer_id` (integer, optional)
- `expiring_soon` (boolean, optional) - Meters expiring in 3 months
- `per_page` (integer, optional)

**Response:** (200 OK)
```json
{
  "data": [
    {
      "id": 1,
      "customer_id": 1,
      "customer_name": "Ali Valiyev",
      "meter_number": "1234567",
      "installation_date": "2020-01-01",
      "expiration_date": "2028-01-01",
      "last_reading_date": "2025-11-10"
    }
  ],
  "pagination": {...}
}
```

---

### 2. Get Single Water Meter
**GET** `/water-meters/{id}`

**Response includes recent readings**

---

### 3. Create Water Meter
**POST** `/water-meters`

**Request Body:**
```json
{
  "customer_id": 1,
  "meter_number": "1234567",
  "installation_date": "2025-11-15"
}
```

---

## 📊 Meter Readings Endpoints

### 1. Get Meter Readings List
**GET** `/meter-readings`

**Query Parameters:**
- `water_meter_id` (integer, optional)
- `confirmed` (boolean, optional)
- `per_page` (integer, optional)

---

### 2. Get Single Reading
**GET** `/meter-readings/{id}`

---

### 3. Submit Meter Reading
**POST** `/meter-readings`

**Request Body:** (multipart/form-data for photo)
```
water_meter_id: 1
reading: 150
reading_date: 2025-11-15  (optional)
photo: [file]  (optional, max 5MB)
```

**Response:** (201 Created)
```json
{
  "message": "Ko'rsatkich qo'shildi",
  "reading": {
    "id": 1,
    "reading": 150,
    "reading_date": "2025-11-15",
    "photo_url": "https://esuv.uz/storage/meter_readings/photo.jpg"
  }
}
```

---

### 4. Confirm Reading
**PATCH** `/meter-readings/{id}/confirm`

---

## 🗺️ Master Data Endpoints

### 1. Get Regions
**GET** `/regions`

**Response:**
```json
{
  "data": [
    {"id": 1, "name": "Toshkent"},
    {"id": 2, "name": "Samarqand"}
  ]
}
```

---

### 2. Get Cities
**GET** `/cities?region_id=1`

---

### 3. Get Neighborhoods
**GET** `/neighborhoods?city_id=1`

---

### 4. Get Streets
**GET** `/streets?neighborhood_id=1`

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Amir Temur ko'chasi",
      "neighborhood_id": 1,
      "neighborhood_name": "Chilonzor",
      "city_name": "Toshkent",
      "region_name": "Toshkent"
    }
  ]
}
```

---

### 5. Get Tariffs
**GET** `/tariffs`

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Asosiy tarif",
      "price_per_m3": 1500,
      "for_one_person": 5000,
      "valid_from": "2025-01-01",
      "valid_to": null,
      "is_active": true
    }
  ]
}
```

---

## 📈 Reports Endpoints

### 1. Daily Payments Report
**GET** `/reports/daily-payments?date=2025-11-15`

**Response:**
```json
{
  "date": "2025-11-15",
  "total_amount": 500000,
  "total_count": 45,
  "by_payment_method": [
    {"method": "cash", "count": 30, "amount": 300000},
    {"method": "card", "count": 15, "amount": 200000}
  ],
  "payments": [...]
}
```

---

### 2. Customer Debts Report
**GET** `/reports/customer-debts`

**Response:**
```json
{
  "total_debt": 5000000,
  "total_customers": 120,
  "customers": [
    {
      "id": 1,
      "name": "Ali Valiyev",
      "phone": "+998901234567",
      "account_number": "0000001",
      "balance": -50000,
      "debt": 50000
    }
  ]
}
```

---

### 3. Dashboard Statistics
**GET** `/reports/dashboard-stats`

**Response:**
```json
{
  "customers": {
    "total": 1200,
    "active": 1150,
    "with_debt": 120
  },
  "payments": {
    "today_count": 45,
    "today_sum": 500000,
    "pending": 5
  },
  "debts": {
    "total_debt": 5000000,
    "customers_count": 120
  },
  "invoices": {
    "unpaid": 80
  },
  "current_tariff": {
    "name": "Asosiy tarif",
    "price_per_m3": 1500,
    "for_one_person": 5000
  },
  "recent_payments": [
    {
      "customer_name": "Ali Valiyev",
      "amount": 50000,
      "payment_date": "2025-11-15"
    }
  ]
}
```

---

## 📁 File Upload Endpoint

### Upload File
**POST** `/upload`

**Request Body:** (multipart/form-data)
```
file: [file]  (max 10MB)
type: meter_photo|customer_pdf|invoice_pdf|profile_photo
```

**Response:** (201 Created)
```json
{
  "message": "Fayl yuklandi",
  "file": {
    "path": "storage/meter_readings/photo.jpg",
    "url": "https://esuv.uz/storage/meter_readings/photo.jpg",
    "type": "meter_photo",
    "size": 1024000,
    "original_name": "photo.jpg"
  }
}
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 422 Validation Error
```json
{
  "message": "Validation error",
  "errors": {
    "name": ["Name field is required"],
    "phone": ["Phone must be a valid number"]
  }
}
```

### 404 Not Found
```json
{
  "message": "Resource not found"
}
```

### 500 Server Error
```json
{
  "message": "Server error"
}
```

---

## Rate Limiting

- **Limit:** 60 requests per minute per IP
- **Header:** `X-RateLimit-Limit`, `X-RateLimit-Remaining`

---

## Notes

1. All dates are in `Y-m-d` format (2025-11-15)
2. All datetimes are in `Y-m-d H:i:s` format (2025-11-15 10:30:00)
3. Amounts are in UZS (integers, no decimals)
4. Phone numbers should include country code (+998)
5. Images are automatically compressed to 85% quality and resized to max 1920px
6. All endpoints return JSON
7. HTTPS is required in production

---

## Testing with Postman/Insomnia

1. Set base URL: `https://your-domain.uz/api`
2. Add headers:
   - `Content-Type: application/json`
   - `Accept: application/json`
3. Login and get token
4. Add token to all requests:
   - `Authorization: Bearer {your_token}`

---

**Last Updated:** 2025-11-15
