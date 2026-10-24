<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$uploadServices = [
    'app/Services/FileUploadService.php',
    'app/Services/ProfilePhotoService.php',
    'app/Services/SchoolClassService.php',
    'app/Services/NoticeMediaService.php',
    'app/Services/OccurrenceAttachmentService.php',
    'app/Services/Occurrence/ActionAttachmentService.php',
    'app/Services/Monitoring/StudentMonitoringActionAttachmentService.php',
];
foreach ($uploadServices as $file) {
    $source = (string) file_get_contents($root . '/' . $file);
    if (!str_contains($source, 'FileSecurityService::')) $errors[] = "Fluxo sem validação central: {$file}";
}
$logo = (string) file_get_contents($root . '/app/Services/FileUploadService.php');
if (str_contains($logo, "'svg'")) $errors[] = 'Upload de logotipo ainda aceita SVG.';
$htaccess = (string) file_get_contents($root . '/public/uploads/.htaccess');
if (!str_contains($htaccess, 'php') || !str_contains($htaccess, 'occurrences')) $errors[] = 'Regras Apache para uploads estão incompletas.';
$routes = (string) file_get_contents($root . '/routes/web.php');
foreach (['/arquivos/visualizar', '/painel-tv/autorizar'] as $route) if (!str_contains($routes, $route)) $errors[] = "Rota de segurança ausente: {$route}";
$tv = (string) file_get_contents($root . '/app/Controllers/TvPanelController.php');
if (!str_contains($tv, 'TvPanelAccessService') || !str_contains($tv, 'privateDisplayHeaders')) $errors[] = 'Painel TV sem controle de dispositivo/cache.';

echo 'Auditoria de arquivos e Painel TV: ' . count($errors) . " erro(s).\n";
foreach ($errors as $error) fwrite(STDERR, "ERRO: {$error}\n");
exit($errors === [] ? 0 : 1);
