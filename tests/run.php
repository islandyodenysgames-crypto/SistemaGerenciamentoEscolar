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
require_once $root . '/app/Services/SchoolDataMaintenanceService.php';
require_once $root . '/app/Services/Intelligence/InsightPriority.php';
require_once $root . '/app/Services/Intelligence/InsightFormatter.php';
require_once $root . '/app/Services/Intelligence/InsightGenerator.php';
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
use App\Services\SchoolDataMaintenanceService;
use App\Services\Intelligence\InsightFormatter;
use App\Services\Intelligence\InsightGenerator;

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
$assert(Permissions::roleHas(Roles::ADMIN, Permissions::USERS_MANAGE), 'Administrador deve gerenciar usuarios.');
$assert(Permissions::roleHas(Roles::DIRECTION, Permissions::REPORTS_VIEW), 'Direcao deve consultar relatorios.');
$assert(Permissions::roleHas(Roles::COORDINATION, Permissions::OCCURRENCES_MANAGE), 'Coordenacao deve administrar ocorrencias.');
$assert(Permissions::roleHas(Roles::SECRETARY, Permissions::ATTENDANCE_MANAGE), 'Secretaria deve preservar o fluxo legitimo de frequencia.');
$assert(!Permissions::roleHas(Roles::SECRETARY, Permissions::OCCURRENCES_MANAGE), 'Secretaria nao deve administrar ocorrencias.');
$assert(Permissions::roleHas(Roles::TEACHER, Permissions::OCCURRENCES_CREATE), 'Professor deve preservar a criacao legitima de ocorrencias.');
$assert(!Permissions::roleHas(Roles::TEACHER, Permissions::OCCURRENCES_MANAGE), 'Professor nao deve administrar ocorrencias de terceiros.');
$assert(!Permissions::roleHas(Roles::TEACHER, Permissions::USERS_MANAGE), 'Professor nao deve acessar administracao de usuarios.');

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
$sessionRevalidation=(string)file_get_contents($root.'/app/Services/AuthenticatedSessionService.php');
$assert(str_contains($sessionRevalidation,"Config::get('session.revalidate_interval', 0)")&&str_contains($sessionRevalidation,'$interval > 0'),'Conta e perfil devem ser revalidados em toda requisição por padrão.');

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
$studentMigration = (string) file_get_contents($root . '/database/migrations/202607020003_create_students_table.php');
$assert(str_contains($studentMigration, "'students'") && !str_contains($studentMigration, "'schools'"), 'A migration inicial de estudantes deve criar a tabela students, não schools.');
$providerSource = (string) file_get_contents($root . '/app/Core/AppServiceProvider.php');
$assert(str_contains($providerSource, 'PDO::class') && str_contains($providerSource, 'Connection::getInstance()'), 'O container deve fornecer PDO aos serviços que o tipam diretamente.');
$errorHandlerSource = (string) file_get_contents($root . '/app/Core/ErrorHandler.php');
$assert(str_contains($errorHandlerSource, 'PHP_SAPI === \'cli\'') && str_contains($errorHandlerSource, 'exit(1)'), 'Exceções CLI devem terminar com código de falha para não mascarar migrations incompletas.');
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
$rejected = static function (callable $callback): bool { try { $callback(); return false; } catch (Throwable) { return true; } };
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

