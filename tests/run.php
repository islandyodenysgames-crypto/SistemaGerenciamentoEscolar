<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/app/Core/Env.php';
require_once $root . '/app/Core/Config.php';
require_once $root . '/app/Core/Request.php';
require_once $root . '/app/Core/Session.php';
require_once $root . '/app/Core/Csrf.php';
require_once $root . '/app/Auth/Roles.php';
require_once $root . '/app/Auth/Permissions.php';
require_once $root . '/app/Services/LoginThrottleService.php';
require_once $root . '/app/Services/AuthService.php';
require_once $root . '/app/Services/FileSecurityService.php';
require_once $root . '/app/Services/TvPanelAccessService.php';
require_once $root . '/app/Helpers/url.php';

use App\Auth\Permissions;
use App\Auth\Roles;
use App\Core\Csrf;
use App\Core\Config;
use App\Core\Request;
use App\Core\Session;
use App\Services\LoginThrottleService;
use App\Services\AuthService;
use App\Services\FileSecurityService;
use App\Services\TvPanelAccessService;

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
$assert(substr_count($controller, "Response::redirect(base_url('configuracoes/dados'))") >= 2 && substr_count($controller, 'return;') >= 2, 'Confirmacoes invalidas devem interromper restauracao e limpeza.');
$assert(substr_count($view, 'name="_token"') === 3, 'Todos os formularios de manutencao devem enviar CSRF.');

$html = '<form method="post" action="/save"><button>Salvar</button></form><form method="get"></form>';
$protected = Csrf::injectIntoHtml($html);
$assert(substr_count($protected, 'name="_token"') === 1, 'A proteção central deve injetar CSRF em formulários POST.');
$variants = "<form METHOD=POST></form><form method = 'POST'></form><form\n method=\"post\"></form>";
$assert(substr_count(Csrf::injectIntoHtml($variants), 'name="_token"') === 3, 'A proteção central deve cobrir variações válidas do atributo method.');
$alreadyProtected = '<form method="post"><input type="hidden" name=_token value="existente"></form>';
$assert(substr_count(Csrf::injectIntoHtml($alreadyProtected), 'name=_token') === 1, 'Token explícito não deve ser duplicado.');
$nestedForms = '<form method="post"><form method="post"><input name="_token" value="existente"></form></form>';
$assert(substr_count(Csrf::injectIntoHtml($nestedForms), '_token') === 2, 'Cada formulário POST deve possuir seu próprio token, inclusive em HTML legado aninhado.');
$_POST = []; $_SERVER['HTTP_X_CSRF_TOKEN'] = $token;
$assert(Csrf::validate(Csrf::requestToken()), 'O token CSRF de AJAX deve ser aceito.');
unset($_SERVER['HTTP_X_CSRF_TOKEN']);
$assert(Session::isExpired(100, 200, 60), 'Sessão inativa deve expirar.');
$assert(!Session::isExpired(150, 200, 60), 'Sessão dentro do limite não deve expirar.');

$_ENV['LOGIN_IDENTITY_LIMIT'] = '2'; $_ENV['LOGIN_ORIGIN_LIMIT'] = '10'; $_ENV['LOGIN_WINDOW'] = '900'; $_ENV['LOGIN_LOCKOUT'] = '60';
Config::load();
$throttleFile = sys_get_temp_dir() . '/sge-login-throttle-' . bin2hex(random_bytes(4)) . '.json';
$throttle = new LoginThrottleService($throttleFile);
$throttle->recordFailure('teste@escola.local', '127.0.0.1');
$assert($throttle->retryAfter('teste@escola.local', '127.0.0.1') === 0, 'Primeira falha não deve bloquear antes do limite.');
$throttle->recordFailure('teste@escola.local', '127.0.0.1');
$assert($throttle->retryAfter('teste@escola.local', '127.0.0.1') > 0, 'Limite de login deve aplicar bloqueio temporário.');
$throttle->recordSuccess('teste@escola.local');
$corruptThrottle = new LoginThrottleService($throttleFile);
file_put_contents($throttleFile, '{arquivo-corrompido');
$assert($corruptThrottle->retryAfter('outro@escola.local', '127.0.0.2') === 0, 'Arquivo corrompido do limitador deve ser recuperado sem bloquear usuários.');
$assert(is_array(json_decode((string) file_get_contents($throttleFile), true)), 'Arquivo corrompido deve ser regravado de forma íntegra.');
file_put_contents($throttleFile, json_encode(['expired' => ['attempts' => 1, 'started_at' => 1, 'locked_until' => 1]]));
$corruptThrottle->retryAfter('novo@escola.local', '127.0.0.3');
$prunedThrottleData = json_decode((string) file_get_contents($throttleFile), true);
$assert(is_array($prunedThrottleData) && !isset($prunedThrottleData['expired']), 'Registros vencidos do limitador devem ser removidos automaticamente.');
@unlink($throttleFile); @unlink($throttleFile . '.lock');
$blockedParent = sys_get_temp_dir() . '/sge-throttle-parent-' . bin2hex(random_bytes(4));
file_put_contents($blockedParent, 'arquivo');
$unavailableThrottle = new LoginThrottleService($blockedParent . '/login.json');
$assert($unavailableThrottle->retryAfter('teste@escola.local', '127.0.0.1') === 0, 'Falha de armazenamento não deve indisponibilizar o login.');
@unlink($blockedParent);

