<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $db = Connection::getInstance();
        $db->exec("CREATE TABLE IF NOT EXISTS student_monitoring_extensions (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            monitoring_user_id BIGINT NOT NULL,
            previous_end_date DATE NOT NULL,
            new_end_date DATE NOT NULL,
            reason TEXT NOT NULL,
            extended_by BIGINT NOT NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_monitoring_extension_link (monitoring_user_id, created_at),
            CONSTRAINT fk_monitoring_extension_link FOREIGN KEY (monitoring_user_id) REFERENCES student_monitoring_users(id) ON DELETE CASCADE,
            CONSTRAINT fk_monitoring_extension_user FOREIGN KEY (extended_by) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $columns = $db->query("SHOW COLUMNS FROM student_monitoring_actions")->fetchAll();
        $names = array_column($columns, 'Field');
        if (!in_array('issue_type', $names, true)) {
            $db->exec("ALTER TABLE student_monitoring_actions ADD issue_type VARCHAR(30) NULL AFTER result_status");
        }
        if (!in_array('treatment_status', $names, true)) {
            $db->exec("ALTER TABLE student_monitoring_actions ADD treatment_status VARCHAR(30) NULL AFTER issue_type");
        }
    }

    public function down(): void
    {
        $db = Connection::getInstance();
        $columns = $db->query("SHOW COLUMNS FROM student_monitoring_actions")->fetchAll();
        $names = array_column($columns, 'Field');
        if (in_array('treatment_status', $names, true)) $db->exec("ALTER TABLE student_monitoring_actions DROP COLUMN treatment_status");
        if (in_array('issue_type', $names, true)) $db->exec("ALTER TABLE student_monitoring_actions DROP COLUMN issue_type");
        $db->exec("DROP TABLE IF EXISTS student_monitoring_extensions");
    }
};
