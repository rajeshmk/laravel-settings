<?php

declare(strict_types=1);

namespace Hatchyu\Settings\Resolvers;

class ValueCaster
{
    /**
     * Encode the value based on its type for storage.
     */
    public static function encode(string $type, mixed $value): mixed
    {
        return match ($type) {
            'array', 'object', 'json' => json_encode($value),
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOL) ? '1' : '0',
            default => $value,
        };
    }

    /**
     * Decode the value based on its type from storage.
     */
    public static function decode(string $type, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'integer' => (int) $value,
            'float', 'double' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOL),
            'array' => is_array($value) ? $value : json_decode((string) $value, true),
            'object' => is_object($value) ? $value : json_decode((string) $value, false),
            'json' => is_array($value) ? $value : json_decode((string) $value, true),
            default => (string) $value,
        };
    }
}
