<?php

declare(strict_types=1);

namespace Hatchyu\Settings\Managers;

use Hatchyu\Settings\Contracts\SettingsManager as SettingsManagerContract;
use Hatchyu\Settings\Contracts\SettingsRepository;
use Hatchyu\Settings\Contracts\SettingsScope;
use Hatchyu\Settings\Exceptions\SettingsValidationException;
use Hatchyu\Settings\Models\SettingDefinition;
use Hatchyu\Settings\Repositories\CachedSettingsRepository;
use Hatchyu\Settings\Repositories\DatabaseSettingsRepository;
use Hatchyu\Settings\Support\Scope;
use Illuminate\Support\Facades\Validator;

class SettingsManager implements SettingsManagerContract
{
    protected SettingsRepository $repository;

    public function __construct(protected ?SettingsScope $scope = null)
    {
        $this->repository = new CachedSettingsRepository(
            new DatabaseSettingsRepository($this->scope),
            $this->scope
        );
    }

    public function scope(?SettingsScope $scope): Scope
    {
        return new Scope($scope);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->repository->has($key)) {
            return $this->repository->get($key, $default);
        }

        $all = $this->all($key);

        if ($all !== []) {
            return $all;
        }

        return $this->repository->get($key, $default);
    }

    public function set(string $key, mixed $value): static
    {
        $this->validate($key, $value);

        $this->repository->set($key, $value);

        return $this;
    }

    public function string(string $key, string $default = ''): string
    {
        return (string) ($this->get($key, $default) ?? $default);
    }

    public function boolean(string $key, bool $default = false): bool
    {
        return filter_var($this->get($key, $default), FILTER_VALIDATE_BOOL);
    }

    public function integer(string $key, int $default = 0): int
    {
        return (int) $this->get($key, $default);
    }

    public function float(string $key, float $default = 0.0): float
    {
        return (float) $this->get($key, $default);
    }

    public function array(string $key, array $default = []): array
    {
        return (array) $this->get($key, $default);
    }

    public function json(string $key, array $default = []): array
    {
        $value = $this->get($key, $default);

        return (is_array($value) ? $value : json_decode((string) $value, true)) ?: $default;
    }

    public function all(?string $prefix = null): array
    {
        return $this->repository->all($prefix);
    }

    public function has(string $key): bool
    {
        return $this->repository->has($key);
    }

    public function forget(string $key): void
    {
        $this->repository->forget($key);
    }

    protected function validate(string $key, mixed $value): void
    {
        $definition = SettingDefinition::where('key', $key)->first();

        if ($definition && $definition->validation_rules) {
            $validator = Validator::make(
                [$key => $value],
                [$key => $definition->validation_rules]
            );

            if ($validator->fails()) {
                throw new SettingsValidationException($key, $validator->errors());
            }
        }
    }
}
