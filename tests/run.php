<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/app/Core/Session.php';
require_once $root . '/app/Core/Csrf.php';
require_once $root . '/app/Auth/Roles.php';
require_once $root . '/app/Auth/Permissions.php';

use App\Auth\Permissions;
use App\Auth\Roles;
use App\Core\Csrf;

$tests = 0;
$failures = [];
$assert = static function (bool $condition, string $message) use (&$tests, &$failures): void {
    $tests++;
    if (!$condition) $failures[] = $message;
};

$_SESSION = [];
$token = Csrf::token();
$assert(strlen($token) === 64, 'O token CSRF deve possuir 64 caracteres.');
$assert(Csrf::validate($token), 'O token CSRF valido foi rejeitado.');
$assert(!Csrf::validate('token-invalido'), 'Um token CSRF invalido foi aceito.');
$assert(Csrf::token() === $token, 'O token CSRF mudou durante a mesma sessao.');

$assert(Permissions::roleHas(Roles::ADMIN, Permissions::SETTINGS_MANAGE), 'Administrador deve gerenciar configuracoes.');
$assert(Permissions::roleHas(Roles::DIRECTION, Permissions::SETTINGS_MANAGE), 'Direcao deve gerenciar configuracoes.');
$assert(!Permissions::roleHas(Roles::TEACHER, Permissions::SETTINGS_MANAGE), 'Professor nao deve gerenciar configuracoes.');
$assert(!Permissions::roleHas(Roles::SECRETARY, Permissions::SETTINGS_MANAGE), 'Secretaria nao deve executar manutencao critica.');

$lifecycle = (string) file_get_contents($root . '/app/Services/AcademicLifecycleService.php');
$assert(substr_count($lifecycle, '->transaction(') >= 4, 'Operacoes criticas do ciclo academico devem ser transacionais.');
$assert(substr_count($lifecycle, 'createBackup') >= 2, 'Fechamentos criticos devem criar backup de seguranca.');

$controller = (string) file_get_contents($root . '/app/Controllers/DataMaintenanceController.php');
$view = (string) file_get_contents($root . '/app/Views/pages/settings/data-maintenance.php');
$assert(str_contains($controller, 'Csrf::validate'), 'Manutencao de dados deve validar CSRF.');
$assert(substr_count($controller, "Response::redirect(base_url('configuracoes/dados'));\n            return;") >= 2, 'Confirmacoes invalidas devem interromper restauracao e limpeza.');
$assert(substr_count($view, 'name="_token"') === 3, 'Todos os formularios de manutencao devem enviar CSRF.');

echo sprintf("Testes executados: %d\n", $tests);
if ($failures !== []) {
    foreach ($failures as $failure) fwrite(STDERR, "FALHA: {$failure}\n");
    exit(1);
}
echo "Resultado: aprovado\n";
