<?php

declare(strict_types=1);

/**
 * Auditoria do ciclo acadêmico.
 *
 * Uso estático: php scripts/academic_cycle_audit.php
 * Com banco:    php scripts/academic_cycle_audit.php --database
 */

$root = dirname(__DIR__);
$errors = [];
$warnings = [];

$requiredFiles = [
    'app/Controllers/SchoolYearController.php',
    'app/Services/SchoolYearService.php',
    'app/Services/SchoolPeriodService.php',
    'app/Services/SchoolDayService.php',
    'app/Services/AcademicLifecycleService.php',
    'app/Services/CurrentAcademicContextService.php',
    'app/Repositories/SchoolYearRepository.php',
    'app/Repositories/SchoolPeriodRepository.php',
    'app/Repositories/SchoolDayRepository.php',
    'app/Repositories/AcademicLifecycleRepository.php',
    'database/migrations/202608120001_create_school_years_table.php',
    'database/migrations/202608120002_create_school_periods_table.php',
    'database/migrations/202608120003_create_school_days_and_integrate_calendar.php',
    'database/migrations/202608120004_create_academic_closures_snapshots_and_audit.php',
    'database/migrations/202608130001_enforce_single_active_school_year.php',
];

foreach ($requiredFiles as $file) {
    if (!is_file($root . '/' . $file)) {
        $errors[] = "Arquivo obrigatório ausente: {$file}";
    }
}

$routes = (string) file_get_contents($root . '/routes/web.php');
foreach ([
    '/configuracoes/ano-letivo',
    '/configuracoes/ano-letivo/periodos/salvar',
    '/configuracoes/ano-letivo/dias/gerar',
    '/configuracoes/ano-letivo/periodos/fechar',
    '/configuracoes/ano-letivo/encerrar-seguro',
    '/configuracoes/ano-letivo/assistente',
] as $route) {
    if (!str_contains($routes, "'{$route}'")) {
        $errors[] = "Rota acadêmica ausente: {$route}";
    }
}

$controller = (string) file_get_contents($root . '/app/Controllers/SchoolYearController.php');
if (!str_contains($controller, 'Csrf::validate')) {
    $errors[] = 'As operações acadêmicas não estão protegidas por validação CSRF.';
}

$lifecycle = (string) file_get_contents($root . '/app/Services/AcademicLifecycleService.php');
if (substr_count($lifecycle, '->transaction(') < 4) {
    $errors[] = 'Nem todas as operações críticas do ciclo acadêmico estão transacionais.';
}

if (in_array('--database', $argv, true)) {
    $autoload = $root . '/vendor/autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
    } else {
        spl_autoload_register(static function (string $class) use ($root): void {
            if (!str_starts_with($class, 'App\\')) {
                return;
            }
            $file = $root . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
            if (is_file($file)) {
                require_once $file;
            }
        });
    }

    $envFile = $root . '/.env';
    foreach ($argv as $argument) {
        if (str_starts_with($argument, '--env=')) {
            $envFile = substr($argument, 6);
        }
    }
    if (!is_file($envFile)) {
        $errors[] = 'Arquivo .env não encontrado. Informe --env=C:\\caminho\\.env para auditar o banco.';
    } else {
        App\Core\Env::load($envFile);
    }
    App\Core\Config::load();

    try {
        if (!is_file($envFile)) {
            throw new RuntimeException('Configuração do banco indisponível.');
        }
        $pdo = new PDO(
            sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                App\Core\Config::get('database.host'),
                App\Core\Config::get('database.port'),
                App\Core\Config::get('database.database')
            ),
            App\Core\Config::get('database.username'),
            App\Core\Config::get('database.password'),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $activeYears = (int) $pdo->query('SELECT COUNT(*) FROM school_years WHERE is_active = 1')->fetchColumn();
        if ($activeYears > 1) {
            $errors[] = "Existem {$activeYears} anos letivos ativos; o máximo permitido é um.";
        }

        $outsideYear = (int) $pdo->query(<<<'SQL'
SELECT COUNT(*)
FROM school_periods p
JOIN school_years y ON y.id = p.school_year_id
WHERE p.start_date < y.start_date OR p.end_date > y.end_date OR p.end_date < p.start_date
SQL)->fetchColumn();
        if ($outsideYear > 0) {
            $errors[] = "Existem {$outsideYear} períodos fora da vigência do respectivo ano.";
        }

        $overlaps = (int) $pdo->query(<<<'SQL'
SELECT COUNT(*)
FROM school_periods a
JOIN school_periods b
  ON b.school_year_id = a.school_year_id
 AND b.id > a.id
 AND b.start_date <= a.end_date
 AND b.end_date >= a.start_date
SQL)->fetchColumn();
        if ($overlaps > 0) {
            $errors[] = "Foram encontradas {$overlaps} sobreposições entre períodos letivos.";
        }

        $closedWithoutSnapshot = (int) $pdo->query(<<<'SQL'
SELECT COUNT(*)
FROM school_years y
WHERE y.status = 'CLOSED'
  AND NOT EXISTS (
      SELECT 1 FROM academic_snapshots s
      WHERE s.school_year_id = y.id AND s.snapshot_type = 'YEAR_CLOSE'
  )
SQL)->fetchColumn();
        if ($closedWithoutSnapshot > 0) {
            $warnings[] = "Existem {$closedWithoutSnapshot} anos encerrados sem snapshot final.";
        }
    } catch (Throwable $exception) {
        $errors[] = 'Não foi possível auditar o banco: ' . $exception->getMessage();
    }
}

echo "Auditoria do ciclo acadêmico\n";
echo 'Erros: ' . count($errors) . "\n";
echo 'Avisos: ' . count($warnings) . "\n";
foreach ($errors as $message) {
    echo "[ERRO] {$message}\n";
}
foreach ($warnings as $message) {
    echo "[AVISO] {$message}\n";
}

exit($errors === [] ? 0 : 1);
