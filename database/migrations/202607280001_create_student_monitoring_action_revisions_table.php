<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Connection::getInstance()->exec("CREATE TABLE IF NOT EXISTS student_monitoring_action_revisions (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            action_id BIGINT NOT NULL,
            revision_type VARCHAR(20) NOT NULL,
            action_date DATE NOT NULL,
            action_type VARCHAR(60) NOT NULL,
            action_type_label VARCHAR(150) NOT NULL,
            description TEXT NOT NULL,
            result_status VARCHAR(30) NULL,
            issue_type VARCHAR(40) NOT NULL DEFAULT 'GENERAL',
            treatment_status VARCHAR(40) NULL,
            next_action TEXT NULL,
            changed_by BIGINT NOT NULL,
            changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_action_revisions_timeline (action_id, changed_at, id),
            CONSTRAINT fk_action_revision_action FOREIGN KEY (action_id) REFERENCES student_monitoring_actions(id) ON DELETE CASCADE,
            CONSTRAINT fk_action_revision_user FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function down(): void
    {
        Connection::getInstance()->exec("DROP TABLE IF EXISTS student_monitoring_action_revisions");
    }
};
