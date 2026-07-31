<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $db = Connection::getInstance();
        $columns = array_column($db->query("SHOW COLUMNS FROM student_monitoring_plans")->fetchAll(), 'Field');

        if (!in_array('monitoring_id', $columns, true)) {
            $db->exec("ALTER TABLE student_monitoring_plans ADD COLUMN monitoring_id BIGINT NULL AFTER id");
        }

        // Vincula os planos antigos ao caso ao qual o vínculo pertencia.
        $db->exec("UPDATE student_monitoring_plans smp
            INNER JOIN student_monitoring_users smu ON smu.id = smp.monitoring_user_id
            SET smp.monitoring_id = smu.monitoring_id
            WHERE smp.monitoring_id IS NULL");

        // O plano agora é único por caso. Mantém a versão mais recentemente atualizada
        // e remove somente as cópias legadas criadas para os demais participantes.
        $db->exec("DELETE older
            FROM student_monitoring_plans older
            INNER JOIN student_monitoring_plans newer
                ON newer.monitoring_id = older.monitoring_id
               AND newer.monitoring_id IS NOT NULL
               AND (
                    newer.updated_at > older.updated_at
                    OR (newer.updated_at = older.updated_at AND newer.id > older.id)
               )");

        try { $db->exec("ALTER TABLE student_monitoring_plans DROP INDEX uq_student_monitoring_plan_link"); } catch (\Throwable $e) { }
        try { $db->exec("ALTER TABLE student_monitoring_plans ADD UNIQUE KEY uq_student_monitoring_plan_case (monitoring_id)"); } catch (\Throwable $e) { }
        try { $db->exec("ALTER TABLE student_monitoring_plans ADD CONSTRAINT fk_student_monitoring_plan_case FOREIGN KEY (monitoring_id) REFERENCES student_monitoring(id) ON DELETE CASCADE"); } catch (\Throwable $e) { }

        // Mantém o vínculo legado apenas para compatibilidade e auditoria da migração.
        // Novos planos são gravados diretamente no caso.
        try { $db->exec("ALTER TABLE student_monitoring_plans MODIFY monitoring_user_id BIGINT NULL"); } catch (\Throwable $e) { }
        $db->exec("UPDATE student_monitoring_plans SET monitoring_user_id=NULL WHERE monitoring_id IS NOT NULL");

        $actionColumns = array_column($db->query("SHOW COLUMNS FROM student_monitoring_actions")->fetchAll(), 'Field');
        if (!in_array('monitoring_id', $actionColumns, true)) {
            $db->exec("ALTER TABLE student_monitoring_actions ADD COLUMN monitoring_id BIGINT NULL AFTER id");
        }
        $db->exec("UPDATE student_monitoring_actions sma
            INNER JOIN student_monitoring_users smu ON smu.id = sma.monitoring_user_id
            SET sma.monitoring_id = smu.monitoring_id
            WHERE sma.monitoring_id IS NULL");
        try { $db->exec("CREATE INDEX idx_monitoring_actions_case_date ON student_monitoring_actions (monitoring_id, action_date)"); } catch (\Throwable $e) { }
        try { $db->exec("ALTER TABLE student_monitoring_actions ADD CONSTRAINT fk_monitoring_action_case FOREIGN KEY (monitoring_id) REFERENCES student_monitoring(id) ON DELETE CASCADE"); } catch (\Throwable $e) { }
        try { $db->exec("ALTER TABLE student_monitoring_actions MODIFY monitoring_user_id BIGINT NULL"); } catch (\Throwable $e) { }
        $db->exec("UPDATE student_monitoring_actions SET monitoring_user_id=NULL WHERE monitoring_id IS NOT NULL");
    }

    public function down(): void
    {
        $db = Connection::getInstance();
        try { $db->exec("ALTER TABLE student_monitoring_actions DROP FOREIGN KEY fk_monitoring_action_case"); } catch (\Throwable $e) { }
        try { $db->exec("DROP INDEX idx_monitoring_actions_case_date ON student_monitoring_actions"); } catch (\Throwable $e) { }
        try { $db->exec("ALTER TABLE student_monitoring_actions MODIFY monitoring_user_id BIGINT NOT NULL"); } catch (\Throwable $e) { }
        $actionColumns = array_column($db->query("SHOW COLUMNS FROM student_monitoring_actions")->fetchAll(), 'Field');
        if (in_array('monitoring_id', $actionColumns, true)) {
            $db->exec("ALTER TABLE student_monitoring_actions DROP COLUMN monitoring_id");
        }
        try { $db->exec("ALTER TABLE student_monitoring_plans DROP FOREIGN KEY fk_student_monitoring_plan_case"); } catch (\Throwable $e) { }
        try { $db->exec("ALTER TABLE student_monitoring_plans DROP INDEX uq_student_monitoring_plan_case"); } catch (\Throwable $e) { }
        try { $db->exec("ALTER TABLE student_monitoring_plans MODIFY monitoring_user_id BIGINT NOT NULL"); } catch (\Throwable $e) { }
        try { $db->exec("ALTER TABLE student_monitoring_plans ADD UNIQUE KEY uq_student_monitoring_plan_link (monitoring_user_id)"); } catch (\Throwable $e) { }
        $columns = array_column($db->query("SHOW COLUMNS FROM student_monitoring_plans")->fetchAll(), 'Field');
        if (in_array('monitoring_id', $columns, true)) {
            $db->exec("ALTER TABLE student_monitoring_plans DROP COLUMN monitoring_id");
        }
    }
};
