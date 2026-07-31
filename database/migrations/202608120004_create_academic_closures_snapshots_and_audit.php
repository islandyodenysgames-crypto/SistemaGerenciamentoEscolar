<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $db=Connection::getInstance();
        $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS academic_snapshots (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    school_year_id BIGINT NOT NULL,
    school_period_id BIGINT NULL,
    snapshot_type ENUM('PERIOD_CLOSE','YEAR_CLOSE','HISTORICAL') NOT NULL,
    metrics_json LONGTEXT NOT NULL,
    created_by BIGINT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_academic_snapshots_year FOREIGN KEY (school_year_id) REFERENCES school_years(id) ON DELETE RESTRICT,
    CONSTRAINT fk_academic_snapshots_period FOREIGN KEY (school_period_id) REFERENCES school_periods(id) ON DELETE RESTRICT,
    CONSTRAINT fk_academic_snapshots_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_academic_snapshots_scope (school_year_id, school_period_id, snapshot_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
        $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS academic_audit_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    school_year_id BIGINT NULL,
    school_period_id BIGINT NULL,
    action VARCHAR(80) NOT NULL,
    description TEXT NOT NULL,
    metadata_json LONGTEXT NULL,
    user_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_academic_audit_year FOREIGN KEY (school_year_id) REFERENCES school_years(id) ON DELETE SET NULL,
    CONSTRAINT fk_academic_audit_period FOREIGN KEY (school_period_id) REFERENCES school_periods(id) ON DELETE SET NULL,
    CONSTRAINT fk_academic_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_academic_audit_scope (school_year_id, school_period_id, created_at),
    INDEX idx_academic_audit_action (action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }

    public function down(): void
    {
        $db=Connection::getInstance();
        $db->exec('DROP TABLE IF EXISTS academic_audit_log');
        $db->exec('DROP TABLE IF EXISTS academic_snapshots');
    }
};
