<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$checks = [
    ['app/Controllers/AuthController.php', 'session_regenerate_id(true)', 'O login nao regenera a sessao.'],
    ['app/Controllers/DataMaintenanceController.php', 'Csrf::validate', 'A manutencao de dados nao valida CSRF.'],
    ['app/Controllers/DataMaintenanceController.php', 'Permissions::SETTINGS_MANAGE', 'A manutencao de dados nao exige permissao administrativa.'],
    ['app/Controllers/SchoolYearController.php', 'Csrf::validate', 'O ciclo academico nao valida CSRF.'],
    ['app/Services/SchoolDataMaintenanceService.php', 'manifest.json', 'O backup nao possui manifesto de compatibilidade.'],
];
foreach ($checks as [$file, $needle, $message]) {
    $source = is_file($root . '/' . $file) ? (string) file_get_contents($root . '/' . $file) : '';
    if (!str_contains($source, $needle)) $errors[] = $message;
}
$view = (string) file_get_contents($root . '/app/Views/pages/settings/data-maintenance.php');
if (substr_count($view, 'name="_token"') !== 3) $errors[] = 'Nem todos os formularios criticos enviam token CSRF.';
$routes = (string) file_get_contents($root . '/routes/web.php');
foreach (['/configuracoes/dados/restaurar', '/configuracoes/dados/limpar', '/configuracoes/ano-letivo/encerrar-seguro'] as $route) {
    if (!preg_match('/\$router->post\(\s*[\'\"]' . preg_quote($route, '/') . '[\'\"]/', $routes)) $errors[] = "Rota critica nao esta restrita a POST: {$route}";
}
echo 'Auditoria de seguranca: ' . count($errors) . " erro(s).\n";
foreach ($errors as $error) fwrite(STDERR, "ERRO: {$error}\n");
exit($errors === [] ? 0 : 1);
