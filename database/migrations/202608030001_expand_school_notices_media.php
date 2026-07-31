<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;
use App\Database\Schema;
use App\Database\Table;

return new class extends Migration
{
    public function up(): void
    {
        $db = Connection::getInstance();
        $columns = [
            'summary' => "ALTER TABLE school_notices ADD COLUMN summary VARCHAR(320) NULL AFTER title",
            'category' => "ALTER TABLE school_notices ADD COLUMN category VARCHAR(40) NOT NULL DEFAULT 'GENERAL' AFTER content",
            'banner_path' => "ALTER TABLE school_notices ADD COLUMN banner_path VARCHAR(500) NULL AFTER category",
            'youtube_url' => "ALTER TABLE school_notices ADD COLUMN youtube_url VARCHAR(500) NULL AFTER banner_path",
            'featured' => "ALTER TABLE school_notices ADD COLUMN featured BOOLEAN NOT NULL DEFAULT 0 AFTER pinned",
        ];
        foreach ($columns as $name => $sql) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'school_notices' AND column_name = :name");
            $stmt->execute(['name' => $name]);
            if ((int) $stmt->fetchColumn() === 0) {
                $db->exec($sql);
            }
        }

        if (!Schema::hasTable('school_notice_attachments')) {
            Schema::create('school_notice_attachments', function (Table $table) {
                $table->id();
                $table->unsignedBigInteger('notice_id');
                $table->string('original_name', 255);
                $table->string('stored_name', 255);
                $table->string('relative_path', 500);
                $table->string('mime_type', 150);
                $table->string('extension', 20)->nullable();
                $table->integer('size_bytes');
                $table->unsignedBigInteger('uploaded_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_notice_attachments');
    }
};
