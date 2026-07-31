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
            'occurrence_actions',
            function (Table $table) {
                $table->id();

                /*
                 * Ocorrência à qual a providência pertence.
                 */
                $table->integer(
                    'occurrence_id'
                );

                /*
                 * Texto da intervenção ou providência.
                 */
                $table->text(
                    'description'
                );

                /*
                 * Situação da ocorrência após
                 * esta providência:
                 *
                 * OPEN
                 * RESOLVED
                 */
                $table->string(
                    'status_after',
                    20
                )->default('OPEN');

                /*
                 * Fotografia do autor no momento
                 * em que a providência foi registrada.
                 */
                $table->integer(
                    'created_by'
                )->nullable();

                $table->string(
                    'created_by_name',
                    150
                )->nullable();

                $table->string(
                    'created_by_role',
                    60
                )->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'occurrence_actions'
        );
    }
};