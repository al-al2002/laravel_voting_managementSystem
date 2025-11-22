<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem;
use App\Storage\SupabaseStorageAdapter;

class SupabaseStorageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Storage::extend('supabase', function ($app, $config) {
            $adapter = new SupabaseStorageAdapter($config);
            $filesystem = new Filesystem($adapter, $config);

            return new FilesystemAdapter($filesystem, $adapter, $config);
        });
    }
}
