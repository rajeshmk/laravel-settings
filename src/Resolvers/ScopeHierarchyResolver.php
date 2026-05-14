<?php

declare(strict_types=1);

namespace Hatchyu\Settings\Resolvers;

use Hatchyu\Settings\Contracts\SettingsScope;

class ScopeHierarchyResolver
{
    public function resolve(?SettingsScope $scope): array
    {
        if (! $scope instanceof SettingsScope) {
            return [
                [
                    'scope_type' => 'global',
                    'scope_id' => null,
                ],
            ];
        }

        $resolved = [
            [
                'scope_type' => $scope->getSettingsScopeType(),
                'scope_id' => (string) $scope->getSettingsScopeId(),
            ],
        ];

        foreach ($scope->getSettingsFallbackScopes() as $fallback) {
            if (! $fallback instanceof SettingsScope) {
                continue;
            }

            $resolved[] = [
                'scope_type' => $fallback->getSettingsScopeType(),
                'scope_id' => (string) $fallback->getSettingsScopeId(),
            ];
        }

        $resolved[] = [
            'scope_type' => 'global',
            'scope_id' => null,
        ];

        return $resolved;
    }
}
