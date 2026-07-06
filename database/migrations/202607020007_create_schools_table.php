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
            'schools',
            function (Table $table) {

                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Identificação
                |--------------------------------------------------------------------------
                */

                $table->string('name', 180);

                $table->string('short_name', 80)
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Localização
                |--------------------------------------------------------------------------
                */

                $table->string('city', 120)
                    ->nullable();

                $table->string('state', 2)
                    ->nullable();

                $table->string('address', 255)
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Gestão
                |--------------------------------------------------------------------------
                */

                $table->string('principal', 120)
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Contato
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
                | Controle
                |--------------------------------------------------------------------------
                */

                $table->boolean('active')
                    ->default(true);

                $table->timestamps();

            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};