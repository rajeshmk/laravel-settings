<?php

declare(strict_types=1);

namespace Hatchyu\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SettingDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'group',
        'label',
        'description',
        'data_type',
        'default_value',
        'is_public',
        'is_encrypted',
        'is_system',
        'validation_rules',
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'is_public' => 'boolean',
        'is_encrypted' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(SettingValue::class);
    }
}
