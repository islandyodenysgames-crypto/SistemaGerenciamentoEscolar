<?php

declare(strict_types=1);

use App\Database\Migration;
use App\Database\Schema;
use App\Database\Table;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Table $table): void {
            $table->id();
            $table->string('group_name', 80);
            $table->string('key_name', 120);
            $table->string('setting_key', 210)->unique();
            $table->text('value');
            $table->text('default_value');
            $table->string('type', 30)->default('string');
            $table->string('category', 100)->default('Geral');
            $table->string('label', 160);
            $table->text('description')->nullable();
            $table->boolean('editable')->default(true);
            $table->boolean('requires_restart')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('system_setting_history', function (Table $table): void {
            $table->id();
            $table->string('group_name', 80);
            $table->string('key_name', 120);
            $table->string('setting_key', 210)->unique();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 30)->default('UPDATE');
            $table->dateTime('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_setting_history');
        Schema::dropIfExists('system_settings');
    }
};
