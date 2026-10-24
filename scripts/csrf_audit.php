<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$viewFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/app/Views'));
$forms = 0;
$explicit = 0;
$externalActions = [];

foreach ($viewFiles as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') continue;
    $source = (string) file_get_contents($file->getPathname());
    preg_match_all('/<form\b[^>]*\bmethod\s*=\s*(?:"post"|\'post\'|post(?=\s|\/?>|$))[^>]*>(.*?)<\/form>/is', $source, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $forms++;
        if (preg_match('/\bname\s*=\s*(?:"_token"|\'_token\'|_token(?=\s|\/?>))/i', $match[1])) $explicit++;
        if (preg_match('/\baction\s*=\s*["\']https?:\/\//i', $match[0])) $externalActions[] = $file->getPathname();
    }
}

$routes = (string) file_get_contents($root . '/routes/web.php');
preg_match_all('/\$router->post\s*\(/', $routes, $postRoutes);
$router = (string) file_get_contents($root . '/app/Core/Router.php');
$view = (string) file_get_contents($root . '/app/Core/View.php');
$client = (string) file_get_contents($root . '/public/assets/js/core/csrf.js');
$errors = [];
if (!str_contains($router, "\$method === 'POST' && !Csrf::validate")) $errors[] = 'Router não possui validação central de POST.';
if (!str_contains($view, 'Csrf::injectIntoHtml')) $errors[] = 'View não possui proteção de fallback para formulários.';
if (!str_contains($client, 'X-CSRF-Token')) $errors[] = 'Cliente não envia cabeçalho CSRF.';
if ($externalActions !== []) $errors[] = 'Foram encontrados formulários POST com action externa.';

echo "Auditoria CSRF\n";
echo 'Rotas POST: ' . count($postRoutes[0]) . "\n";
echo "Formulários POST encontrados nas views: {$forms}\n";
echo "Formulários com token explícito: {$explicit}\n";
echo 'Cobertura central/fallback: ' . ($errors === [] ? 'aprovada' : 'reprovada') . "\n";
foreach ($errors as $error) fwrite(STDERR, "ERRO: {$error}\n");
exit($errors === [] ? 0 : 1);
