<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $db = Connection::getInstance();

        $db->exec("CREATE TABLE IF NOT EXISTS student_intelligence_daily_snapshots (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            snapshot_date DATE NOT NULL,
            student_id BIGINT NOT NULL,
            class_id BIGINT NULL,
            class_name VARCHAR(150) NULL,
            analysis_window_days INT NOT NULL DEFAULT 30,
            risk_level VARCHAR(20) NOT NULL,
            risk_score INT NOT NULL DEFAULT 0,
            attendance_records INT NOT NULL DEFAULT 0,
            presences INT NOT NULL DEFAULT 0,
            attendance_percentage DECIMAL(5,2) NULL,
            unjustified_absences INT NOT NULL DEFAULT 0,
            attenuated_absences INT NOT NULL DEFAULT 0,
            total_occurrences INT NOT NULL DEFAULT 0,
            serious_occurrences INT NOT NULL DEFAULT 0,
            open_occurrences INT NOT NULL DEFAULT 0,
            has_active_monitoring TINYINT(1) NOT NULL DEFAULT 0,
            active_followers INT NOT NULL DEFAULT 0,
            pending_recommendations INT NOT NULL DEFAULT 0,
            latest_action_date DATE NULL,
            days_without_action INT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_student_intelligence_daily (snapshot_date, student_id),
            KEY idx_student_intelligence_daily_student_date (student_id, snapshot_date),
            KEY idx_student_intelligence_daily_class_date (class_id, snapshot_date),
            KEY idx_student_intelligence_daily_risk (snapshot_date, risk_level),
            CONSTRAINT fk_student_intelligence_daily_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            CONSTRAINT fk_student_intelligence_daily_class FOREIGN KEY (class_id) REFERENCES school_classes(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $db->exec("CREATE TABLE IF NOT EXISTS school_intelligence_daily_snapshots (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            snapshot_date DATE NOT NULL,
            analysis_window_days INT NOT NULL DEFAULT 30,
            active_students INT NOT NULL DEFAULT 0,
            priority_students INT NOT NULL DEFAULT 0,
            critical_students INT NOT NULL DEFAULT 0,
            high_risk_students INT NOT NULL DEFAULT 0,
            moderate_risk_students INT NOT NULL DEFAULT 0,
            attention_classes INT NOT NULL DEFAULT 0,
            combined_risk_students INT NOT NULL DEFAULT 0,
            stale_critical_occurrences INT NOT NULL DEFAULT 0,
            unjustified_absences INT NOT NULL DEFAULT 0,
            students_with_unjustified_absence INT NOT NULL DEFAULT 0,
            total_occurrences INT NOT NULL DEFAULT 0,
            serious_occurrences INT NOT NULL DEFAULT 0,
            open_occurrences INT NOT NULL DEFAULT 0,
            monitoring_coverage_percentage INT NOT NULL DEFAULT 0,
            covered_cases INT NOT NULL DEFAULT 0,
            without_follower INT NOT NULL DEFAULT 0,
            without_recent_action INT NOT NULL DEFAULT 0,
            pending_recommendations INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_school_intelligence_daily_date (snapshot_date),
            KEY idx_school_intelligence_daily_date (snapshot_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function down(): void
    {
        $db = Connection::getInstance();
        $db->exec("DROP TABLE IF EXISTS school_intelligence_daily_snapshots");
        $db->exec("DROP TABLE IF EXISTS student_intelligence_daily_snapshots");
    }
};
