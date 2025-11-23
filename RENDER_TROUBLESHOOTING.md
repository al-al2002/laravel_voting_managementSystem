# Render Deployment Troubleshooting Guide

## Quick Diagnostics

### 1. Test Health Endpoint

First, check if the app is running:

`https://your-app-name.onrender.com/health`

Expected Response:

```json
{
  "status": "OK",
  "timestamp": "2025-11-23T...",
  "environment": "production"
}
```

### 2. Check Render Logs

In Render Dashboard → Your Service → Logs tab, look for:

Successful Deployment:

```text
=== Laravel Voting System Startup ===
Running migrations...
Migration table created successfully.
Migrated: 2025_09_08_133923_add_custom_fields_to_users_table
...
Seeding admin...
Creating storage link...
=== Starting Apache on port 10000 ===
[core:notice] [pid 1] AH00094: Command line: 'apache2 -D FOREGROUND'
```

Common Errors:

#### Error 1: "404 Not Found" on all routes

**Cause:** Apache not serving from `/public` or `.htaccess` not working

**Fix in Render Shell:**

```bash
cat /etc/apache2/sites-available/000-default.conf | grep DocumentRoot
```

Should show: DocumentRoot /var/www/html/public

#### Error 2: "Admin dashboard not found" after login

**Cause:** Session not persisting or routes not accessible

**Fix:** Check environment variables in Render:

- `SESSION_DRIVER=file` (NOT database)
- `APP_URL=https://your-actual-render-url.onrender.com`

#### Error 3: "SQLSTATE Connection refused"

**Cause:** Database credentials incorrect

**Fix:** Verify Clever Cloud database credentials in Render Shell:

```bash
php artisan tinker
DB::connection()->getPdo();
```

Should connect successfully

#### Error 4: "Broken UI / No CSS"

**Cause:** Vite assets not built

**Fix:** Check build logs for:

```text
> npm install
> npm run build
✓ built in 15s
```

If missing, the Dockerfile didn't run npm commands.

---

## Manual Fixes (Render Shell)

Access the shell: Render Dashboard → Your Service → Shell tab

### Clear All Caches

```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize:clear
```

### Rebuild Caches

```bash
php artisan config:cache
php artisan view:cache
```

### Check Routes

```bash
php artisan route:list | grep admin
```

Should show:

```text
GET|HEAD  admin/dashboard ... admin.dashboard
```

### Test Admin Login Manually

```bash
php artisan tinker
```

Then run:

```php
$admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
if($admin) {
    echo "Admin exists: " . $admin->name;
} else {
    echo "No admin found - need to seed!";
}
exit
```

### Reseed Admin (if missing)

```bash
php artisan db:seed --class=AdminSeeder --force
```

---

## Environment Variables Checklist

In Render Dashboard → Your Service → Environment tab:

### Critical Variables

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY=base64:...` (Your actual app key)
- `APP_URL=https://your-app.onrender.com` (Your actual Render URL)
- `SESSION_DRIVER=file` (MUST be 'file', NOT 'database')

### Database (Clever Cloud)

- `DB_CONNECTION=mysql`
- `DB_HOST=xxxxx.mysql.services.clever-cloud.com`
- `DB_PORT=3306`
- `DB_DATABASE=xxxxx`
- `DB_USERNAME=xxxxx`
- `DB_PASSWORD=xxxxx`

### Mail (Gmail)

- `MAIL_MAILER=smtp`
- `MAIL_HOST=smtp.gmail.com`
- `MAIL_PORT=587`
- `MAIL_USERNAME=quizasiblings@gmail.com`
- `MAIL_PASSWORD=utkvflyxocqvwkcp`
- `MAIL_ENCRYPTION=tls`

### Supabase Storage

- `SUPABASE_URL=https://bbfycktusiltonrhmrhz.supabase.co`
- `SUPABASE_KEY=eyJhbGc...`
- `SUPABASE_BUCKET=uploads`

---

## Step-by-Step Login Test

1. Open your Render URL in incognito mode
2. Check login page loads with styling
3. Open browser DevTools (F12) → Network tab
4. Login with: `admin@gmail.com` / `admin123`
5. Watch the network request to `/login`
6. Expected: Redirect to `/admin/dashboard` (Status 302, then 200)
7. If 404: Admin routes not accessible

### Debug Login Flow

Check Render logs during login attempt:

```text
IsAdmin check
Admin access denied - redirecting to login
```

If you see this AFTER successful login, the session isn't persisting.

**Solution:**

1. Ensure `SESSION_DRIVER=file` in environment variables
2. Redeploy the service
3. Try again

---

## Common Issues and Solutions

### Issue: "Page keeps redirecting to login"

**Cause:** Session not saving

**Solution:**

1. Set `SESSION_DRIVER=file`
2. Check permissions: `chmod 775 /var/www/html/storage/framework/sessions`

### Issue: "Admin dashboard shows 404"

**Cause:** Routes not loaded or cached incorrectly

**Solution:**

```bash
php artisan route:clear
php artisan config:clear
```

### Issue: "Database migration errors"

**Cause:** Database already has tables

**Solution:** This is normal if redeploying. Check logs for "already exists" - can be ignored.

### Issue: "Class not found" errors

**Cause:** Composer autoload not optimized

**Solution:**

```bash
composer dump-autoload --optimize
```

---

## Force Redeploy

If all else fails:

1. Render Dashboard → Your Service
2. Click "Manual Deploy" dropdown
3. Select "Clear build cache & deploy"
4. Wait for fresh deployment

---

## Success Indicators

Your deployment is working when:

1. `/health` returns `{"status":"OK"}`
2. Login page has proper styling (Tailwind CSS loaded)
3. Admin login redirects to `/admin/dashboard`
4. Dashboard shows navigation and content
5. No errors in browser console (F12)
6. Render logs show Apache running on port 10000

---

## Still Having Issues?

Check these files in the repository:

- `Dockerfile` - Build configuration
- `apache-laravel.conf` - Apache virtual host
- `routes/web.php` - Route definitions
- `.env.example` - Environment variable template

Make sure your environment variables in Render match the structure in `.env.example`.
