<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];
$required = [
    '.env.production.example',
    'deploy/oracle-cloud/nginx/sge.conf.example',
    'deploy/oracle-cloud/php/99-sge.ini',
    'deploy/oracle-cloud/mysql/sge.cnf.example',
    'deploy/oracle-cloud/mysql/create-users.sql.example',
    'deploy/oracle-cloud/scripts/health-check.sh',
    'deploy/oracle-cloud/scripts/backup-external.sh',
    'docs/GRUPO_5_ORACLE_CLOUD_ALWAYS_FREE.md',
];
foreach ($required as $relative) {
    if (!is_file($root . '/' . $relative)) $failures[] = "Arquivo ausente: {$relative}";
}

$nginx = (string) @file_get_contents($root . '/deploy/oracle-cloud/nginx/sge.conf.example');
foreach (['root /var/www/sge/current/public;', 'try_files $uri $uri/ /index.php?$query_string;', 'location = /index.php', 'php8.3-fpm.sock', 'location ~ \.php$ { return 404; }', 'Content-Security-Policy-Report-Only'] as $needle) {
    if (!str_contains($nginx, $needle)) $failures[] = "Nginx sem requisito: {$needle}";
}
if (!preg_match('#location ~ \^/uploads/\(students\|users\|occurrences#', $nginx)) $failures[] = 'Nginx não bloqueia a principal família de uploads privados.';

$productionEnv = (string) @file_get_contents($root . '/.env.production.example');
foreach (['APP_ENV=production', 'APP_DEBUG=false', 'DB_HOST=127.0.0.1', 'TRUSTED_PROXIES='] as $needle) {
    if (!str_contains($productionEnv, $needle)) $failures[] = "Ambiente de produção sem: {$needle}";
}
if (preg_match('/^(?:DB_PASSWORD|TV_PANEL_ACCESS_TOKEN)=\s*(?!SUBSTITUA)(\S+)/m', $productionEnv)) $failures[] = 'Possível segredo real no ambiente de exemplo.';

$mysql = (string) @file_get_contents($root . '/deploy/oracle-cloud/mysql/sge.cnf.example');
if (!str_contains($mysql, 'bind-address = 127.0.0.1')) $failures[] = 'MySQL não está limitado ao loopback no exemplo.';

echo 'Auditoria de preparação para produção: ' . (count($failures) === 0 ? 'APROVADA' : 'REPROVADA') . PHP_EOL;
foreach ($failures as $failure) fwrite(STDERR, "- {$failure}" . PHP_EOL);
exit($failures === [] ? 0 : 1);