$database = new PDO('sqlite::memory:');
$database->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, email TEXT, role TEXT, password TEXT, active INTEGER, photo_path TEXT, photo_updated_at TEXT)');
$insert = $database->prepare('INSERT INTO users VALUES (1, :name, :email, :role, :password, 1, NULL, NULL)');
$insert->execute(['name' => 'Usuário Teste', 'email' => 'teste@escola.local', 'role' => 'TEACHER', 'password' => password_hash('senha-correta', PASSWORD_DEFAULT)]);
$auth = new AuthService($database);
$assert($auth->attempt('teste@escola.local', 'senha-correta') !== null, 'Login válido deve ser aceito.');
$assert($auth->attempt('teste@escola.local', 'senha-incorreta') === null, 'Login inválido deve ser rejeitado.');
$database->exec("UPDATE users SET role = 'ADMIN' WHERE id = 1");
$assert(($auth->currentUser(1)['role'] ?? '') === 'ADMIN', 'Revalidação deve refletir mudança de papel.');
$database->exec('UPDATE users SET active = 0 WHERE id = 1');
$assert($auth->currentUser(1) === null, 'Revalidação deve invalidar usuário inativo.');

$_ENV['APP_URL'] = 'https://escola.exemplo/sistema/public'; Config::load();
$assert(base_url('login') === 'https://escola.exemplo/sistema/public/login', 'base_url deve respeitar APP_URL com HTTPS e subdiretório.');
$assert(Request::safeApplicationReturnUrl('https://escola.exemplo/sistema/public/alunos') !== null, 'Retorno interno válido deve ser aceito.');
$assert(Request::safeApplicationReturnUrl('https://malicioso.exemplo/sistema/public') === null, 'Retorno para host externo deve ser rejeitado.');
$assert(Request::safeApplicationReturnUrl('https://escola.exemplo/outro-sistema') === null, 'Retorno fora do APP_URL deve ser rejeitado.');
$assert(Request::safeApplicationReturnUrl('//malicioso.exemplo/caminho') === null, 'URL relativa a protocolo externo deve ser rejeitada.');
$_ENV['APP_URL'] = '/'; Config::load();
$assert(base_url() === '/' && base_url('login') === '/login', 'base_url deve funcionar na raiz do domínio.');

$_SERVER = ['HTTPS' => 'off', 'SERVER_PORT' => '80', 'REMOTE_ADDR' => '10.0.0.10', 'HTTP_X_FORWARDED_PROTO' => 'https'];
$_ENV['TRUSTED_PROXIES'] = ''; Config::load();
$assert(!Request::isSecure(), 'Proxy não confiável não pode forçar cookie Secure via cabeçalho.');
$_ENV['TRUSTED_PROXIES'] = '10.0.0.10'; Config::load();
$assert(Request::isSecure(), 'Proxy explicitamente confiável deve informar HTTPS.');
$_SERVER = ['HTTPS' => 'on', 'SERVER_PORT' => '443', 'REMOTE_ADDR' => '127.0.0.1'];
$assert(Request::isSecure(), 'HTTPS direto deve ser reconhecido.');

$routes = (string) file_get_contents($root . '/routes/web.php');
$assert((bool) preg_match('/\\$router->post\\(\\s*[\'\"]\\/logout[\'\"]/', $routes), 'Logout deve usar POST.');
$assert(!str_contains((string) file_get_contents($root . '/app/Console/Commands/SeedCommand.php'), 'admin123'), 'Seed não pode conter senha administrativa fixa.');
$routerSource = (string) file_get_contents($root . '/app/Core/Router.php');
$assert(str_contains($routerSource, "http_response_code(419)") && str_contains($routerSource, "http_response_code(303)"), 'Falha CSRF deve responder 419 para AJAX e 303 para formulário HTML.');
$assert(str_contains($routerSource, 'reload_required'), 'Falha CSRF em AJAX deve indicar recarregamento obrigatório.');

$csrfJavascript = (string) file_get_contents($root . '/public/assets/js/core/csrf.js');
$assert(str_contains($csrfJavascript, 'window.fetch') && str_contains($csrfJavascript, 'XMLHttpRequest.prototype.open') && str_contains($csrfJavascript, 'HTMLFormElement.prototype.submit'), 'CSRF do cliente deve cobrir fetch, XMLHttpRequest e submit programático.');

