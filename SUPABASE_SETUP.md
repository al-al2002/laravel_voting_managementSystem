# Supabase Storage - Quick Setup Checklist

## ✅ Prerequisites

- [ ] Supabase account created at <https://supabase.com>
- [ ] New Supabase project created
- [ ] Project fully provisioned (wait ~2 minutes)

## ✅ Supabase Configuration

### Step 1: Create Storage Bucket

1. [ ] Navigate to **Storage** in Supabase dashboard
2. [ ] Click **New bucket**
3. [ ] Enter bucket name: `voting-system`
4. [ ] Select **Public bucket** ✓
5. [ ] Click **Create bucket**

### Step 2: Get API Credentials

1. [ ] Go to **Project Settings** (gear icon)
2. [ ] Click **API** tab
3. [ ] Copy **Project URL** (e.g., `https://abcdefgh.supabase.co`)
4. [ ] Copy **anon public** key (long string starting with `eyJ...`)

## ✅ Laravel Configuration

### Step 3: Update Environment Variables

1. [ ] Open your `.env` file (not `.env.example`)
2. [ ] Add these three lines (replace with your actual values):

```env
SUPABASE_URL=https://your-project-id.supabase.co
SUPABASE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
SUPABASE_BUCKET=voting-system
```

1. [ ] Save the file

### Step 4: Clear Cache

Run these commands in your terminal:

```bash
php artisan config:clear
php artisan cache:clear
```

## ✅ Testing

### Test 1: Upload Profile Photo

1. [ ] Log in as a user
2. [ ] Go to Profile → Edit
3. [ ] Upload a new profile photo
4. [ ] Check if photo appears correctly
5. [ ] Verify in Supabase Storage → `profile-photos/` folder

### Test 2: Add Candidate Photo

1. [ ] Log in as admin
2. [ ] Go to Candidates → Add Candidate
3. [ ] Upload a candidate photo
4. [ ] Check if photo appears in candidate list
5. [ ] Verify in Supabase Storage → `candidates/` folder

### Test 3: Download Vote Receipt

1. [ ] Log in as user who has voted
2. [ ] Go to Dashboard
3. [ ] Click "Download Cast" for a completed election
4. [ ] PDF should download
5. [ ] Verify in Supabase Storage → `receipts/` folder

### Test 4: Send Message with Image

1. [ ] Log in as user
2. [ ] Go to Messages
3. [ ] Send a message with an attached image
4. [ ] Check if image displays correctly
5. [ ] Verify in Supabase Storage → `messages/` folder

## ✅ Verification

All files should now be stored in Supabase:

- [ ] Profile photos work ✓
- [ ] Candidate photos work ✓
- [ ] Message images work ✓
- [ ] PDF receipts work ✓

## 🔍 Troubleshooting

If uploads fail:

1. Check `.env` has correct `SUPABASE_URL`, `SUPABASE_KEY`, `SUPABASE_BUCKET`
2. Verify bucket is set to **Public**
3. Clear config: `php artisan config:clear`
4. Check Laravel logs: `storage/logs/laravel.log`
5. Check Supabase dashboard for error messages

If images don't display:

1. Verify bucket is **Public** (not Private)
2. Check browser console for 404 errors
3. Verify URL format: `https://xxx.supabase.co/storage/v1/object/public/voting-system/...`

## 📊 Storage Usage

Monitor your storage in Supabase dashboard:

- **Free tier**: 1 GB storage, 2 GB bandwidth/month
- **View usage**: Project Settings → Usage

## ✨ You're Done

Your voting system is now using Supabase Storage for all file uploads. Files are stored in the cloud and accessible via CDN URLs.

For detailed information, see `SUPABASE_MIGRATION.md`.
