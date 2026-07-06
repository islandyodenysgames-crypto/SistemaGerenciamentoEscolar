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
            'school_settings',
            function (Table $table) {

                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Dados Institucionais
                |--------------------------------------------------------------------------
                */

                $table->string('school_name', 180);

                $table->string('school_short_name', 80)
                    ->nullable();

                $table->string('city', 120)
                    ->nullable();

                $table->string('state', 2)
                    ->nullable();

                $table->string('principal', 120)
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Identidade Visual
                |--------------------------------------------------------------------------
                */

                $table->string('logo_path', 255)
                    ->nullable();

                $table->string('primary_color', 20)
                    ->default('#16a34a');

                $table->string('secondary_color', 20)
                    ->default('#f97316');

                /*
                |--------------------------------------------------------------------------
                | Contatos
                |--------------------------------------------------------------------------
                */

                $table->string('phone', 30)
                    ->nullable();

                $table->string('email', 180)
                    ->nullable();

                $table->string('website', 180)
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Controle
                |--------------------------------------------------------------------------
                */

                $table->timestamps();

            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};