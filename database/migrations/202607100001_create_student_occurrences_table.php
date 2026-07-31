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
            'student_occurrences',
            function (Table $table) {

                $table->id();

                $table->integer('student_id');

                $table->date('occurrence_date');

                $table->string('type', 50);

                $table->string('title', 150);

                $table->text('description');

                $table->text('actions_taken')
                    ->nullable();

                $table->string('status', 20)
                    ->default('OPEN');

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'student_occurrences'
        );
    }
};