if (class_exists(ZipArchive::class)) {
    $backupDirectory = sys_get_temp_dir() . '/sge-backup-tests-' . bin2hex(random_bytes(4));
    mkdir($backupDirectory, 0770, true);
    $makeBackup = static function (string $path, string $payload, string $schemaHash = 'schema-ok', ?string $declaredHash = null, ?string $entryName = null): void {
        $entryName ??= 'database/users.ndjson';
        $manifest = [
            'format' => SchoolDataMaintenanceService::FORMAT,
            'version' => SchoolDataMaintenanceService::VERSION,
            'schema_hash' => $schemaHash,
            'tables' => [['name'=>'users','rows'=>$payload === '' ? 0 : substr_count($payload, "\n"),'entry'=>$entryName]],
            'entries' => [$entryName => ['sha256'=>$declaredHash ?? hash('sha256', $payload),'size'=>strlen($payload)]],
        ];
        $zip = new ZipArchive(); $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR));
        $zip->addFromString($entryName, $payload); $zip->close();
    };
    $validBackup = $backupDirectory . '/valid.zip'; $makeBackup($validBackup, "{\"id\":1}\n");
    $assert((SchoolDataMaintenanceService::inspectArchive($validBackup, 'schema-ok')['version'] ?? 0) === 2, 'Backup válido e compatível deve ser aceito.');
    $emptyBackup = $backupDirectory . '/empty.zip'; $makeBackup($emptyBackup, '');
    $assert((SchoolDataMaintenanceService::inspectArchive($emptyBackup)['tables'][0]['rows'] ?? -1) === 0, 'Backup de tabela sem conteúdo deve permanecer estruturalmente válido.');
    $manyRows = implode("\n", array_fill(0, 10000, '{"id":1}')) . "\n"; $manyBackup=$backupDirectory.'/many.zip';$makeBackup($manyBackup,$manyRows);
    $assert((SchoolDataMaintenanceService::inspectArchive($manyBackup)['version'] ?? 0) === 2, 'Arquivo com muitos registros deve ser verificado incrementalmente.');
    $largePayload=random_bytes(2*1024*1024);$largeBackup=$backupDirectory.'/large.zip';$makeBackup($largeBackup,$largePayload);
    $assert((SchoolDataMaintenanceService::inspectArchive($largeBackup)['version'] ?? 0) === 2, 'Arquivo grande não compressível dentro do limite deve ser aceito.');
    $assert($rejected(fn () => SchoolDataMaintenanceService::inspectArchive($validBackup, 'outro-schema')), 'Backup com schema incompatível deve ser rejeitado.');
    $badHash = $backupDirectory . '/bad-hash.zip'; $makeBackup($badHash, "{\"id\":1}\n", 'schema-ok', str_repeat('0', 64));
    $assert($rejected(fn () => SchoolDataMaintenanceService::inspectArchive($badHash)), 'Backup com hash incorreto deve ser rejeitado.');
    $badManifest=$backupDirectory.'/bad-manifest.zip';$badManifestZip=new ZipArchive();$badManifestZip->open($badManifest,ZipArchive::CREATE|ZipArchive::OVERWRITE);$badManifestZip->addFromString('manifest.json','{"format":"outro"}');$badManifestZip->close();
    $assert($rejected(fn () => SchoolDataMaintenanceService::inspectArchive($badManifest)), 'Manifesto inválido deve ser rejeitado.');
    $traversal = $backupDirectory . '/traversal.zip'; $makeBackup($traversal, 'x', 'schema-ok', null, 'uploads/../escape.txt');
    $assert($rejected(fn () => SchoolDataMaintenanceService::inspectArchive($traversal)), 'Backup com path traversal deve ser rejeitado.');
    $corrupt = $backupDirectory . '/corrupt.zip'; file_put_contents($corrupt, 'arquivo corrompido');
    $assert($rejected(fn () => SchoolDataMaintenanceService::inspectArchive($corrupt)), 'ZIP corrompido deve ser rejeitado antes da restauração.');
    $bomb = $backupDirectory . '/bomb.zip'; $makeBackup($bomb, str_repeat('A', 2 * 1024 * 1024));
    $assert($rejected(fn () => SchoolDataMaintenanceService::inspectArchive($bomb)), 'Entrada com taxa de compressão típica de ZIP bomb deve ser rejeitada.');
    foreach (glob($backupDirectory . '/*') ?: [] as $testFile) @unlink($testFile); @rmdir($backupDirectory);
}
$maintenanceSource = (string) file_get_contents($root . '/app/Services/SchoolDataMaintenanceService.php');
$assert(str_contains($maintenanceSource, "'pre-restore-'") && str_contains($maintenanceSource, "'pre-cleanup-'"), 'Restauração e limpeza devem criar backup de segurança prévio.');
$assert(str_contains($maintenanceSource, 'while(($row=$statement->fetch'), 'Exportação deve processar registros incrementalmente.');
$assert(str_contains($maintenanceSource, "'.partial'"), 'Backup deve ser publicado somente após concluir arquivo temporário.');
$assert(str_contains($maintenanceSource, 'withMaintenanceLock') && str_contains($maintenanceSource, 'LOCK_EX'), 'Operações de manutenção concorrentes devem ser serializadas.');

$settingsController=(string)file_get_contents($root.'/app/Controllers/SettingsController.php');
$reportController=(string)file_get_contents($root.'/app/Controllers/ReportController.php');
$searchService=(string)file_get_contents($root.'/app/Services/SearchService.php');
$favoriteService=(string)file_get_contents($root.'/app/Services/FavoriteService.php');
$fileController=(string)file_get_contents($root.'/app/Controllers/FileAccessController.php');
$assert(str_contains($settingsController,'Permissions::SETTINGS_MANAGE'),'Pagina de configuracoes deve exigir permissao administrativa.');
$assert(str_contains($reportController,'Permissions::REPORTS_VIEW'),'Exportacoes devem exigir permissao de relatorios no servidor.');
$assert(str_contains($searchService,'allowedGroups')&&str_contains($searchService,'student_monitoring_users'),'Busca deve filtrar recursos e casos pelo escopo do usuario.');
$assert(str_contains($favoriteService,'isAccessible'),'Favoritos devem rejeitar IDs inexistentes ou fora do escopo.');
$assert(str_contains($fileController,'withinRecordScope')&&str_contains($fileController,"http_response_code(404)"),'Downloads privados devem validar escopo e ocultar existencia do arquivo.');

