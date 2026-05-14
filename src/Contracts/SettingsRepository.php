<?php

declare(strict_types=1);

namespace Hatchyu\Settings\Contracts;

interface SettingsRepository
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value): void;

    public function has(string $key): bool;

    public function forget(string $key): void;

    public function all(?string $prefix = null): array;
}
