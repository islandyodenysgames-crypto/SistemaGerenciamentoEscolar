<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $db = Connection::getInstance();
        $columns = array_column($db->query("SHOW COLUMNS FROM student_monitoring")->fetchAll(), 'Field');
        if (!in_array('case_title', $columns, true)) {
            $db->exec("ALTER TABLE student_monitoring ADD case_title VARCHAR(180) NULL AFTER student_id");
        }
        if (!in_array('problem_code', $columns, true)) {
            $db->exec("ALTER TABLE student_monitoring ADD problem_code VARCHAR(50) NULL AFTER case_title");
        }
        if (!in_array('problem_details', $columns, true)) {
            $db->exec("ALTER TABLE student_monitoring ADD problem_details TEXT NULL AFTER problem_code");
        }
        $db->exec("UPDATE student_monitoring SET case_title=COALESCE(NULLIF(case_title,''), LEFT(reason,180)), problem_code=COALESCE(NULLIF(problem_code,''),'OTHER'), problem_details=COALESCE(problem_details,reason) WHERE case_title IS NULL OR case_title='' OR problem_code IS NULL OR problem_code=''");
        try { $db->exec("CREATE INDEX idx_student_monitoring_student_case ON student_monitoring (student_id,status,problem_code,id)"); } catch (\Throwable $e) { }
    }

    public function down(): void
    {
        $db = Connection::getInstance();
        try { $db->exec("DROP INDEX idx_student_monitoring_student_case ON student_monitoring"); } catch (\Throwable $e) { }
        $columns = array_column($db->query("SHOW COLUMNS FROM student_monitoring")->fetchAll(), 'Field');
        if (in_array('problem_details', $columns, true)) $db->exec("ALTER TABLE student_monitoring DROP COLUMN problem_details");
        if (in_array('problem_code', $columns, true)) $db->exec("ALTER TABLE student_monitoring DROP COLUMN problem_code");
        if (in_array('case_title', $columns, true)) $db->exec("ALTER TABLE student_monitoring DROP COLUMN case_title");
    }
};
