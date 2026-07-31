<?php

declare(strict_types=1);

use App\Database\Migration;
use App\Database\Schema;
use App\Database\Table;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('intelligence_action_history');
        Schema::dropIfExists('intelligence_actions');

        Schema::create('student_monitoring', function (Table $table): void {
            $table->id();
            $table->integer('student_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 30)->default('ACTIVE');
            $table->text('reason')->nullable();
            $table->integer('created_by');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('student_monitoring_users', function (Table $table): void {
            $table->id();
            $table->integer('monitoring_id');
            $table->integer('user_id');
            $table->integer('assigned_by');
            $table->string('status', 30)->default('ACTIVE');
            $table->dateTime('assigned_at');
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('student_monitoring_snapshots', function (Table $table): void {
            $table->id();
            $table->integer('monitoring_id');
            $table->string('snapshot_type', 20);
            $table->decimal('attendance_percentage', 5, 2)->default(0);
            $table->integer('total_records')->default(0);
            $table->integer('total_absences')->default(0);
            $table->integer('total_occurrences')->default(0);
            $table->integer('open_occurrences')->default(0);
            $table->string('risk_level', 30)->nullable();
            $table->json('payload')->nullable();
            $table->createdAt();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_monitoring_snapshots');
        Schema::dropIfExists('student_monitoring_users');
        Schema::dropIfExists('student_monitoring');
    }
};
