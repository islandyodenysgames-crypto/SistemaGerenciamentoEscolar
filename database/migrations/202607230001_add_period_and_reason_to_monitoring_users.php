<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $db = Connection::getInstance();
        $columns = $db->query("SHOW COLUMNS FROM student_monitoring_users")->fetchAll();
        $names = array_map(static fn(array $column): string => (string) $column['Field'], $columns);

        if (!in_array('start_date', $names, true)) {
            $db->exec("ALTER TABLE student_monitoring_users ADD COLUMN start_date DATE NULL AFTER assigned_by");
        }
        if (!in_array('end_date', $names, true)) {
            $db->exec("ALTER TABLE student_monitoring_users ADD COLUMN end_date DATE NULL AFTER start_date");
        }
        if (!in_array('reason', $names, true)) {
            $db->exec("ALTER TABLE student_monitoring_users ADD COLUMN reason TEXT NULL AFTER end_date");
        }

        $db->exec("
            UPDATE student_monitoring_users smu
            INNER JOIN student_monitoring sm ON sm.id = smu.monitoring_id
            SET smu.start_date = COALESCE(smu.start_date, sm.start_date),
                smu.end_date = COALESCE(smu.end_date, sm.end_date),
                smu.reason = COALESCE(smu.reason, sm.reason)
        ");
    }

    public function down(): void
    {
        $db = Connection::getInstance();
        $columns = $db->query("SHOW COLUMNS FROM student_monitoring_users")->fetchAll();
        $names = array_map(static fn(array $column): string => (string) $column['Field'], $columns);

        if (in_array('reason', $names, true)) {
            $db->exec("ALTER TABLE student_monitoring_users DROP COLUMN reason");
        }
        if (in_array('end_date', $names, true)) {
            $db->exec("ALTER TABLE student_monitoring_users DROP COLUMN end_date");
        }
        if (in_array('start_date', $names, true)) {
            $db->exec("ALTER TABLE student_monitoring_users DROP COLUMN start_date");
        }
    }
};
