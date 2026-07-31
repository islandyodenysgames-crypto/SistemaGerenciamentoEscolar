<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $db = Connection::getInstance();
        $db->exec("CREATE TABLE IF NOT EXISTS student_monitoring_plans (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            monitoring_user_id BIGINT NOT NULL,
            objective_code VARCHAR(40) NOT NULL,
            objective_label VARCHAR(160) NOT NULL,
            strategies TEXT NULL,
            target_metric VARCHAR(30) NULL,
            baseline_value DECIMAL(10,2) NULL,
            target_value DECIMAL(10,2) NULL,
            notes TEXT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'ACTIVE',
            created_by BIGINT NOT NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_student_monitoring_plan_link (monitoring_user_id),
            CONSTRAINT fk_student_monitoring_plan_link FOREIGN KEY (monitoring_user_id) REFERENCES student_monitoring_users(id) ON DELETE CASCADE,
            CONSTRAINT fk_student_monitoring_plan_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function down(): void
    {
        Connection::getInstance()->exec("DROP TABLE IF EXISTS student_monitoring_plans");
    }
};
