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
            'enrollments',
            function (Table $table) {

                $table->id();

                $table->integer('student_id');

                $table->integer('school_class_id');

                $table->string('enrollment_date', 20);

                $table->boolean('active')
                    ->default(true);

                $table->timestamps();

            }
        );
    }

    public function down(): void
    {
    }
};