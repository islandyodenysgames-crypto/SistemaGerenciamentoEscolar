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
            'attendance',
            function (Table $table) {

                $table->id();

                $table->integer('school_class_id');

                $table->string('attendance_date', 20);

                $table->string('notes', 255)
                    ->nullable();

                $table->timestamps();

            }
        );
    }

    public function down(): void
    {
    }
};