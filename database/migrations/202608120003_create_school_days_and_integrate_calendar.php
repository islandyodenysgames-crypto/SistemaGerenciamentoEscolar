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
CREATE TABLE IF NOT EXISTS school_days (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    school_year_id BIGINT NOT NULL,
    school_period_id BIGINT NULL,
    calendar_event_id BIGINT NULL,
    school_date DATE NOT NULL,
    day_type ENUM('SCHOOL_DAY','HOLIDAY','RECESS','VACATION','PLANNING','COUNCIL','STOPPAGE','MAKEUP','SATURDAY_SCHOOL','OPTIONAL') NOT NULL,
    is_instructional TINYINT(1) NOT NULL DEFAULT 0,
    is_completed TINYINT(1) NOT NULL DEFAULT 0,
    title VARCHAR(180) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_school_days_year FOREIGN KEY (school_year_id) REFERENCES school_years(id) ON DELETE CASCADE,
    CONSTRAINT fk_school_days_period FOREIGN KEY (school_period_id) REFERENCES school_periods(id) ON DELETE SET NULL,
    CONSTRAINT fk_school_days_event FOREIGN KEY (calendar_event_id) REFERENCES school_calendar_events(id) ON DELETE SET NULL,
    UNIQUE KEY uq_school_days_year_date (school_year_id, school_date),
    INDEX idx_school_days_period (school_period_id, school_date),
    INDEX idx_school_days_instructional (school_year_id, is_instructional, school_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        $this->addColumn($db,'school_calendar_events','school_year_id','BIGINT NULL');
        $this->addColumn($db,'school_calendar_events','school_period_id','BIGINT NULL');
        $this->addColumn($db,'school_calendar_events','affects_school_day','TINYINT(1) NOT NULL DEFAULT 0');
        $this->addColumn($db,'school_calendar_events','school_day_type',"VARCHAR(30) NULL");
    }

    private function addColumn(\PDO $db,string $table,string $column,string $definition): void
    {
        $stmt=$db->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?');
        $stmt->execute([$table,$column]);
        if((int)$stmt->fetchColumn()===0){$db->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");}
    }

    public function down(): void
    {
        Connection::getInstance()->exec('DROP TABLE IF EXISTS school_days');
    }
};
