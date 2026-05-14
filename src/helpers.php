<?php

declare(strict_types=1);

use Hatchyu\Settings\Managers\SettingsManager;

if (! function_exists('settings')) {
    function settings(): SettingsManager
    {
        return resolve('settings');
    }
}