$attendanceControllerSource=(string)file_get_contents($root.'/app/Controllers/AttendanceController.php');
$attendanceCreateSource=(string)file_get_contents($root.'/app/Views/pages/attendance/create.php');
$assert(str_contains($attendanceControllerSource,"'frequencia/novo?turma='")&&str_contains($attendanceControllerSource,"'&data=' . urlencode(\$date)"),'Salvar chamada deve retornar ao fluxo de nova chamada preservando turma e data.');
$assert(str_contains($attendanceCreateSource,'Próxima turma pendente')&&str_contains($attendanceCreateSource,'completedClassIds'),'Nova chamada deve orientar a próxima turma e identificar as turmas já concluídas.');

$classInsightGenerator = new InsightGenerator(new InsightFormatter());
$classInsights = $classInsightGenerator->school([
    'summary' => ['classes_in_attention' => 2],
    'classes' => [
        ['name' => '1º ano B', 'risk_score' => 30, 'risk_level' => 'MODERATE'],
        ['name' => '3º ano D', 'risk_score' => 99, 'risk_level' => 'CRITICAL'],
    ],
    'period' => ['days' => 30],
]);
$classInsight = current(array_filter($classInsights, static fn(array $item): bool => ($item['key'] ?? '') === 'school.attention_classes')) ?: [];
$assert(str_contains((string) ($classInsight['description'] ?? ''), '3º ano D') && ($classInsight['level'] ?? '') === 'CRITICAL','Insight de turmas deve destacar a maior pontuação de risco, independentemente da ordem alfabética.');

$trendInsights = $classInsightGenerator->school([
    'summary' => [],
    'attendance' => ['trend' => ['status' => 'WORSENING', 'percentage' => 25.0, 'current' => 10, 'previous' => 8, 'baseline_available' => true]],
    'occurrences' => ['trend' => ['status' => 'IMPROVING', 'percentage' => -50.0, 'current' => 2, 'previous' => 4, 'baseline_available' => true]],
    'period' => ['days' => 30],
]);
$assert(count(array_filter($trendInsights, static fn(array $item): bool => ($item['key'] ?? '') === 'school.attendance_worsening')) === 1,'Insight deve reconhecer status de tendência em maiúsculas.');
$assert(count(array_filter($trendInsights, static fn(array $item): bool => ($item['key'] ?? '') === 'school.occurrences_improving')) === 1,'Insight positivo de ocorrências deve reconhecer status padronizado.');

$zeroBaselineInsights = $classInsightGenerator->school([
    'summary' => [],
    'attendance' => ['trend' => ['status' => 'WORSENING', 'percentage' => 100.0, 'current' => 7, 'previous' => 0, 'baseline_available' => false]],
    'period' => ['days' => 30],
]);
$zeroBaselineInsight = current(array_filter($zeroBaselineInsights, static fn(array $item): bool => ($item['key'] ?? '') === 'school.attendance_worsening')) ?: [];
$assert(str_contains((string) ($zeroBaselineInsight['title'] ?? ''), '7 falta(s)') && !str_contains((string) ($zeroBaselineInsight['title'] ?? ''), '100%'),'Tendência sem base anterior não deve apresentar variação percentual enganosa.');

