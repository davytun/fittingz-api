# AUTH
POST   /api/v1/auth/register
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
POST   /api/v1/auth/refresh
POST   /api/v1/auth/change-password
POST   /api/v1/auth/forgot-password
POST   /api/v1/auth/verify-reset-code
POST   /api/v1/auth/reset-password
POST   /api/v1/auth/resend-verification
GET    /api/v1/auth/verify-email/{id}/{hash}

# CLIENTS
POST   /api/v1/clients
GET    /api/v1/clients                                # List all, search, filter
GET    /api/v1/clients/{client}
GET    /api/v1/clients/{client}/profile               # Full profile with summary
PATCH  /api/v1/clients/{client}
DELETE /api/v1/clients/{client}

# MEASUREMENTS
POST   /api/v1/measurements                           # body: {client_id, measurements, unit, ...}
GET    /api/v1/measurements?client_id={client}        # Filter by client
GET    /api/v1/measurements/{measurement}
PATCH  /api/v1/measurements/{measurement}
PATCH  /api/v1/measurements/{measurement}/set-default
DELETE /api/v1/measurements/{measurement}

# ORDERS
POST   /api/v1/orders                                 # body: {client_id, measurement_id, ...}
GET    /api/v1/orders?client_id={client}&status=pending&search=agbada
GET    /api/v1/orders/{order}
PATCH  /api/v1/orders/{order}
PATCH  /api/v1/orders/{order}/status                  # body: {status}
PATCH  /api/v1/orders/{order}/measurement             # body: {measurement_id}
DELETE /api/v1/orders/{order}

# PAYMENTS
POST   /api/v1/payments                               # body: {order_id, amount, payment_date, ...}
GET    /api/v1/payments?order_id={order}              # Filter by order
GET    /api/v1/payments?client_id={client}            # Filter by client (via order relationship)
GET    /api/v1/payments?start_date=2026-01&end_date=2026-03  # Date range
GET    /api/v1/payments/{payment}
PATCH  /api/v1/payments/{payment}
DELETE /api/v1/payments/{payment}

# STYLES
POST   /api/v1/styles                                 # Upload image + details
GET    /api/v1/styles?search=agbada&category=traditional
GET    /api/v1/styles/{style}
PATCH  /api/v1/styles/{style}
DELETE /api/v1/styles/{style}

# LINK STYLES TO ORDERS
POST   /api/v1/orders/{order}/styles                  # body: {style_id}
GET    /api/v1/orders/{order}/styles                  # Get linked styles
DELETE /api/v1/orders/{order}/styles/{style}          # Unlink style

# DASHBOARD/OVERVIEW
GET    /api/v1/dashboard/stats                        # Total clients, orders, revenue, pending payments
GET    /api/v1/dashboard/recent-orders                # Last 10 orders
GET    /api/v1/dashboard/pending-payments             # Orders with outstanding balance
