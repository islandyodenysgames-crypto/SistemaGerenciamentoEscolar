<?php

declare(strict_types=1);

/**
 * Verifica invariantes que sustentam contadores, percentuais e indicadores.
 *
 * Uso: php scripts/calculated_data_audit.php [--env=/caminho/.env]
 * É estritamente somente leitura.
 */

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
} else {
    spl_autoload_register(static function (string $class) use ($root): void {
        if (!str_starts_with($class, 'App\\')) return;
        $file = $root . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) require_once $file;
    });
}

$envFile = $root . '/.env';
foreach ($argv as $argument) {
    if (str_starts_with($argument, '--env=')) $envFile = substr($argument, 6);
}
if (!is_file($envFile)) {
    fwrite(STDERR, "Arquivo .env não encontrado: {$envFile}\n");
    exit(2);
}

App\Core\Env::load($envFile);
App\Core\Config::load();
$db = App\Database\Connection::getInstance();
$errors = [];
$warnings = [];
$checks = 0;

$scalar = static function (PDO $db, string $sql): int {
    return (int) $db->query($sql)->fetchColumn();
};
$checkZero = static function (string $label, int $value, array &$target) use (&$checks): void {
    $checks++;
    if ($value !== 0) $target[] = "{$label}: {$value}";
};
$tableExists = static function (PDO $db, string $table): bool {
    $stmt = $db->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=:table');
    $stmt->execute(['table' => $table]);
    return (int) $stmt->fetchColumn() === 1;
};

