<?php

declare(strict_types=1);

namespace Hatchyu\Settings\Contracts;

use Hatchyu\Settings\Support\Scope;

interface SettingsManager
{
    public function scope(?SettingsScope $scope): Scope;

    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value): static;

    public function string(string $key, string $default = ''): string;

    public function boolean(string $key, bool $default = false): bool;

    public function integer(string $key, int $default = 0): int;

    public function float(string $key, float $default = 0.0): float;

    public function array(string $key, array $default = []): array;

    public function json(string $key, array $default = []): array;

    public function all(?string $prefix = null): array;

    public function has(string $key): bool;

    public function forget(string $key): void;
}