$temporaryDirectory = sys_get_temp_dir() . '/sge-file-security-' . bin2hex(random_bytes(4));
mkdir($temporaryDirectory, 0770, true);
$pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true);
$pngPath = $temporaryDirectory . '/valid.png'; file_put_contents($pngPath, $pngBytes);
$validImage = FileSecurityService::inspectUpload(['name'=>'foto.png','tmp_name'=>$pngPath,'error'=>UPLOAD_ERR_OK,'size'=>strlen($pngBytes)], ['png'], 1024, false);
$assert($validImage['mime_type'] === 'image/png', 'Imagem válida deve ser identificada pelo MIME real.');
$rejected = static function (callable $callback): bool { try { $callback(); return false; } catch (InvalidArgumentException) { return true; } };
$phpPath = $temporaryDirectory . '/fake.jpg'; file_put_contents($phpPath, '<?php echo "x";');
$assert($rejected(fn () => FileSecurityService::inspectUpload(['name'=>'foto.jpg','tmp_name'=>$phpPath,'error'=>UPLOAD_ERR_OK,'size'=>20], ['jpg'], 1024, false)), 'PHP disfarçado de imagem deve ser rejeitado.');
$assert($rejected(fn () => FileSecurityService::inspectUpload(['name'=>'foto.jpg','tmp_name'=>$pngPath,'error'=>UPLOAD_ERR_OK,'size'=>strlen($pngBytes)], ['jpg'], 1024, false)), 'Extensão incompatível com MIME deve ser rejeitada.');
$assert($rejected(fn () => FileSecurityService::inspectUpload(['name'=>'foto.png','tmp_name'=>$pngPath,'error'=>UPLOAD_ERR_OK,'size'=>strlen($pngBytes)], ['png'], 10, false)), 'Arquivo acima do limite real deve ser rejeitado.');
$assert($rejected(fn () => FileSecurityService::inspectUpload(['name'=>'foto.png','tmp_name'=>$pngPath,'error'=>UPLOAD_ERR_PARTIAL,'size'=>1], ['png'], 1024, false)), 'Upload interrompido deve ser rejeitado.');
$assert(FileSecurityService::safePath($temporaryDirectory, '../outside.txt') === null, 'Path traversal deve ser rejeitado.');
$inside = $temporaryDirectory . '/inside.txt'; file_put_contents($inside, 'ok');
$assert(FileSecurityService::deleteWithin($temporaryDirectory, 'inside.txt') && !is_file($inside), 'Exclusão confinada deve remover somente arquivo autorizado.');
@unlink($pngPath); @unlink($phpPath); @rmdir($temporaryDirectory);

$_SESSION = [];
$_ENV['APP_ENV'] = 'production'; $_ENV['TV_PANEL_ACCESS_TOKEN'] = ''; Config::load();
$tvAccess = new TvPanelAccessService();
$assert(!$tvAccess->canView(), 'Painel TV sem token deve ser bloqueado em produção.');
$tvToken = str_repeat('a', 40); $_ENV['TV_PANEL_ACCESS_TOKEN'] = $tvToken; Config::load();
$assert(!$tvAccess->pair('token-incorreto'), 'Pareamento do Painel TV deve rejeitar token incorreto.');
$assert($tvAccess->pair($tvToken), 'Pareamento do Painel TV deve aceitar token correto.');
$_COOKIE['sge_tv_device'] = hash('sha256', $tvToken);
$assert($tvAccess->canView(), 'Cookie pareado deve permitir atualização automática do Painel TV.');
$assert(str_contains(base_url('uploads/students/1/profile.jpg'), 'arquivos/visualizar?path='), 'Fotografia de estudante deve usar endpoint autenticado.');
$assert(base_url('uploads/classes/turma.jpg') === '/uploads/classes/turma.jpg', 'Foto institucional de turma deve permanecer pública.');

$tvControllerSource = (string) file_get_contents($root . '/app/Controllers/TvPanelController.php');
$dataMethod = strstr($tvControllerSource, 'public function data()');
$dataMethod = is_string($dataMethod) ? strstr($dataMethod, 'public function pair()', true) : '';
$assert(is_string($dataMethod) && !str_contains($dataMethod, "'occurrences'") && !str_contains($dataMethod, "'intelligence'"), 'JSON do Painel TV não deve expor ocorrências ou inteligência.');
$assert(!str_contains((string) file_get_contents($root . '/app/Services/FileUploadService.php'), "'svg'"), 'Novos uploads de logotipo não devem aceitar SVG.');

echo sprintf("Testes executados: %d\n", $tests);
if ($failures !== []) {
    foreach ($failures as $failure) fwrite(STDERR, "FALHA: {$failure}\n");
    exit(1);
}
echo "Resultado: aprovado\n";
