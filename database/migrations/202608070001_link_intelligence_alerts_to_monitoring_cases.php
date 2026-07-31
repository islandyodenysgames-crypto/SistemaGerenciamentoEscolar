<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $db = Connection::getInstance();
        $db->exec("CREATE TABLE IF NOT EXISTS intelligence_alert_case_links (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            student_id BIGINT NOT NULL,
            monitoring_id BIGINT NOT NULL,
            alert_key VARCHAR(120) NOT NULL,
            alert_type VARCHAR(80) NOT NULL,
            linked_by BIGINT NULL,
            linked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_intelligence_alert_case (student_id, monitoring_id, alert_key),
            KEY idx_intelligence_alert_student_key (student_id, alert_key),
            KEY idx_intelligence_alert_monitoring (monitoring_id),
            CONSTRAINT fk_intelligence_alert_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            CONSTRAINT fk_intelligence_alert_monitoring FOREIGN KEY (monitoring_id) REFERENCES student_monitoring(id) ON DELETE CASCADE,
            CONSTRAINT fk_intelligence_alert_user FOREIGN KEY (linked_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function down(): void
    {
        Connection::getInstance()->exec('DROP TABLE IF EXISTS intelligence_alert_case_links');
    }
};
