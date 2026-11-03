<?php

namespace App\Providers;

use App\Filesystem\VercelBlobAdapter;
use App\View\Composers\ShellComposer;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Livewire\Volt\Volt;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Volt::mount(resource_path('views/livewire'));

        View::composer('layouts.hss', ShellComposer::class);

        Storage::extend('vercel-blob', function ($app, array $config) {
            $adapter = new VercelBlobAdapter((string) $config['token'], (string) ($config['prefix'] ?? ''));

            return new FilesystemAdapter(new Filesystem($adapter, $config), $adapter, $config);
        });
    }
}
