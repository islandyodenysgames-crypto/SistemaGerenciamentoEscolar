<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Connection::getInstance()->exec("CREATE TABLE IF NOT EXISTS student_monitoring_recommendations (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            monitoring_id BIGINT NOT NULL,
            recommendation_code VARCHAR(60) NOT NULL,
            title VARCHAR(180) NOT NULL,
            reason TEXT NOT NULL,
            priority VARCHAR(20) NOT NULL DEFAULT 'ATTENTION',
            status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
            resolution_notes TEXT NULL,
            handled_by BIGINT NULL,
            handled_at DATETIME NULL,
            generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_monitoring_recommendation_monitoring (monitoring_id,status),
            KEY idx_monitoring_recommendation_priority (priority,status),
            CONSTRAINT fk_monitoring_recommendation_monitoring FOREIGN KEY (monitoring_id) REFERENCES student_monitoring(id) ON DELETE CASCADE,
            CONSTRAINT fk_monitoring_recommendation_handler FOREIGN KEY (handled_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function down(): void
    {
        Connection::getInstance()->exec("DROP TABLE IF EXISTS student_monitoring_recommendations");
    }
};