$attendanceAnalyticsSource = (string) file_get_contents($root . '/app/Services/AttendanceAnalyticsService.php');
$attendanceChartSource = (string) file_get_contents($root . '/app/Views/components/dashboard/attendance-chart-pro.php');
$executivePanelSource = (string) file_get_contents($root . '/app/Views/components/dashboard/executive-panel.php');
$schoolIndexSource = (string) file_get_contents($root . '/app/Services/SchoolGeneralIndexService.php');
$assert(str_contains($attendanceAnalyticsSource, 'AS total_records') && str_contains($attendanceAnalyticsSource, 'AS presents'),'Série de frequência deve fornecer os pesos usados na média consolidada.');
$assert(str_contains($attendanceChartSource, '$totalPresents / $totalRecords') && str_contains($attendanceChartSource, "'Estável'"),'Gráfico deve usar média ponderada e distinguir estabilidade de crescimento.');
$assert(str_contains($executivePanelSource, '$studentsInAlert / $totalStudents') && !str_contains($executivePanelSource, '$studentsInAlert * 10'),'Progresso de alunos em alerta deve representar proporção real dos alunos ativos.');
$assert(str_contains($schoolIndexSource, '$distance <= $tolerance'),'Comparações históricas do índice não devem reutilizar snapshots distantes da data-alvo.');
$assert(str_contains($schoolIndexSource, '$priorityStudents === 0 ? 10.0'),'Índice geral não deve penalizar acompanhamento quando não existem alunos prioritários.');
$dashboardServiceSource = (string) file_get_contents($root . '/app/Services/DashboardService.php');
$assert(str_contains($dashboardServiceSource, '$frequencyForIndex') && str_contains($dashboardServiceSource, 'schoolFrequencyMonth'),'Índice geral deve usar frequência consolidada quando ainda não há chamadas no dia.');
$assert(str_contains($attendanceAnalyticsSource, 'activeSchoolYearRange'),'Indicador anual de frequência deve respeitar o ano letivo ativo.');
$tvControllerSource = (string) file_get_contents($root . '/app/Controllers/TvPanelController.php');
$tvViewSource = (string) file_get_contents($root . '/app/Views/pages/tv-panel/show.php');
$assert(str_contains($tvControllerSource, "'ife_score'") && str_contains($tvViewSource, 'IFE do ranking'),'Painel TV deve exibir o mesmo IFE que determina a ordem do ranking diário.');
$assert(!str_contains($tvViewSource, '<h2>Turmas com maior evolução</h2>'),'Painel TV não deve chamar o ranking diário de evolução histórica.');

$dashboardIndicatorsSource = (string) file_get_contents($root . '/app/Views/components/dashboard/indicators.php');
$occurrenceIndicatorSource = (string) file_get_contents($root . '/app/Services/Occurrence/OccurrenceIndicatorService.php');
$reportServiceSource = (string) file_get_contents($root . '/app/Services/ReportService.php');
$monitoringRepositorySource = (string) file_get_contents($root . '/app/Repositories/Monitoring/StudentMonitoringRepository.php');
$assert(str_contains($dashboardIndicatorsSource, '$hasRecords') && str_contains($dashboardIndicatorsSource, "'Sem registros'"),'Indicadores de frequência sem chamadas não devem exibir 0% como resultado crítico real.');
$assert(str_contains($occurrenceIndicatorSource, "'Sem ocorrências no mês para calcular a taxa'"),'Taxa de resolução sem ocorrências deve ser apresentada como indisponível.');
$assert(str_contains($reportServiceSource, "SUM(o.status IN ('OPEN','IN_PROGRESS')) open_count") && str_contains($reportServiceSource, "SUM(o.status IN ('RESOLVED','CLOSED')) resolved_count"),'Relatórios devem separar ocorrências abertas/em andamento das resolvidas/encerradas.');
$assert(str_contains($monitoringRepositorySource, "SUM(status IN ('OPEN','IN_PROGRESS')) open_total"),'Métricas do acompanhamento não devem classificar ocorrência encerrada como aberta.');
$classIntelligenceViewSource = (string) file_get_contents($root . '/app/Views/components/classes/intelligence.php');
$assert(str_contains($classIntelligenceViewSource, "'baseline_available'") && str_contains($classIntelligenceViewSource, 'Sem base anterior'),'Inteligência da turma não deve exibir 100% de variação quando o período anterior não possui base.');
$intelligenceServiceSource = (string) file_get_contents($root . '/app/Services/Intelligence/IntelligenceService.php');
$intelligenceCasesSource = (string) file_get_contents($root . '/app/Views/pages/intelligence/cases.php');
$assert(str_contains($intelligenceServiceSource, "'label' => 'Alunos afetados'") && str_contains($intelligenceServiceSource, "'label' => 'Total de faltas F'"),'Detalhamento de faltas deve distinguir alunos únicos do total de registros F.');
$assert(str_contains($intelligenceCasesSource, 'O que este indicador significa') && str_contains($intelligenceCasesSource, 'O que a escola pode fazer'),'Tela de casos deve explicar significado, impacto e ação recomendada para indicadores que exigem contexto.');

echo sprintf("Testes executados: %d\n", $tests);
if ($failures !== []) {
    foreach ($failures as $failure) fwrite(STDERR, "FALHA: {$failure}\n");
    exit(1);
}
echo "Resultado: aprovado\n";
