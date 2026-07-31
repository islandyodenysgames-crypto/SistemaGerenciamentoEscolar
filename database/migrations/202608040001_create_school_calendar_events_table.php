<?php

declare(strict_types=1);

use App\Database\Migration;
use App\Database\Schema;
use App\Database\Table;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('school_calendar_events')) {
            return;
        }

        Schema::create('school_calendar_events', function (Table $table) {
            $table->id();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('type', 40)->default('OTHER');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('start_time', 5)->nullable();
            $table->string('end_time', 5)->nullable();
            $table->string('location', 180)->nullable();
            $table->boolean('all_day')->default(true);
            $table->boolean('featured')->default(false);
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_calendar_events');
    }
};
