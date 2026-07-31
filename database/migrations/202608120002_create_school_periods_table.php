<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Connection::getInstance()->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS school_periods (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    school_year_id BIGINT NOT NULL,
    name VARCHAR(120) NOT NULL,
    short_name VARCHAR(40) NULL,
    type ENUM('BIMESTER','TRIMESTER','SEMESTER','CUSTOM') NOT NULL DEFAULT 'CUSTOM',
    order_number INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    color VARCHAR(20) NULL,
    description TEXT NULL,
    status ENUM('OPEN','CLOSING','CLOSED','REOPENED') NOT NULL DEFAULT 'OPEN',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_school_periods_year FOREIGN KEY (school_year_id) REFERENCES school_years(id) ON DELETE RESTRICT,
    CONSTRAINT chk_school_periods_dates CHECK (end_date >= start_date),
    UNIQUE KEY uq_school_period_order (school_year_id, order_number),
    UNIQUE KEY uq_school_period_name (school_year_id, name),
    INDEX idx_school_period_dates (school_year_id, start_date, end_date),
    INDEX idx_school_period_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }

    public function down(): void
    {
        Connection::getInstance()->exec('DROP TABLE IF EXISTS school_periods');
    }
};
