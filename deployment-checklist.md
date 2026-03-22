# deployment-checklist.md

## Pre-Deployment
- [ ] Run all tests locally
- [ ] Update .env.production with correct values
- [ ] Set APP_DEBUG=false
- [ ] Set APP_ENV=production
- [ ] Configure production database credentials
- [ ] Configure production mail provider (SMTP)
- [ ] Generate strong APP_KEY
- [ ] Generate strong CRON_SECRET
- [ ] Review all API endpoints in Postman

## Deployment Steps
- [ ] Upload files to server (exclude .git, node_modules, tests)
- [ ] Set document root to /public
- [ ] Run: composer install --no-dev --optimize-autoloader
- [ ] Copy .env.production to .env
- [ ] Run: php artisan key:generate
- [ ] Run: php artisan migrate --force
- [ ] Run: php artisan storage:link
- [ ] Run: php artisan config:cache
- [ ] Run: php artisan route:cache
- [ ] Run: php artisan optimize
- [ ] Set permissions: chmod -R 755 storage bootstrap/cache

## Cron Jobs (cPanel)
- [ ] Add queue processor: * * * * * curl https://yourdomain.com/cron/YOUR_SECRET/queue
- [ ] Add backup job: 0 2 * * * cd /path/to/app && php artisan backup:database

## Post-Deployment
- [ ] Test health check: GET /health
- [ ] Test registration flow
- [ ] Test login flow
- [ ] Test password reset
- [ ] Create test client, measurement, order, payment
- [ ] Upload test style image
- [ ] Check logs: storage/logs/
- [ ] Monitor error rates
- [ ] Setup uptime monitoring (UptimeRobot, Pingdom)

## Email Configuration
- [ ] Test email sending (registration, password reset)
- [ ] Verify SPF/DKIM records
- [ ] Check spam folder

## Security
- [ ] Verify HTTPS is enforced
- [ ] Test rate limiting
- [ ] Test account lockout
- [ ] Check security headers
- [ ] Review logs for suspicious activity

## Performance
- [ ] Test API response times
- [ ] Verify database indexes are working
- [ ] Check image optimization
- [ ] Monitor memory usage
- [ ] Check queue processing speed
