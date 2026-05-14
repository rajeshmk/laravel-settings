<?php

declare(strict_types=1);

namespace Hatchyu\Settings\Exceptions;

use Exception;
use Illuminate\Support\MessageBag;

class SettingsValidationException extends Exception
{
    public function __construct(
        protected string $key,
        protected MessageBag $errors
    ) {
        parent::__construct("Validation failed for setting [{$key}]: " . $errors->first());
    }

    public function getErrors(): MessageBag
    {
        return $this->errors;
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
