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
            'users',
            function (Table $table) {

                $table->id();

                $table->string('name', 120);

                $table->string('email', 180)
                    ->unique();

                $table->string('password');

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