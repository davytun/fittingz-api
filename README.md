# Fittingz API

REST API for fashion designers and tailoring businesses to manage clients, measurements, styles, and orders.

## Tech Stack

- Laravel 12
- PHP 8.2
- MySQL
- Laravel Sanctum (Authentication)

## Features

### Authentication
- User registration with email verification
- Login with account lockout (5 failed attempts = 30min lock)
- Password reset with 4-digit code
- Token-based auth (7-day expiration)
- Rate limiting on all auth endpoints

### Core Modules (Coming Soon)
- Client Management
- Measurement Tracking
- Style Inspirations
- Order Management

## Installation

### Requirements
- PHP 8.2+
- Composer
- MySQL
- Node.js (for queue processing in production)

### Setup

1. Clone repository
```bash
git clone <repo-url>
cd fittingz
```

2. Install dependencies
```bash
composer install
```

3. Environment setup
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure `.env`
```env
DB_DATABASE=fittingz
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@fittingz.com

CRON_SECRET=your-random-secret-key
```

5. Run migrations
```bash
php artisan migrate
php artisan storage:link
```

6. Start development server
```bash
php artisan serve
```

7. Start queue worker (separate terminal)
```bash
php artisan queue:work
```

## Production Deployment

### Shared Hosting Setup

1. Upload files via FTP/cPanel File Manager

2. Set document root to `/public`

3. Create database and update `.env`

4. Run migrations via SSH or cPanel Terminal
```bash
php artisan migrate --force
```

5. Set permissions
```bash
chmod -R 755 storage bootstrap/cache
```

6. Setup cron job in cPanel (every minute)
```bash
* * * * * curl https://yourdomain.com/cron/YOUR_CRON_SECRET/queue >/dev/null 2>&1
```

7. Clear caches
```bash
php artisan config:cache
php artisan route:cache
```

## API Documentation

Base URL: `/api/v1`

### Authentication Endpoints

#### Register
```http
POST /auth/register
Content-Type: application/json

{
  "email": "designer@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "business_name": "Threads & Co",
  "contact_phone": "08012345678",
  "business_address": "123 Fashion Street, Lagos"
}
```

#### Login
```http
POST /auth/login
Content-Type: application/json

{
  "email": "designer@example.com",
  "password": "password123"
}
```

#### Logout
```http
POST /auth/logout
Authorization: Bearer {token}
```

#### Forgot Password
```http
POST /auth/forgot-password
Content-Type: application/json

{
  "email": "designer@example.com"
}
```

#### Reset Password
```http
POST /auth/reset-password
Content-Type: application/json

{
  "email": "designer@example.com",
  "token": "1234",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

## Security Features

- Email verification required
- Account lockout after 5 failed login attempts (30min)
- Rate limiting on all auth endpoints
- Token expiration (7 days)
- Password reset with 4-digit code (60min expiration)
- Secure password hashing (bcrypt)

## License

Proprietary