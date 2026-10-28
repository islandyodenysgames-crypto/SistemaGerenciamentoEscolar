<?php

declare(strict_types=1);

if (($argv[1] ?? '') !== '--run') {
    fwrite(STDERR, "Este teste cria e remove um banco descartável. Execute com --run.\n");
    exit(2);
}

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';

use App\Core\Config;
use App\Core\Env;
use App\Database\Connection;
use App\Database\MigrationRepository;
Env::load($root . '/.env');
$host = (string) Env::get('DB_HOST', '127.0.0.1');
$port = (string) Env::get('DB_PORT', '3306');
$user = (string) Env::get('DB_USERNAME', 'root');
$password = (string) Env::get('DB_PASSWORD', '');
$database = 'sge_migrations_' . bin2hex(random_bytes(5));
$server = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

try {
    $server->exec("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $_ENV['DB_DATABASE'] = $_SERVER['DB_DATABASE'] = $database;
    Config::load();
    $db = Connection::getInstance();
    $repository = new MigrationRepository($db);
    $repository->createTableIfNotExists();
    $files = glob($root . '/database/migrations/*.php') ?: [];
    sort($files);
    foreach ($files as $file) {
        $name = basename($file);
        if (!$repository->hasRun($name)) {
            (require $file)->up();
            $repository->log($name);
        }
    }
    foreach ($files as $file) {
        if (!$repository->hasRun(basename($file))) throw new RuntimeException('Migration ausente após reexecução.');
    }
    $requiredTables = ['users', 'students', 'school_classes', 'enrollments', 'attendance', 'school_years', 'school_periods'];
    foreach ($requiredTables as $table) {
        $statement = $db->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=?');
        $statement->execute([$table]);
        if ((int) $statement->fetchColumn() !== 1) throw new RuntimeException("Tabela obrigatória ausente: {$table}");
    }
    $counts = [
        'migrations' => (int) $db->query('SELECT COUNT(*) FROM migrations')->fetchColumn(),
        'tables' => (int) $db->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE()')->fetchColumn(),
        'foreign_keys' => (int) $db->query('SELECT COUNT(*) FROM information_schema.referential_constraints WHERE constraint_schema=DATABASE()')->fetchColumn(),
        'indexes' => (int) $db->query('SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE()')->fetchColumn(),
    ];
    echo 'Migrations MySQL descartáveis: aprovadas ' . json_encode($counts, JSON_UNESCAPED_SLASHES) . PHP_EOL;
} finally {
    if (str_starts_with($database, 'sge_migrations_')) $server->exec("DROP DATABASE IF EXISTS `{$database}`");
}
