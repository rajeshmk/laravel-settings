<?php

declare(strict_types=1);

namespace Hatchyu\Settings;

use Hatchyu\Settings\Managers\SettingsManager;
use Illuminate\Support\ServiceProvider;
use Override;

class SettingsServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton('settings', function (): SettingsManager {
            return new SettingsManager();
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        require_once __DIR__ . '/helpers.php';
    }
}
