<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$service = (string) file_get_contents($root . '/app/Services/SchoolDataMaintenanceService.php');
$controller = (string) file_get_contents($root . '/app/Controllers/DataMaintenanceController.php');
$failures = [];
$require = static function (bool $condition, string $message) use (&$failures): void { if (!$condition) $failures[] = $message; };

$require(str_contains($service, 'REPEATABLE READ'), 'O snapshot do banco não declara isolamento consistente.');
$require(str_contains($service, 'hash_file') && str_contains($service, "hash_init('sha256')"), 'O backup não verifica SHA-256 por entrada.');
$require(str_contains($service, "'.partial'"), 'O backup não utiliza publicação por arquivo temporário.');
$require(str_contains($service, "'pre-restore-'") && str_contains($service, "'pre-cleanup-'"), 'Operações destrutivas não criam backup prévio.');
$require(str_contains($service, 'MAX_UNCOMPRESSED') && str_contains($service, 'MAX_RATIO'), 'Proteções contra ZIP bomb estão incompletas.');
$require(str_contains($service, 'rollback_restored'), 'Falhas após commit não registram tentativa de recuperação.');
$require(str_contains($service, 'data-maintenance.ndjson'), 'Operações críticas não possuem trilha administrativa.');
$require(str_contains($service, 'withMaintenanceLock') && str_contains($service, 'LOCK_EX'), 'Operações concorrentes de manutenção não estão serializadas.');
$require(str_contains($controller, 'Request::clientIp()') && str_contains($controller, 'actorId()'), 'Autoria e origem não são enviadas ao serviço.');

foreach ($failures as $failure) fwrite(STDERR, "FALHA: {$failure}\n");
echo 'Auditoria de backup e recuperação: ' . count($failures) . " erro(s).\n";
exit($failures === [] ? 0 : 1);
