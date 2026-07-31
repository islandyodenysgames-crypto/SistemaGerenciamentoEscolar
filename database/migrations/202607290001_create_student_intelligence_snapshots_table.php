<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Connection::getInstance()->exec("CREATE TABLE IF NOT EXISTS student_intelligence_snapshots (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT NOT NULL,
            student_id BIGINT NOT NULL,
            risk_level VARCHAR(20) NOT NULL,
            risk_score INT NOT NULL DEFAULT 0,
            latest_action_date DATE NULL,
            last_stale_alert_at DATETIME NULL,
            evaluated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_intelligence_snapshot_user_student (user_id, student_id),
            KEY idx_intelligence_snapshot_student (student_id),
            CONSTRAINT fk_intelligence_snapshot_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_intelligence_snapshot_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function down(): void
    {
        Connection::getInstance()->exec("DROP TABLE IF EXISTS student_intelligence_snapshots");
    }
};
