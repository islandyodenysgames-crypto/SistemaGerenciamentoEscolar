<?php

declare(strict_types=1);

use App\Database\Migration;
use App\Database\Schema;
use App\Database\Table;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intelligence_actions', function (Table $table): void {
            $table->id();
            $table->string('insight_key', 190);
            $table->string('source_type', 40)->default('INSIGHT');
            $table->string('status', 30)->default('NEW');
            $table->string('priority', 30)->default('ATTENTION');
            $table->string('title', 220);
            $table->string('category', 40)->default('SCHOOL');
            $table->integer('responsible_user_id')->nullable();
            $table->string('responsible_name', 150)->nullable();
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->integer('created_by')->nullable();
            $table->string('created_by_name', 150)->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('intelligence_action_history', function (Table $table): void {
            $table->id();
            $table->integer('action_id');
            $table->string('event_type', 40);
            $table->string('status_before', 30)->nullable();
            $table->string('status_after', 30)->nullable();
            $table->text('description')->nullable();
            $table->integer('created_by')->nullable();
            $table->string('created_by_name', 150)->nullable();
            $table->createdAt();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intelligence_action_history');
        Schema::dropIfExists('intelligence_actions');
    }
};
