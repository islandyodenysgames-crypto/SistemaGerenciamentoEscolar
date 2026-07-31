<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Connection::getInstance()->exec("CREATE TABLE IF NOT EXISTS student_monitoring_actions (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            monitoring_user_id BIGINT NOT NULL,
            action_date DATE NOT NULL,
            action_type VARCHAR(50) NOT NULL,
            action_type_label VARCHAR(160) NOT NULL,
            description TEXT NOT NULL,
            result_status VARCHAR(30) NULL,
            next_action TEXT NULL,
            created_by BIGINT NOT NULL,
            deleted_at TIMESTAMP NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_monitoring_actions_link_date (monitoring_user_id, action_date),
            KEY idx_monitoring_actions_deleted (deleted_at),
            CONSTRAINT fk_monitoring_action_link FOREIGN KEY (monitoring_user_id) REFERENCES student_monitoring_users(id) ON DELETE CASCADE,
            CONSTRAINT fk_monitoring_action_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function down(): void
    {
        Connection::getInstance()->exec("DROP TABLE IF EXISTS student_monitoring_actions");
    }
};
