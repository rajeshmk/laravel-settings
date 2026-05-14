<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('setting_definitions', function (Blueprint $table) {
            $table->id();

            $table->string('key')->unique();
            $table->string('group')->nullable()->index();

            $table->string('label')->nullable();
            $table->text('description')->nullable();

            $table->string('data_type', 50)->default('string');

            $table->longText('default_value')->nullable();

            $table->boolean('is_public')->default(false);
            $table->boolean('is_encrypted')->default(false);
            $table->boolean('is_system')->default(false);

            $table->json('validation_rules')->nullable();

            $table->timestamps();

            $table->index(['data_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_definitions');
    }
};
