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
            'school_notices',
            function (Table $table) {

                $table->id();

                $table->string('title', 180);

                $table->text('content');

                /*
                 * INFO
                 * IMPORTANT
                 * URGENT
                 */
                $table->string('priority', 20)
                    ->default('INFO');

                $table->boolean('pinned')
                    ->default(false);

                $table->boolean('active')
                    ->default(true);

                /*
                 * ALL
                 * TEACHERS
                 * COORDINATION
                 * SECRETARY
                 * ADMINISTRATION
                 * CLASS
                 * TV_PANEL
                 */
                $table->string('target', 30)
                    ->default('ALL');

                /*
                 * Preenchido somente quando:
                 * target = CLASS
                 */
                $table->unsignedBigInteger('target_class_id')
                    ->nullable();

                /*
                 * Data e hora em que o aviso começa
                 * a aparecer no sistema.
                 */
                $table->dateTime('published_at')
                    ->nullable();

                /*
                 * Data e hora em que o aviso deixa
                 * de aparecer no Dashboard.
                 */
                $table->dateTime('expires_at')
                    ->nullable();

                /*
                 * Usuário que criou o aviso.
                 */
                $table->unsignedBigInteger('created_by')
                    ->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('school_notices');
    }
};