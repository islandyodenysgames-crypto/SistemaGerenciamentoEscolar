<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Connection::getInstance()->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS institutional_generated_contents (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    content_type VARCHAR(80) NOT NULL,
    reference_date DATE NOT NULL,
    style VARCHAR(30) NOT NULL DEFAULT 'institutional',
    text_content TEXT NOT NULL,
    data_snapshot JSON NULL,
    source_provider VARCHAR(50) NOT NULL DEFAULT 'rules',
    status ENUM('pending','approved','published','discarded') NOT NULL DEFAULT 'published',
    is_automatic TINYINT(1) NOT NULL DEFAULT 1,
    approved_by BIGINT NULL,
    approved_at DATETIME NULL,
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_igc_type_date (content_type, reference_date),
    INDEX idx_igc_status_date (status, reference_date),
    CONSTRAINT fk_igc_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }

    public function down(): void
    {
        Connection::getInstance()->exec('DROP TABLE IF EXISTS institutional_generated_contents');
    }
};
