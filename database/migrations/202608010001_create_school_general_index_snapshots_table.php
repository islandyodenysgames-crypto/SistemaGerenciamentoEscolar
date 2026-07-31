<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration {
    public function up(): void {
        Connection::getInstance()->exec("CREATE TABLE IF NOT EXISTS school_general_index_snapshots (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            snapshot_date DATE NOT NULL,
            score INT NOT NULL DEFAULT 0,
            frequency_score DECIMAL(5,1) NOT NULL DEFAULT 0,
            occurrence_score DECIMAL(5,1) NOT NULL DEFAULT 0,
            risk_score DECIMAL(5,1) NOT NULL DEFAULT 0,
            monitoring_score DECIMAL(5,1) NOT NULL DEFAULT 0,
            classes_score DECIMAL(5,1) NOT NULL DEFAULT 0,
            details_json JSON NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_school_general_index_date (snapshot_date),
            KEY idx_school_general_index_date (snapshot_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
    public function down(): void { Connection::getInstance()->exec('DROP TABLE IF EXISTS school_general_index_snapshots'); }
};
