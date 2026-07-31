<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$warnings = [];
foreach (['pdo', 'fileinfo', 'zip'] as $extension) {
    if (!extension_loaded($extension)) $warnings[] = "Extensao PHP recomendada nao carregada: {$extension}";
}
foreach (['public/uploads', 'database/migrations'] as $path) {
    if (!is_dir($root . '/' . $path)) $errors[] = "Diretorio obrigatorio ausente: {$path}";
}
$migrations = glob($root . '/database/migrations/*.php') ?: [];
if ($migrations === []) $errors[] = 'Nenhuma migracao foi encontrada.';
$latest = $root . '/database/migrations/202608130001_enforce_single_active_school_year.php';
if (!is_file($latest)) $errors[] = 'A protecao de ano letivo ativo nao foi encontrada.';
echo sprintf("Prontidao operacional: %d erro(s), %d aviso(s).\n", count($errors), count($warnings));
foreach ($warnings as $warning) echo "AVISO: {$warning}\n";
foreach ($errors as $error) fwrite(STDERR, "ERRO: {$error}\n");
exit($errors === [] ? 0 : 1);
