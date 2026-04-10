# Laravel Cloud Deployment Checklist

This checklist is for deploying this repository to Laravel Cloud after connecting the project through GitHub.

## 1. Application Setup In Laravel Cloud

- [ ] Confirm the correct repository and branch are connected in Laravel Cloud.
- [ ] Open the target environment, such as `production` or `staging`.
- [ ] Set the PHP version to a version compatible with this app.
  Current requirement in `composer.json`: `php ^8.2`
- [ ] Trigger an initial deploy only after the environment variables and resources below are configured.

## 2. Attach Required Resources

- [ ] Attach a MySQL database to the environment.
- [ ] Redeploy after attaching the database so Laravel Cloud injects the database credentials.
- [ ] Decide whether the app needs a cache resource such as Redis or Valkey.
  This is optional for a first deployment, but useful later for cache and queue scaling.
- [ ] Decide whether uploaded files should remain on local disk or move to object storage.
  This app currently defaults to `FILESYSTEM_DISK=local`.

## 3. Configure Environment Variables

Set these in the Laravel Cloud environment settings instead of copying the local `.env` file directly.

### Required

- [ ] `APP_NAME=Fittingz`
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL=https://your-domain`
- [ ] `APP_KEY=base64:...`

### Database

- [ ] Confirm Laravel Cloud injected the database variables after the database resource was attached.
- [ ] If needed, verify `DB_CONNECTION=mysql`.

### Session / Sanctum / Frontend

- [ ] Set `SESSION_DOMAIN` correctly for your production domain.
- [ ] Set `SANCTUM_STATEFUL_DOMAINS` if the frontend runs on a different domain or subdomain.
- [ ] Add any frontend origin or CORS-related variables used by the client application.

### Mail

- [ ] Set `MAIL_MAILER`
- [ ] Set `MAIL_HOST`
- [ ] Set `MAIL_PORT`
- [ ] Set `MAIL_USERNAME`
- [ ] Set `MAIL_PASSWORD`
- [ ] Set `MAIL_ENCRYPTION`
- [ ] Set `MAIL_FROM_ADDRESS`
- [ ] Set `MAIL_FROM_NAME`

### Queue / Cache / Files

- [ ] Confirm `QUEUE_CONNECTION=database` unless you intentionally change it.
- [ ] Confirm `CACHE_STORE=database` unless you intentionally move to Redis or another backend.
- [ ] Keep `FILESYSTEM_DISK=local` for now, or switch to object storage if persistent cloud file storage is required.

## 4. Configure Build And Deploy Commands

These are the recommended starting commands for this repository.

### Build Command

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan optimize
```

### Deploy Command

```bash
php artisan migrate --force
```

### Notes

- [ ] Confirm Node is available in the Laravel Cloud environment because this app uses Vite.
- [ ] Keep `npm run build` in the build step because `package.json` defines a production build.
- [ ] Do not run `php artisan key:generate` in deploy commands for an existing environment.

## 5. Queue And Background Work

- [ ] Enable a worker process if the app sends mail, processes queued jobs, or handles async tasks.
- [ ] Because this app uses `QUEUE_CONNECTION=database`, make sure the database migrations have created the queue tables.
- [ ] Confirm queued jobs are actually processing after deployment.

## 6. Scheduler

- [ ] Enable the Scheduler toggle in the App compute cluster if this app uses Laravel scheduled tasks.
- [ ] Redeploy after enabling the Scheduler.
- [ ] Confirm scheduled jobs run correctly in production.

## 7. Storage And Uploaded Files

- [ ] Review whether local filesystem storage is acceptable for this environment.
- [ ] If user uploads must persist independently of app instances, attach object storage and switch the filesystem disk accordingly.
- [ ] If public file access is needed, confirm storage URLs resolve correctly under the production domain.

## 8. Domain And HTTPS

- [ ] Use the generated `laravel.cloud` domain for first-pass testing.
- [ ] Attach the real custom domain after the first successful deployment.
- [ ] Update `APP_URL` to the final HTTPS domain.
- [ ] Confirm SSL is active and the custom domain resolves correctly.

## 9. First Deployment Verification

- [ ] Open `/up`
- [ ] Open `/api/health`
- [ ] Test registration
- [ ] Test login
- [ ] Test email verification
- [ ] Test password reset
- [ ] Test authenticated API requests
- [ ] Test client creation
- [ ] Test nested measurement, order, and payment flows
- [ ] Test any upload flow if styles or media are uploaded

## 10. After Deployment

- [ ] Review Laravel Cloud deployment logs for build or runtime errors.
- [ ] Review application logs for exceptions.
- [ ] Confirm migrations completed successfully.
- [ ] Confirm mail is sending in production.
- [ ] Confirm queue jobs are not stuck in the `jobs` table.
- [ ] Confirm API responses are using the correct production domain and CORS/session settings.

## 11. Recommended First-Pass Production Values

Use these as a baseline, then adjust to your actual domain and providers.

```dotenv
APP_NAME=Fittingz
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain
DB_CONNECTION=mysql
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=local
```

## 12. Project-Specific Notes

- This app is an API-first Laravel 12 application with routes under `/api/v1`.
- Health checks exist at `/up` and `/api/health`.
- The application uses Sanctum-protected API routes, so production session and stateful domain settings need to be correct.
- The app builds frontend assets with Vite, so Node-based build steps are required during deployment.
