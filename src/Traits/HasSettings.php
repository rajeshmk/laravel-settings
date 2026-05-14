<?php

declare(strict_types=1);

namespace Hatchyu\Settings\Traits;

use Hatchyu\Settings\Support\Scope;

trait HasSettings
{
    public function settings(): Scope
    {
        return settings()->scope($this);
    }

    public function getSettingsScopeType(): string
    {
        return str($this->getMorphClass())
            ->afterLast('\\')
            ->snake()
            ->toString()
        ;
    }

    public function getSettingsScopeId(): int|string
    {
        return $this->getKey();
    }

    public function getSettingsFallbackScopes(): array
    {
        return [];
    }
}
