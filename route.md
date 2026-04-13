# HEALTH
GET    /up
GET    /api/health

# AUTH
POST   /api/v1/auth/register
POST   /api/v1/auth/login
POST   /api/v1/auth/verify-email
POST   /api/v1/auth/resend-verification
POST   /api/v1/auth/forgot-password
POST   /api/v1/auth/verify-reset-code
POST   /api/v1/auth/reset-password
POST   /api/v1/auth/logout
POST   /api/v1/auth/refresh
POST   /api/v1/auth/change-password

# CLIENTS
GET    /api/v1/clients                                         # List clients, search by name/email/phone, filter by gender
POST   /api/v1/clients                                         # body: {name, email?, phone?, gender?}
GET    /api/v1/clients/{client}
PATCH  /api/v1/clients/{client}                                # body: {name?, email?, phone?, gender?}
DELETE /api/v1/clients/{client}
GET    /api/v1/clients/{client}/profile                        # Profile summary with measurements and orders

# CLIENT MEASUREMENTS
GET    /api/v1/clients/{client}/measurements
POST   /api/v1/clients/{client}/measurements                   # body: {name, fields, unit, measurement_date, notes?, is_default?}
GET    /api/v1/clients/{client}/measurements/{measurement}
PATCH  /api/v1/clients/{client}/measurements/{measurement}     # body: {name?, fields?, unit?, measurement_date?, notes?, is_default?}
PATCH  /api/v1/clients/{client}/measurements/{measurement}/set-default
DELETE /api/v1/clients/{client}/measurements/{measurement}

# CLIENT ORDERS
GET    /api/v1/clients/{client}/orders?status=pending_payment&search=agbada&include=measurement,styleImages
POST   /api/v1/clients/{client}/orders                         # body: {measurement_id?, details?, style_description?, total_amount, currency?, status?, due_date?, notes?, deposit?}
GET    /api/v1/clients/{client}/orders/{order}
PATCH  /api/v1/clients/{client}/orders/{order}                 # body: {details?, style_description?, total_amount?, currency?, due_date?, notes?}
PATCH  /api/v1/clients/{client}/orders/{order}/status          # body: {status}
PATCH  /api/v1/clients/{client}/orders/{order}/measurement     # body: {measurement_id}
DELETE /api/v1/clients/{client}/orders/{order}

# ORDER PAYMENTS
GET    /api/v1/clients/{client}/orders/{order}/payments
POST   /api/v1/clients/{client}/orders/{order}/payments        # body: {amount, payment_date, payment_method, reference?, notes?}
GET    /api/v1/clients/{client}/orders/{order}/payments/{payment}
DELETE /api/v1/clients/{client}/orders/{order}/payments/{payment}

# ORDER STYLES
POST   /api/v1/clients/{client}/orders/{order}/styles          # body: {style_id}
DELETE /api/v1/clients/{client}/orders/{order}/styles/{style}

# STYLES
GET    /api/v1/styles?search=agbada&category=traditional
POST   /api/v1/styles                                          # Upload image + details
GET    /api/v1/styles/{style}
PATCH  /api/v1/styles/{style}
DELETE /api/v1/styles/{style}

# PROFILE
GET    /api/v1/profile
PATCH  /api/v1/profile                                         # body: {business_name?, contact_phone?, business_address?, email?, email_notifications?}

# DASHBOARD
GET    /api/v1/dashboard/stats
GET    /api/v1/dashboard/recent-orders?limit=10
GET    /api/v1/dashboard/pending-payments
GET    /api/v1/dashboard/upcoming-deliveries?days=7
GET    /api/v1/dashboard/overdue-orders
GET    /api/v1/dashboard/revenue-analytics?period=month        # period: month|year
GET    /api/v1/dashboard/top-clients?limit=10

# STATUS / PAYLOAD NOTES
- Order status values: pending_payment, in_progress, completed, delivered, cancelled
- Measurement payloads use `fields` instead of `measurements`
- Order payloads use `details`, `style_description`, and `currency`; `title` and `quantity` are no longer part of the order schema
- Creating an order with `deposit` records the initial payment automatically
