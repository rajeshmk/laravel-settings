<?php

declare(strict_types=1);

namespace Hatchyu\Settings\Repositories;

use Hatchyu\Settings\Contracts\SettingsRepository;
use Hatchyu\Settings\Contracts\SettingsScope;
use Illuminate\Support\Facades\Cache;

class CachedSettingsRepository implements SettingsRepository
{
    public function __construct(
        protected SettingsRepository $repository,
        protected ?SettingsScope $scope = null,
        protected string $cachePrefix = 'settings',
        protected int $ttl = 3600,
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        if (! $this->has($key)) {
            return $this->repository->get($key, $default);
        }

        return Cache::remember(
            $this->cacheKey($key, 'val'),
            $this->ttl,
            fn (): mixed => $this->repository->get($key, $default)
        );
    }

    public function set(string $key, mixed $value): void
    {
        $this->repository->set($key, $value);

        $this->clearCache($key);
    }

    public function has(string $key): bool
    {
        return Cache::remember(
            $this->cacheKey($key, 'has'),
            $this->ttl,
            fn (): bool => $this->repository->has($key)
        );
    }

    public function forget(string $key): void
    {
        $this->repository->forget($key);

        $this->clearCache($key);
    }

    public function all(?string $prefix = null): array
    {
        return Cache::remember(
            $this->cacheKey($prefix ?? 'all', 'grp'),
            $this->ttl,
            fn (): array => $this->repository->all($prefix)
        );
    }

    protected function cacheKey(string $key, string $type): string
    {
        $version = $this->cacheVersion();
        $scopeType = $this->scope?->getSettingsScopeType() ?? 'global';
        $scopeId = (string) ($this->scope?->getSettingsScopeId() ?? '__global__');

        return "{$this->cachePrefix}:{$version}:{$type}:{$scopeType}:{$scopeId}:{$key}";
    }

    protected function clearCache(string $key): void
    {
        Cache::forever($this->versionKey(), (string) hrtime(true));

        // Clear the specific value and existence cache
        Cache::forget($this->cacheKey($key, 'val'));
        Cache::forget($this->cacheKey($key, 'has'));

        // Clear "all" cache
        Cache::forget($this->cacheKey('all', 'grp'));

        // Clear all parent prefixes recursively
        $parts = explode('.', $key);
        while (count($parts) > 0) {
            array_pop($parts);
            $prefix = implode('.', $parts);
            if ($prefix !== '' && $prefix !== '0') {
                Cache::forget($this->cacheKey($prefix, 'grp'));
            }
        }
    }

    protected function cacheVersion(): string
    {
        return (string) Cache::get($this->versionKey(), '1');
    }

    protected function versionKey(): string
    {
        return "{$this->cachePrefix}:version";
    }
}
