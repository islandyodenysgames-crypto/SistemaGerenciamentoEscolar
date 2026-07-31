<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $db = Connection::getInstance();

        $participantColumns = array_column($db->query("SHOW COLUMNS FROM student_monitoring_users")->fetchAll(), 'Field');
        if (!in_array('internal_notes', $participantColumns, true)) {
            $db->exec("ALTER TABLE student_monitoring_users ADD COLUMN internal_notes TEXT NULL AFTER reason");
        }
        $db->exec("UPDATE student_monitoring_users SET internal_notes=reason WHERE internal_notes IS NULL AND reason IS NOT NULL");
        $db->exec("UPDATE student_monitoring_users SET reason=NULL");

        $extensionColumns = array_column($db->query("SHOW COLUMNS FROM student_monitoring_extensions")->fetchAll(), 'Field');
        if (!in_array('monitoring_id', $extensionColumns, true)) {
            $db->exec("ALTER TABLE student_monitoring_extensions ADD COLUMN monitoring_id BIGINT NULL AFTER id");
        }
        $db->exec("UPDATE student_monitoring_extensions sme INNER JOIN student_monitoring_users smu ON smu.id=sme.monitoring_user_id SET sme.monitoring_id=smu.monitoring_id WHERE sme.monitoring_id IS NULL");
        try { $db->exec("CREATE INDEX idx_monitoring_extension_case ON student_monitoring_extensions (monitoring_id, created_at)"); } catch (\Throwable $e) { }
        try { $db->exec("ALTER TABLE student_monitoring_extensions ADD CONSTRAINT fk_monitoring_extension_case FOREIGN KEY (monitoring_id) REFERENCES student_monitoring(id) ON DELETE CASCADE"); } catch (\Throwable $e) { }
        try { $db->exec("ALTER TABLE student_monitoring_extensions MODIFY monitoring_user_id BIGINT NULL"); } catch (\Throwable $e) { }
        $db->exec("UPDATE student_monitoring_extensions SET monitoring_user_id=NULL WHERE monitoring_id IS NOT NULL");
    }

    public function down(): void
    {
        $db = Connection::getInstance();
        try { $db->exec("ALTER TABLE student_monitoring_extensions DROP FOREIGN KEY fk_monitoring_extension_case"); } catch (\Throwable $e) { }
        try { $db->exec("DROP INDEX idx_monitoring_extension_case ON student_monitoring_extensions"); } catch (\Throwable $e) { }
        $columns = array_column($db->query("SHOW COLUMNS FROM student_monitoring_extensions")->fetchAll(), 'Field');
        if (in_array('monitoring_id', $columns, true)) $db->exec("ALTER TABLE student_monitoring_extensions DROP COLUMN monitoring_id");
        $participantColumns = array_column($db->query("SHOW COLUMNS FROM student_monitoring_users")->fetchAll(), 'Field');
        if (in_array('internal_notes', $participantColumns, true)) $db->exec("ALTER TABLE student_monitoring_users DROP COLUMN internal_notes");
    }
};
