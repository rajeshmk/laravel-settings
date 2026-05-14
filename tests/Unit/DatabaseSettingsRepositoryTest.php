<?php

declare(strict_types=1);

use Hatchyu\Settings\Models\SettingDefinition;
use Hatchyu\Settings\Repositories\DatabaseSettingsRepository;

afterEach(function (): void {
    TestDatabaseSettingsRepository::resetDefinitionCache();
});

it('returns cached missing setting definitions without querying again', function (): void {
    TestDatabaseSettingsRepository::primeDefinitionCache([
        'missing.setting' => null,
    ]);

    $repository = new TestDatabaseSettingsRepository();

    expect($repository->lookup('missing.setting'))->toBeNull();
});

class TestDatabaseSettingsRepository extends DatabaseSettingsRepository
{
    /**
     * @param array<string, SettingDefinition|null> $definitions
     */
    public static function primeDefinitionCache(array $definitions): void
    {
        static::$definitionCache = $definitions;
    }

    public static function resetDefinitionCache(): void
    {
        static::$definitionCache = [];
    }

    public function lookup(string $key): ?SettingDefinition
    {
        return $this->getDefinition($key);
    }
}
