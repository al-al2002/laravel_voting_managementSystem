# Supabase Storage Migration Guide

This Laravel voting system has been migrated from local storage to Supabase Storage for file management.

## What Changed

### Files Migrated to Supabase

1. **Candidate Photos** - Stored in `candidates/` folder
2. **Profile Photos** - Stored in `profile-photos/` folder
3. **Message Images** - Stored in `messages/` folder
4. **Vote Receipt PDFs** - Stored in `receipts/` folder

### New Files Created

1. **`app/Storage/SupabaseStorageAdapter.php`**
   - Custom Flysystem adapter for Supabase Storage
   - Handles file operations (upload, download, delete, etc.)
   - Generates public URLs for files

2. **`app/Providers/SupabaseStorageServiceProvider.php`**
   - Registers the Supabase storage driver with Laravel
   - Automatically loaded via `bootstrap/providers.php`

### Modified Files

#### Configuration

- **`config/filesystems.php`** - Added `supabase` disk configuration
- **`.env.example`** - Added Supabase environment variables
- **`bootstrap/providers.php`** - Registered SupabaseStorageServiceProvider

#### Controllers

- **`app/Http/Controllers/Admin/CandidateController.php`** - Changed from `public` to `supabase` disk
- **`app/Http/Controllers/User/ProfileController.php`** - Changed from `public` to `supabase` disk
- **`app/Http/Controllers/Admin/SmsController.php`** - Changed from `public` to `supabase` disk
- **`app/Http/Controllers/User/VoteController.php`** - PDFs now stored in Supabase

#### Models

- **`app/Models/User.php`** - Added `profile_photo_url` accessor
- **`app/Models/Candidate.php`** - Added `photo_url` accessor
- **`app/Models/Message.php`** - Changed to `image_urls` accessor (array)

#### Views (All Updated)

- All Blade templates now use model accessors instead of `asset('storage/...')`
- User views: dashboard, profile, elections, results, live monitor, messages
- Admin views: voters, candidates, results, live monitor, SMS inbox/conversation

## Setup Instructions

### 1. Create Supabase Project

1. Go to [https://supabase.com](https://supabase.com)
2. Create a new project
3. Wait for the project to be provisioned

### 2. Create Storage Bucket

1. In your Supabase dashboard, go to **Storage**
2. Click **New bucket**
3. Name it `voting-system` (or your preferred name)
4. Make it **Public** bucket for easy URL access
5. Configure CORS if needed

### 3. Get API Credentials

1. Go to **Project Settings** → **API**
2. Copy your **Project URL** (e.g., `https://xxxxx.supabase.co`)
3. Copy your **anon/public key**

### 4. Configure Environment Variables

Add these to your `.env` file:

```env
SUPABASE_URL=https://your-project-id.supabase.co
SUPABASE_KEY=your-anon-key-here
SUPABASE_BUCKET=voting-system
```

### 5. Test the Integration

1. Clear configuration cache: `php artisan config:clear`
2. Try uploading a profile photo
3. Try adding a candidate with a photo
4. Download a vote receipt PDF
5. Send a message with an image

## Supabase Storage Structure

```text
voting-system/
├── candidates/
│   ├── ABC123.jpg
│   ├── DEF456.png
│   └── ...
├── profile-photos/
│   ├── XYZ789.jpg
│   └── ...
├── messages/
│   ├── MSG001.png
│   └── ...
└── receipts/
    ├── Vote_Receipt_1_Election_5.pdf
    └── ...
```

## Public URL Format

Files are accessible via public URLs:

```text
https://your-project.supabase.co/storage/v1/object/public/voting-system/candidates/ABC123.jpg
```

The model accessors automatically generate these URLs:

- `$user->profile_photo_url`
- `$candidate->photo_url`
- `$message->image_urls` (array of URLs)

## Migration from Local Storage (Optional)

If you have existing files in `storage/app/public/`, you can migrate them to Supabase:

### Option 1: Manual Upload via Dashboard

1. Go to Supabase Storage → your bucket
2. Create folders: `candidates`, `profile-photos`, `messages`
3. Upload files from `storage/app/public/` to respective folders

### Option 2: Programmatic Migration

Create a migration command to copy files:

```php
// app/Console/Commands/MigrateToSupabase.php
public function handle()
{
    $localDisk = Storage::disk('public');
    $supabaseDisk = Storage::disk('supabase');

    // Migrate candidates
    foreach ($localDisk->files('candidates') as $file) {
        $supabaseDisk->put($file, $localDisk->get($file));
    }

    // Repeat for other folders...
}
```

## Troubleshooting

### Files Not Appearing

- Check that `SUPABASE_URL` and `SUPABASE_KEY` are correct
- Verify bucket name matches `SUPABASE_BUCKET`
- Ensure bucket is set to **Public**

### Upload Failures

- Check Supabase Storage quota (free tier has limits)
- Verify API key has write permissions
- Check Laravel logs: `storage/logs/laravel.log`

### URL Not Working

- Ensure bucket is public
- Check CORS settings in Supabase
- Verify the URL format in browser

### Permission Errors

- The `anon` key should have full access to public buckets
- Check RLS (Row Level Security) policies in Supabase

## Rollback to Local Storage

If needed, you can switch back to local storage:

1. Change `FILESYSTEM_DISK=local` in `.env`
2. Update controllers to use `Storage::disk('public')` instead of `Storage::disk('supabase')`
3. Run `php artisan storage:link`

## Security Considerations

1. **Public Bucket**: Files are publicly accessible via URL
2. **API Key**: The anon key is safe to expose (it's used client-side)
3. **File Validation**: Upload validation happens in Laravel before reaching Supabase
4. **Bucket Policies**: Consider adding size limits and file type restrictions in Supabase

## Performance

- Files are served from Supabase CDN (faster than local storage)
- No symlink required (`storage:link` not needed)
- Automatic image optimization available through Supabase
- Global CDN distribution

## Cost Considerations

Supabase Free Tier:

- 1 GB storage
- 2 GB bandwidth/month

For production, consider upgrading or using Supabase's Pro plan.

## Support

For Supabase-specific issues:

- [Supabase Documentation](https://supabase.com/docs/guides/storage)
- [Supabase Discord](https://discord.supabase.com/)

For Laravel integration issues:

- Check `storage/logs/laravel.log`
- Review `app/Storage/SupabaseStorageAdapter.php`
