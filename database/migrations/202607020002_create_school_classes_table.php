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
            'school_classes',
            function (Table $table) {

                $table->id();

                $table->string('name', 120);

                $table->integer('year');

                $table->string('shift', 30);

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