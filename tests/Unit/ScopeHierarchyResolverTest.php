<?php

declare(strict_types=1);

use Hatchyu\Settings\Contracts\SettingsScope;
use Hatchyu\Settings\Resolvers\ScopeHierarchyResolver;

it('resolves global scope when no scope is provided', function (): void {
    expect((new ScopeHierarchyResolver())->resolve(null))->toBe([
        [
            'scope_type' => 'global',
            'scope_id' => null,
        ],
    ]);
});

it('resolves a scope hierarchy and skips invalid fallbacks', function (): void {
    $company = new class() implements SettingsScope
    {
        public function getSettingsScopeType(): string
        {
            return 'company';
        }

        public function getSettingsScopeId(): int|string
        {
            return 10;
        }

        public function getSettingsFallbackScopes(): array
        {
            return [];
        }
    };

    $user = new class($company) implements SettingsScope
    {
        public function __construct(private SettingsScope $company) {}

        public function getSettingsScopeType(): string
        {
            return 'user';
        }

        public function getSettingsScopeId(): int|string
        {
            return 5;
        }

        public function getSettingsFallbackScopes(): array
        {
            return [
                null,
                $this->company,
            ];
        }
    };

    expect((new ScopeHierarchyResolver())->resolve($user))->toBe([
        [
            'scope_type' => 'user',
            'scope_id' => '5',
        ],
        [
            'scope_type' => 'company',
            'scope_id' => '10',
        ],
        [
            'scope_type' => 'global',
            'scope_id' => null,
        ],
    ]);
});
