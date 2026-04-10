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

## Deployment Steps (Shared Hosting / cPanel)
- [ ] Upload files to server (exclude `.git`, `node_modules`, `tests`)
- [ ] **Point Document Root**: In cPanel > Domains, set the path to `api.fittingz.app/public`
- [ ] **Dependencies**: 
    - *If SSH available*: Run `composer install --no-dev --optimize-autoloader`
    - *If No SSH*: Run `composer install --no-dev` locally and upload the `vendor` folder
- [ ] **Environment**: Copy `.env.production` to `.env` on server
- [ ] **Database**:
    - **IMPORTANT: Back up the database before running migrations** (e.g., `mysqldump -u user -p dbname > backup_$(date +%Y%m%d).sql`). Verify the backup completed before proceeding.
    - *If SSH available*: Run `php artisan migrate --force`
    - *If No SSH*: Use the hosting control panel's terminal/SSH, or request SSH access from your host. **Do NOT create a public route in `api.php` that calls `Artisan::call('migrate --force')` — this exposes schema changes over HTTP and must never be used.**
- [ ] **Storage Link**: 
    - *If SSH available*: Run `php artisan storage:link`
    - *If No SSH*: Use the hosting control panel's terminal/SSH, or request SSH access from your host. **Do NOT create a public route that calls `Artisan::call('storage:link')` — use a secure, authenticated endpoint with IP whitelisting if SSH is truly unavailable.**
- [ ] **Optimization**: Run `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
- [ ] **Permissions**: Set `storage` and `bootstrap/cache` to `755` (or `775`) recursively.

## Cron Jobs (cPanel)
- [ ] **Artisan Scheduler**: Add this to cPanel Cron Jobs (Every Minute `* * * * *`):
    `/usr/local/bin/php /home/{cpanel_username}/{app_root_path}/artisan schedule:run >> /dev/null 2>&1`
- [ ] **Alternative Queue (Curl)**: If you can't run a daemon, keep your existing curl-based queue processor.
    `* * * * * curl https://api.fittingz.app/cron/YOUR_SECRET/queue`

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
