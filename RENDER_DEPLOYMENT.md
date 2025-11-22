# Render Deployment Guide for Laravel Voting System

## Prerequisites
1. GitHub repository with your code
2. Render account (free tier)
3. Supabase account with your uploads bucket configured

## Environment Variables to Set in Render Dashboard

After creating your web service, go to **Environment** tab and add these:

### Required Variables:
```
APP_NAME=VoteMaster
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com

DB_CONNECTION=mysql
(Database variables will auto-populate when you connect a database)

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

SUPABASE_URL=https://bbfycktusiltonrhmrhz.supabase.co
SUPABASE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImJiZnlja3R1c2lsdG9ucmhtcmh6Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjM3OTYxMjQsImV4cCI6MjA3OTM3MjEyNH0.RvFhbwxY9QlVoWYEjjXiOiOt9RPAFdR0Cqr0WZH6McM
SUPABASE_BUCKET=uploads
```

## Deployment Steps

### 1. Push your code to GitHub
```bash
git add .
git commit -m "Prepare for Render deployment with Supabase"
git push origin main
```

### 2. Create Web Service on Render
1. Go to https://render.com/dashboard
2. Click **New +** → **Web Service**
3. Connect your GitHub repository
4. Configure:
   - **Name**: voting-system (or your choice)
   - **Environment**: Docker
   - **Plan**: Free
   - **Docker Command**: Leave empty (uses Dockerfile CMD)

### 3. Add MySQL Database (Free)
1. In Render Dashboard, click **New +** → **PostgreSQL** (Free tier)
   OR use **PlanetScale** or **Railway** for MySQL
2. Connect it to your web service
3. Database environment variables will auto-populate

### 4. Set Environment Variables
Copy all the variables listed above into Render's Environment tab

### 5. Deploy
- Render will automatically build and deploy
- First deployment takes 5-10 minutes
- Watch the logs for any errors

## Post-Deployment Checklist

✅ **Test Features:**
1. Register/Login
2. Upload profile photo (should save to Supabase)
3. Create candidate with photo (should save to Supabase)
4. Send message with image (should save to Supabase)
5. Download vote receipt PDF (should save to Supabase)
6. Test forgot password email

✅ **Verify Supabase Storage:**
- Check `uploads` bucket for:
  - `profile-photos/`
  - `candidates/`
  - `messages/`
  - `receipts/`

## Troubleshooting

### Images not displaying:
- Verify SUPABASE_URL and SUPABASE_KEY are set correctly
- Check Supabase bucket policy allows public access
- Verify files exist in Supabase Storage dashboard

### PDF downloads failing:
- Check Laravel logs in Render dashboard
- Verify APP_KEY is generated (run `php artisan key:generate`)

### Emails not sending:
- Verify Gmail app password is correct
- Check MAIL_* variables are set

### Database errors:
- Ensure migrations ran successfully
- Check database connection variables
- Look at build logs for migration errors

## Important Notes

- **Free Tier Limitations**: Render free tier spins down after 15 minutes of inactivity
- **First Load**: May take 30-60 seconds to wake up
- **Storage**: All files in Supabase (persistent across deployments)
- **Database**: Use external MySQL/PostgreSQL for persistence
- **Logs**: Check Render dashboard → Logs tab for errors

## Alternative: Using Render.yaml (Automated)

If you want fully automated deployment, the `render.yaml` file is included.
Just push to GitHub and click "Deploy with Blueprint" in Render dashboard.

## Support
If deployment fails, check:
1. Render build logs
2. Laravel logs (via Render dashboard)
3. Supabase dashboard for storage issues
