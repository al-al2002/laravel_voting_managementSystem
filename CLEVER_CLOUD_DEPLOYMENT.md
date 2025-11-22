# Clever Cloud Deployment Guide for Laravel Voting System

## Prerequisites

1. GitHub repository with your code
2. Clever Cloud account (free tier available)
3. Supabase account with your uploads bucket configured

## Step 1: Create a Clever Cloud Account

1. Go to <https://www.clever-cloud.com>
2. Click **Sign Up** or **Try it for free**
3. Sign up using GitHub (recommended) or email
4. Verify your email

## Step 2: Create a PHP Application

1. Log in to [Clever Cloud Console](https://console.clever-cloud.com)
2. Click **Create** → **an application**
3. Select **PHP** application
4. Choose deployment method: **GitHub**
5. Connect your GitHub account if not connected
6. Select repository: `al-al2002/laravel_voting_managementSystem`
7. Select branch: `main`
8. Click **Next**

## Step 3: Configure Application Settings

1. **Application name**: `voting-system` (or your choice)
2. **Region**: Choose closest to you (Paris, Montreal, Singapore, Sydney)
3. **Instance type**: Select **Nano** or **XS** (free tier)
4. Click **Next**

## Step 4: Add MySQL Database (Free Add-on)

1. In the configuration page, click **Add-ons**
2. Click **Add an add-on**
3. Select **MySQL**
4. Choose the **DEV** plan (free)
5. Click **Next** → **Create**
6. The database credentials will be automatically added to your environment variables

## Step 5: Add Environment Variables

1. In your application dashboard, click **Environment variables** (left sidebar)
2. Click **Add Variable** and add each one:

```env
CC_PHP_VERSION=8.2
CC_WEBROOT=/public
CC_POST_BUILD_HOOK=php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache

APP_NAME=VoteMaster
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:GpDgszbGlEvfaEXqT6Mq9r+SAbg9ufZ9Sgzng9gS8os=
APP_URL=https://app-your-id.cleverapps.io

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=quizasiblings@gmail.com
MAIL_PASSWORD=tkxo kzay jmxj uydo
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=quizasiblings@gmail.com
MAIL_FROM_NAME=VoteMaster

SUPABASE_URL=https://bbfycktusiltonrhmrhz.supabase.co
SUPABASE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImJiZnlja3R1c2lsdG9ucmhtcmh6Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjM3OTYxMjQsImV4cCI6MjA3OTM3MjEyNH0.RvFhbwxY9QlVoWYEjjXiOiOt9RPAFdR0Cqr0WZH6McM
SUPABASE_BUCKET=uploads
```

3. **Note**: Database variables (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD) are automatically added from the MySQL add-on

## Step 6: Create composer.json Scripts (Optional)

Clever Cloud uses these for deployment. Your existing `composer.json` should work, but verify it has:

```json
{
  "scripts": {
    "post-install-cmd": [
      "php artisan clear-compiled",
      "php artisan optimize"
    ],
    "post-autoload-dump": [
      "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
      "@php artisan package:discover --ansi"
    ]
  }
}
```

## Step 7: Update APP_URL

1. After deployment starts, Clever Cloud will give you a URL like: `https://app-xxxxx-xxxx.cleverapps.io`
2. Go back to **Environment variables**
3. Update `APP_URL` with your Clever Cloud URL
4. Click **Update**
5. Click **Restart** to apply changes

## Step 8: Deploy

1. Click **Overview** in the sidebar
2. Click **Deploy** button (or it auto-deploys)
3. Watch deployment in **Logs** section
4. Wait 3-5 minutes for first deployment

You'll see:
- Installing dependencies via Composer
- Running post-build hooks (migrations)
- Starting PHP-FPM

## Step 9: Verify Deployment

Once deployment is complete:

1. Click on your app URL
2. Test all features:
   - ✅ Register/Login
   - ✅ Upload profile photo
   - ✅ Create candidates with photos
   - ✅ Send messages with images
   - ✅ Vote and download PDF receipt
   - ✅ Test password reset email

## Configuration Files for Clever Cloud (Optional)

You can create a `.clever.json` file in your project root for more control:

```json
{
  "type": "php",
  "deploy": {
    "php_version": "8.2",
    "webroot": "/public",
    "post_run": "php artisan migrate --force && php artisan config:cache"
  }
}
```

## Troubleshooting

### Deployment fails

- Check **Logs** in Clever Cloud console
- Verify `composer.json` is valid
- Ensure `APP_KEY` is set in environment variables

### Database connection errors

- MySQL add-on credentials are auto-injected
- Check if MySQL add-on is linked to your app
- Go to **Add-ons** → Click MySQL → Verify it's linked

### Images not displaying

- Verify Supabase credentials in environment variables
- Check Supabase bucket is public with RLS policy
- Test Supabase URL directly in browser

### Migration errors

- Check `CC_POST_BUILD_HOOK` is set correctly
- View logs to see migration output
- Ensure database is connected before migrations run

### Email not sending

- Verify Gmail app password is correct
- Check all MAIL_* variables are set
- Test with forgot password feature

## Free Tier Limitations

- **Always On**: No cold starts (unlike Render)
- **Storage**: Limited disk space (use Supabase for files)
- **Database**: 256MB MySQL storage on free tier
- **Bandwidth**: Fair use policy
- **Instances**: 1 instance on free tier

## Useful Clever Cloud Commands

View logs in real-time:

```bash
clever logs
```

Restart application:

```bash
clever restart
```

## Benefits of Clever Cloud

- ✅ No cold starts (always running)
- ✅ Free MySQL database included
- ✅ Auto-deploy on git push
- ✅ European data centers (GDPR compliant)
- ✅ Easy scaling when needed

## Important URLs

- Console: <https://console.clever-cloud.com>
- Documentation: <https://www.clever-cloud.com/doc/>
- Status: <https://www.clever-cloud.com/status/>

Your voting system is now live on Clever Cloud! 🚀
