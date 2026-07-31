<?php

declare(strict_types=1);

use App\Database\Migration;
use App\Database\Schema;
use App\Database\Table;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'occurrence_notifications',
            function (Table $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('type', 80);
                $table->string('title', 180);
                $table->text('message');
                $table->string('severity', 20)
                    ->default('INFO');
                $table->string('reference_type', 60)
                    ->nullable();
                $table->unsignedBigInteger('reference_id')
                    ->nullable();
                $table->string('fingerprint', 64)
                    ->unique();
                $table->boolean('is_read')
                    ->default(false);
                $table->dateTime('read_at')
                    ->nullable();
                $table->json('metadata')
                    ->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'occurrence_notifications'
        );
    }
};
