<?php

declare(strict_types=1);

/**
 * Auditoria estática de navegação.
 * Uso: php scripts/navigation_audit.php
 */

$root = dirname(__DIR__);
$routeFile = $root . '/routes/web.php';
$routeSource = file_get_contents($routeFile);

if ($routeSource === false) {
    fwrite(STDERR, "Não foi possível ler routes/web.php.\n");
    exit(2);
}

preg_match_all(
    '/\\$router->(get|post)\\(\\s*[\'\"]([^\'\"]+)[\'\"]/i',
    $routeSource,
    $routeMatches,
    PREG_SET_ORDER
);

$routes = [];
foreach ($routeMatches as $match) {
    $method = strtoupper($match[1]);
    $path = normalizeRoute($match[2]);
    $routes[$path][$method] = true;
}

$references = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root . '/app', FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $source = file_get_contents($file->getPathname());
    if ($source === false) {
        continue;
    }

    preg_match_all(
        '/(?:base_url|url)\\(\\s*[\'\"]([^\'\"]+)[\'\"]/i',
        $source,
        $matches,
        PREG_OFFSET_CAPTURE
    );

    foreach ($matches[1] as [$value, $offset]) {
        $path = normalizeRoute((string) $value);
        if (isIgnoredReference($path)) {
            continue;
        }

        $line = substr_count(substr($source, 0, (int) $offset), "\n") + 1;
        $references[] = [
            'path' => $path,
            'file' => str_replace($root . '/', '', $file->getPathname()),
            'line' => $line,
        ];
    }
}

$missing = [];
foreach ($references as $reference) {
    if (!isset($routes[$reference['path']])) {
        $missing[] = $reference;
    }
}

$duplicates = [];
foreach ($routeMatches as $match) {
    $key = strtoupper($match[1]) . ' ' . normalizeRoute($match[2]);
    $duplicates[$key] = ($duplicates[$key] ?? 0) + 1;
}
$duplicates = array_filter($duplicates, static fn (int $count): bool => $count > 1);

echo "Auditoria de navegação\n";
echo "Rotas registradas: " . count($routes) . "\n";
echo "Referências internas analisadas: " . count($references) . "\n";
echo "Rotas duplicadas: " . count($duplicates) . "\n";
echo "Destinos sem rota: " . count($missing) . "\n";

foreach ($duplicates as $route => $count) {
    echo "[DUPLICADA] {$route} ({$count}x)\n";
}

foreach ($missing as $item) {
    echo "[SEM ROTA] {$item['path']} em {$item['file']}:{$item['line']}\n";
}

exit(($missing !== [] || $duplicates !== []) ? 1 : 0);

function normalizeRoute(string $route): string
{
    $route = trim($route);
    $route = preg_split('/[?#]/', $route, 2)[0] ?? '';
    $route = '/' . trim($route, '/');

    return $route === '//' ? '/' : $route;
}

function isIgnoredReference(string $route): bool
{
    return $route === '/'
        || str_starts_with($route, '/assets/')
        || str_starts_with($route, '/storage/')
        || str_starts_with($route, '/http:')
        || str_starts_with($route, '/https:');
}
