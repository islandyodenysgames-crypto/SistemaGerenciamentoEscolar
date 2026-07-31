<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $db = Connection::getInstance();
        $db->exec("CREATE TABLE IF NOT EXISTS student_monitoring_events (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            monitoring_id BIGINT NOT NULL,
            event_type VARCHAR(40) NOT NULL,
            target_user_id BIGINT NULL,
            performed_by BIGINT NOT NULL,
            active_followers_count INT NOT NULL DEFAULT 0,
            details TEXT NULL,
            occurred_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_monitoring_events_timeline (monitoring_id, occurred_at, id),
            KEY idx_monitoring_events_type (event_type),
            CONSTRAINT fk_monitoring_event_monitoring FOREIGN KEY (monitoring_id) REFERENCES student_monitoring(id) ON DELETE CASCADE,
            CONSTRAINT fk_monitoring_event_target FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE SET NULL,
            CONSTRAINT fk_monitoring_event_actor FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Gera uma linha do tempo inicial para vínculos já existentes, sem alterar os dados originais.
        $db->exec("INSERT INTO student_monitoring_events
            (monitoring_id,event_type,target_user_id,performed_by,active_followers_count,details,occurred_at,created_at)
            SELECT smu.monitoring_id,'PARTICIPANT_ADDED',smu.user_id,COALESCE(smu.assigned_by,sm.created_by),0,
                   'Evento reconstruído a partir do vínculo existente.',
                   COALESCE(smu.assigned_at,smu.created_at,sm.created_at),NOW()
            FROM student_monitoring_users smu
            INNER JOIN student_monitoring sm ON sm.id=smu.monitoring_id
            WHERE NOT EXISTS (
                SELECT 1 FROM student_monitoring_events sme
                WHERE sme.monitoring_id=smu.monitoring_id
                  AND sme.event_type='PARTICIPANT_ADDED'
                  AND sme.target_user_id=smu.user_id
                  AND sme.occurred_at=COALESCE(smu.assigned_at,smu.created_at,sm.created_at)
            )");

        $db->exec("INSERT INTO student_monitoring_events
            (monitoring_id,event_type,target_user_id,performed_by,active_followers_count,details,occurred_at,created_at)
            SELECT smu.monitoring_id,'PARTICIPANT_REMOVED',smu.user_id,COALESCE(smu.assigned_by,sm.created_by),0,
                   'Encerramento reconstruído a partir do vínculo existente.',smu.ended_at,NOW()
            FROM student_monitoring_users smu
            INNER JOIN student_monitoring sm ON sm.id=smu.monitoring_id
            WHERE smu.status='ENDED' AND smu.ended_at IS NOT NULL
              AND NOT EXISTS (
                SELECT 1 FROM student_monitoring_events sme
                WHERE sme.monitoring_id=smu.monitoring_id
                  AND sme.event_type='PARTICIPANT_REMOVED'
                  AND sme.target_user_id=smu.user_id
                  AND sme.occurred_at=smu.ended_at
            )");
    }

    public function down(): void
    {
        Connection::getInstance()->exec("DROP TABLE IF EXISTS student_monitoring_events");
    }
};
