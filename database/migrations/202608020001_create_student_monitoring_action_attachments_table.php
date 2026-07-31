<?php

declare(strict_types=1);

use App\Database\Migration;
use App\Database\Schema;
use App\Database\Table;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_monitoring_action_attachments', function (Table $table) {
            $table->id();
            $table->integer('action_id');
            $table->string('original_name', 255);
            $table->string('stored_name', 255);
            $table->string('relative_path', 500);
            $table->string('mime_type', 150);
            $table->string('extension', 20)->nullable();
            $table->integer('size_bytes');
            $table->integer('uploaded_by')->nullable();
            $table->string('uploaded_by_name', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_monitoring_action_attachments');
    }
};
