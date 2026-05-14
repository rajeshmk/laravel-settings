<?php

declare(strict_types=1);

namespace Hatchyu\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettingValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_definition_id',
        'scope_type',
        'scope_id',
        'value',
    ];

    protected $casts = [];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(SettingDefinition::class, 'setting_definition_id');
    }
}
