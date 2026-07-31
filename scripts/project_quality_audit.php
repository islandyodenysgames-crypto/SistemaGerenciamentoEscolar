<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$failures = 0;
$files = [];
foreach (['app', 'database', 'routes', 'public', 'scripts', 'tests'] as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) if ($file->isFile() && $file->getExtension() === 'php') $files[] = $file->getPathname();
}
foreach ($files as $file) {
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file), $output, $code);
    if ($code !== 0) { $failures++; fwrite(STDERR, "Sintaxe invalida: {$file}\n"); }
}
echo sprintf("Sintaxe PHP: %d arquivo(s), %d falha(s).\n", count($files), $failures);
foreach (['tests/run.php', 'scripts/navigation_audit.php', 'scripts/academic_cycle_audit.php', 'scripts/security_audit.php', 'scripts/operational_readiness_audit.php'] as $script) {
    passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . '/' . $script), $code);
    if ($code !== 0) $failures++;
}
exit($failures === 0 ? 0 : 1);
