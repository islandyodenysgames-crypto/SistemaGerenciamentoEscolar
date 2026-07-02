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
            'students',
            function (Table $table) {

                $table->id();

                $table->string('name', 160);

                $table->string('registration', 50)
                    ->unique();

                $table->string('birth_date', 20)
                    ->nullable();

                $table->string('guardian_name', 160)
                    ->nullable();

                $table->string('guardian_phone', 30)
                    ->nullable();

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