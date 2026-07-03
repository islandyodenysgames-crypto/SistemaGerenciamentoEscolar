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
            'attendance_items',
            function (Table $table) {

                $table->id();

                $table->integer('attendance_id');

                $table->integer('student_id');

                $table->string('status', 20);

                $table->string('justification', 255)
                    ->nullable();

                $table->timestamps();

            }
        );
    }

    public function down(): void
    {
    }
};