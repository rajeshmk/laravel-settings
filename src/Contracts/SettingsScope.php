<?php

declare(strict_types=1);

namespace Hatchyu\Settings\Contracts;

interface SettingsScope
{
    public function getSettingsScopeType(): string;

    public function getSettingsScopeId(): int|string;

    /**
     * Used for inheritance.
     *
     * Example return:
     *
     * [
     *     [$team],
     *     [$company],
     * ]
     */
    public function getSettingsFallbackScopes(): array;
}
