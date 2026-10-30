<?php

declare(strict_types=1);

/**
 * Auditoria somente leitura das bases usadas pelos cálculos de tendência.
 * Uso: php scripts/trend_data_audit.php [--env=/caminho/.env]
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

$tableExists = static function (PDO $db, string $table): bool {
    $stmt = $db->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=:table');
    $stmt->execute(['table' => $table]);
    return (int) $stmt->fetchColumn() === 1;
};
$checkZero = static function (string $label, int $value, array &$target) use (&$checks): void {
    $checks++;
    if ($value !== 0) $target[] = "{$label}: {$value}";
};
$scalar = static fn(PDO $db, string $sql): int => (int) $db->query($sql)->fetchColumn();

if ($tableExists($db, 'student_intelligence_daily_snapshots')) {
    $checkZero('Snapshots futuros de alunos', $scalar($db, 'SELECT COUNT(*) FROM student_intelligence_daily_snapshots WHERE snapshot_date>CURDATE()'), $errors);
    $checkZero('Snapshots duplicados por aluno e dia', $scalar($db, 'SELECT COUNT(*) FROM (SELECT student_id,snapshot_date FROM student_intelligence_daily_snapshots GROUP BY student_id,snapshot_date HAVING COUNT(*)>1) x'), $errors);
    $checkZero('Percentuais de frequência inválidos nos snapshots', $scalar($db, 'SELECT COUNT(*) FROM student_intelligence_daily_snapshots WHERE attendance_percentage<0 OR attendance_percentage>100'), $errors);
    $checkZero('Pontuações de risco inválidas nos snapshots', $scalar($db, 'SELECT COUNT(*) FROM student_intelligence_daily_snapshots WHERE risk_score<0 OR risk_score>100'), $errors);
    $checkZero('Cobertura agregada superior aos alunos prioritários', $scalar($db, "SELECT COUNT(*) FROM (SELECT class_id,snapshot_date,SUM(risk_level IN ('MODERATE','HIGH','CRITICAL')) priority_students,SUM(risk_level IN ('MODERATE','HIGH','CRITICAL') AND has_active_monitoring=1 AND active_followers>0) covered_students FROM student_intelligence_daily_snapshots WHERE class_id IS NOT NULL GROUP BY class_id,snapshot_date HAVING covered_students>priority_students) x"), $errors);

    $checks++;
    $windowChanges = $scalar($db, 'SELECT GREATEST(COUNT(DISTINCT analysis_window_days)-1,0) FROM student_intelligence_daily_snapshots');
    if ($windowChanges > 0) $warnings[] = 'Há snapshots de janelas de análise diferentes; o cálculo usa somente a janela mais recente.';
}

if ($tableExists($db, 'school_general_index_snapshots')) {
    $checkZero('Snapshots futuros do índice geral', $scalar($db, 'SELECT COUNT(*) FROM school_general_index_snapshots WHERE snapshot_date>CURDATE()'), $errors);
    $checkZero('Índices gerais fora da escala de 0 a 100', $scalar($db, 'SELECT COUNT(*) FROM school_general_index_snapshots WHERE score<0 OR score>100'), $errors);
}

$checks += 3;
$window = 30;
$currentEnd = new DateTimeImmutable('today');
$currentStart = $currentEnd->modify('-' . ($window - 1) . ' days');
$previousEnd = $currentEnd->modify('-' . $window . ' days');
$previousStart = $currentEnd->modify('-' . (($window * 2) - 1) . ' days');
if ((int) $currentStart->diff($currentEnd)->days + 1 !== $window) $errors[] = 'Janela atual não possui a duração configurada.';
if ((int) $previousStart->diff($previousEnd)->days + 1 !== $window) $errors[] = 'Janela anterior não possui a duração configurada.';
if ($previousEnd->modify('+1 day')->format('Y-m-d') !== $currentStart->format('Y-m-d')) $errors[] = 'Janelas comparativas possuem lacuna ou sobreposição.';

echo "Auditoria de tendências\n";
echo 'Verificações: ' . $checks . "\n";
echo 'Erros: ' . count($errors) . "\n";
foreach ($errors as $error) echo "- ERRO: {$error}\n";
echo 'Avisos: ' . count($warnings) . "\n";
foreach ($warnings as $warning) echo "- AVISO: {$warning}\n";
exit($errors === [] ? 0 : 1);