try {
    $checkZero('Chamadas duplicadas para a mesma turma e data', $scalar($db, "SELECT COUNT(*) FROM (SELECT school_class_id,attendance_date FROM attendance GROUP BY school_class_id,attendance_date HAVING COUNT(*)>1) x"), $errors);
    $checkZero('Itens de frequência órfãos', $scalar($db, 'SELECT COUNT(*) FROM attendance_items ai LEFT JOIN attendance a ON a.id=ai.attendance_id WHERE a.id IS NULL'), $errors);
    $checkZero('Status de frequência inválidos', $scalar($db, "SELECT COUNT(*) FROM attendance_items WHERE status NOT IN ('P','F','FJ','AM','FO')"), $errors);
    $checkZero('Matrículas ativas duplicadas por aluno', $scalar($db, 'SELECT COUNT(*) FROM (SELECT student_id FROM enrollments WHERE active=1 GROUP BY student_id HAVING COUNT(*)>1) x'), $errors);
    $checkZero('Matrículas apontando para aluno ou turma inexistente', $scalar($db, 'SELECT COUNT(*) FROM enrollments e LEFT JOIN students s ON s.id=e.student_id LEFT JOIN school_classes sc ON sc.id=e.school_class_id WHERE s.id IS NULL OR sc.id IS NULL'), $errors);
    $checkZero('Casos de acompanhamento órfãos', $scalar($db, 'SELECT COUNT(*) FROM student_monitoring sm LEFT JOIN students s ON s.id=sm.student_id WHERE s.id IS NULL'), $errors);
    $checkZero('Participantes de acompanhamento órfãos', $scalar($db, 'SELECT COUNT(*) FROM student_monitoring_users smu LEFT JOIN student_monitoring sm ON sm.id=smu.monitoring_id LEFT JOIN users u ON u.id=smu.user_id WHERE sm.id IS NULL OR u.id IS NULL'), $errors);

    if ($tableExists($db, 'student_monitoring_plans')) {
        $checkZero('Casos com mais de um plano compartilhado', $scalar($db, 'SELECT COUNT(*) FROM (SELECT monitoring_id FROM student_monitoring_plans WHERE monitoring_id IS NOT NULL GROUP BY monitoring_id HAVING COUNT(*)>1) x'), $errors);
    }
    if ($tableExists($db, 'school_years')) {
        $checkZero('Mais de um ano letivo ativo', max(0, $scalar($db, "SELECT COUNT(*) FROM school_years WHERE status='ACTIVE' OR is_active=1") - 1), $errors);
    }
    if ($tableExists($db, 'school_periods')) {
        $checkZero('Períodos letivos com datas inválidas', $scalar($db, 'SELECT COUNT(*) FROM school_periods WHERE start_date>end_date'), $errors);
        $checkZero('Períodos letivos sobrepostos no mesmo ano', $scalar($db, 'SELECT COUNT(*) FROM school_periods a JOIN school_periods b ON a.school_year_id=b.school_year_id AND a.id<b.id AND a.start_date<=b.end_date AND b.start_date<=a.end_date'), $warnings);
    }
    if ($tableExists($db, 'school_days')) {
        $checkZero('Dias letivos vinculados a período de outro ano ou fora de sua vigência', $scalar($db, 'SELECT COUNT(*) FROM school_days d JOIN school_periods p ON p.id=d.school_period_id WHERE p.school_year_id<>d.school_year_id OR d.school_date<p.start_date OR d.school_date>p.end_date'), $errors);
        $checkZero('Tipos de dia incoerentes com a marcação de dia letivo', $scalar($db, "SELECT COUNT(*) FROM school_days WHERE (day_type IN ('SCHOOL_DAY','MAKEUP','SATURDAY_SCHOOL') AND is_instructional<>1) OR (day_type NOT IN ('SCHOOL_DAY','MAKEUP','SATURDAY_SCHOOL') AND is_instructional<>0)"), $errors);
    }
    if ($tableExists($db, 'student_intelligence_daily_snapshots')) {
        $checkZero('Snapshots de alunos com percentual de frequência divergente', $scalar($db, 'SELECT COUNT(*) FROM student_intelligence_daily_snapshots WHERE (attendance_records=0 AND attendance_percentage IS NOT NULL) OR (attendance_records>0 AND ABS(attendance_percentage-ROUND(100*presences/attendance_records,2))>0.01)'), $errors);
        $checkZero('Snapshots de alunos com composição de risco inválida', $scalar($db, "SELECT COUNT(*) FROM student_intelligence_daily_snapshots WHERE risk_level NOT IN ('LOW','MODERATE','HIGH','CRITICAL') OR risk_score<0 OR risk_score>100"), $errors);
    }
    if ($tableExists($db, 'school_intelligence_daily_snapshots')) {
        $checkZero('Snapshots escolares cuja distribuição de risco ultrapassa os alunos ativos', $scalar($db, 'SELECT COUNT(*) FROM school_intelligence_daily_snapshots WHERE critical_students+high_risk_students+moderate_risk_students>active_students OR priority_students<>critical_students+high_risk_students+moderate_risk_students'), $errors);
        $checkZero('Snapshots escolares com cobertura fora dos limites', $scalar($db, 'SELECT COUNT(*) FROM school_intelligence_daily_snapshots WHERE monitoring_coverage_percentage<0 OR monitoring_coverage_percentage>100'), $errors);
    }
    if ($tableExists($db, 'school_general_index_snapshots')) {
        $checkZero('Snapshots do índice geral cuja soma não corresponde ao total', $scalar($db, 'SELECT COUNT(*) FROM school_general_index_snapshots WHERE ABS(score-ROUND(frequency_score+occurrence_score+risk_score+monitoring_score+classes_score))>1'), $errors);
    }

    $today = date('Y-m-d');
    $analytics = new App\Services\AttendanceAnalyticsService();
    $central = $analytics->dailyCentral($today);
    $classes = (array) ($central['classes'] ?? []);
    $checks += 4;
    if ((int) ($central['totalClasses'] ?? -1) !== count($classes)) $errors[] = 'Total de turmas da central diverge da listagem.';
    if ((int) ($central['doneClasses'] ?? 0) + (int) ($central['pendingClasses'] ?? 0) !== count($classes)) $errors[] = 'Turmas concluídas + pendentes divergem do total.';
    foreach ($classes as $class) {
        if (!(bool) ($class['has_attendance'] ?? false)) continue;
        $total = (int) ($class['total_students'] ?? 0);
        $sum = (int) ($class['presentes'] ?? 0) + (int) ($class['ranking_absences'] ?? 0) + (int) ($class['attenuated_absences'] ?? 0);
        if ($total !== $sum) $errors[] = 'Composição da chamada diverge na turma ' . (string) ($class['class_name'] ?? $class['class_id'] ?? '?') . '.';
        $expectedFrequency = $total > 0 ? round(100 * (int) ($class['presentes'] ?? 0) / $total, 1) : null;
        if ($expectedFrequency !== null && abs($expectedFrequency - (float) ($class['attendance_percentage'] ?? 0)) > 0.05) $errors[] = 'Frequência calculada diverge na turma ' . (string) ($class['class_name'] ?? '?') . '.';
        $expectedIfe = $total > 0 ? round(100 - 100 * (int) ($class['ranking_absences'] ?? 0) / $total, 2) : null;
        if ($expectedIfe !== null && abs($expectedIfe - (float) ($class['ife_score'] ?? 0)) > 0.005) $errors[] = 'IFE calculado diverge na turma ' . (string) ($class['class_name'] ?? '?') . '.';
    }
} catch (Throwable $exception) {
    $errors[] = 'Auditoria interrompida: ' . $exception->getMessage();
}

echo "Auditoria de dados calculados\n";
echo "Verificações: {$checks}\n";
echo 'Erros: ' . count($errors) . "\n";
echo 'Avisos: ' . count($warnings) . "\n";
foreach ($errors as $error) echo "ERRO: {$error}\n";
foreach ($warnings as $warning) echo "AVISO: {$warning}\n";
exit($errors === [] ? 0 : 1);
