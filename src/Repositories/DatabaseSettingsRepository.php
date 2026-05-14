<?php

declare(strict_types=1);

namespace Hatchyu\Settings\Repositories;

use Hatchyu\Settings\Contracts\SettingsRepository;
use Hatchyu\Settings\Contracts\SettingsScope;
use Hatchyu\Settings\Models\SettingDefinition;
use Hatchyu\Settings\Models\SettingValue;
use Hatchyu\Settings\Resolvers\ScopeHierarchyResolver;
use Hatchyu\Settings\Resolvers\ValueCaster;
use Illuminate\Support\Arr;

class DatabaseSettingsRepository implements SettingsRepository
{
    /**
     * In-memory cache for setting definitions during the current PHP process.
     *
     * @var array<string, SettingDefinition>
     */
    protected static array $definitionCache = [];

    public function __construct(
        protected ?SettingsScope $scope = null,
        protected ?ScopeHierarchyResolver $resolver = null,
    ) {
        $this->resolver = $resolver ?: new ScopeHierarchyResolver();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $definition = $this->getDefinition($key);

        if (! $definition instanceof SettingDefinition) {
            return $default;
        }

        $scopes = $this->resolver->resolve($this->scope);

        // Optimization: Fetch all potential values for the entire hierarchy in one query
        $values = SettingValue::query()
            ->where('setting_definition_id', $definition->id)
            ->where(function ($query) use ($scopes): void {
                foreach ($scopes as $scope) {
                    $query->orWhere(function ($q) use ($scope): void {
                        $q->where('scope_type', $scope['scope_type'])
                            ->where('scope_id', $scope['scope_id'])
                        ;
                    });
                }
            })
            ->get()
        ;

        // Find the best match according to the hierarchy priority
        foreach ($scopes as $scope) {
            $match = $values->first(
                fn ($v): bool => $v->scope_type === $scope['scope_type'] && (string) $v->scope_id === (string) $scope['scope_id']
            );

            if ($match) {
                return ValueCaster::decode(
                    $definition->data_type,
                    $match->value
                );
            }
        }

        return ValueCaster::decode(
            $definition->data_type,
            $definition->default_value ?? $default
        );
    }

    public function set(string $key, mixed $value): void
    {
        $definition = SettingDefinition::query()->firstOrCreate(
            ['key' => $key],
            [
                'group' => str($key)->before('.')->toString(),
                'data_type' => $this->mapPhpTypeToSettingType($value),
            ]
        );

        $this->clearDefinitionCache($key);

        $scopeType = $this->scope?->getSettingsScopeType() ?? 'global';
        $scopeId = $this->scope?->getSettingsScopeId();

        SettingValue::query()->updateOrCreate(
            [
                'setting_definition_id' => $definition->id,
                'scope_type' => $scopeType,
                'scope_id' => $scopeId,
            ],
            [
                'value' => ValueCaster::encode($definition->data_type, $value),
            ]
        );
    }

    public function has(string $key): bool
    {
        $definition = $this->getDefinition($key);

        if (! $definition instanceof SettingDefinition) {
            return false;
        }

        $scopes = $this->resolver->resolve($this->scope);

        $exists = SettingValue::query()
            ->where('setting_definition_id', $definition->id)
            ->where(function ($query) use ($scopes): void {
                foreach ($scopes as $scope) {
                    $query->orWhere(function ($q) use ($scope): void {
                        $q->where('scope_type', $scope['scope_type'])
                            ->where('scope_id', $scope['scope_id'])
                        ;
                    });
                }
            })
            ->exists()
        ;

        if ($exists) {
            return true;
        }

        return $definition->default_value !== null;
    }

    public function forget(string $key): void
    {
        $definition = $this->getDefinition($key);

        if (! $definition instanceof SettingDefinition) {
            return;
        }

        $scopeType = $this->scope?->getSettingsScopeType() ?? 'global';
        $scopeId = $this->scope?->getSettingsScopeId();

        SettingValue::query()
            ->where('setting_definition_id', $definition->id)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->delete()
        ;
    }

    public function all(?string $prefix = null): array
    {
        $definitions = SettingDefinition::query()
            ->when($prefix, function ($query) use ($prefix): void {
                $query->where('key', 'LIKE', $prefix . '.%');
            })
            ->get()
        ;

        if ($definitions->isEmpty()) {
            return [];
        }

        $scopes = $this->resolver->resolve($this->scope);

        // Fetch all values for all matching definitions in one query
        $values = SettingValue::query()
            ->whereIn('setting_definition_id', $definitions->pluck('id'))
            ->where(function ($query) use ($scopes): void {
                foreach ($scopes as $scope) {
                    $query->orWhere(function ($q) use ($scope): void {
                        $q->where('scope_type', $scope['scope_type'])
                            ->where('scope_id', $scope['scope_id'])
                        ;
                    });
                }
            })
            ->get()
            ->groupBy('setting_definition_id')
        ;

        $result = [];

        foreach ($definitions as $definition) {
            $definitionValues = $values->get($definition->id) ?: collect();
            $value = null;
            $found = false;

            foreach ($scopes as $scope) {
                $match = $definitionValues->first(
                    fn ($v): bool => $v->scope_type === $scope['scope_type'] && (string) $v->scope_id === (string) $scope['scope_id']
                );

                if ($match) {
                    $value = ValueCaster::decode($definition->data_type, $match->value);
                    $found = true;

                    break;
                }
            }

            if (! $found) {
                $value = ValueCaster::decode($definition->data_type, $definition->default_value);
            }

            $nestedKey = $prefix ? str($definition->key)
                ->after($prefix . '.')
                ->toString() : $definition->key;

            Arr::set($result, $nestedKey, $value);
        }

        return $result;
    }

    protected function getDefinition(string $key): ?SettingDefinition
    {
        if (array_key_exists($key, static::$definitionCache)) {
            return static::$definitionCache[$key];
        }

        return static::$definitionCache[$key] = SettingDefinition::query()
            ->where('key', $key)
            ->first()
        ;
    }

    protected function clearDefinitionCache(?string $key = null): void
    {
        if ($key) {
            unset(static::$definitionCache[$key]);
        } else {
            static::$definitionCache = [];
        }
    }

    protected function mapPhpTypeToSettingType(mixed $value): string
    {
        $type = gettype($value);

        return match ($type) {
            'integer' => 'integer',
            'double' => 'float',
            'boolean' => 'boolean',
            'array' => 'array',
            'object' => 'object',
            default => 'string',
        };
    }
}
