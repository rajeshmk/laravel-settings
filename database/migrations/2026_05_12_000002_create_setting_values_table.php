<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('setting_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('setting_definition_id')
                ->constrained('setting_definitions')
                ->restrictOnDelete()
            ;

            $table->string('scope_type', 100);
            $table->string('scope_id', 100)->nullable();

            $table->longText('value')->nullable();

            $table->timestamps();

            $table->unique([
                'setting_definition_id',
                'scope_type',
                'scope_id',
            ], 'setting_scope_unique');

            $table->index(['scope_type', 'scope_id']);
            $table->index(['scope_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_values');
    }
};
