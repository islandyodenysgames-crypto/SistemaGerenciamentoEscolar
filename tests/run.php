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
require_once $root . '/app/ValueObjects/Intelligence/StudentTrend.php';
require_once $root . '/app/Services/Intelligence/TrendAnalysisService.php';
require_once $root . '/app/Services/Intelligence/ClassTrendAnalysisService.php';
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
$occurrenceValidationSource = (string) file_get_contents($root . '/app/Services/Occurrence/Concerns/OccurrenceValidationOperations.php');
$intelligenceRepositorySource = (string) file_get_contents($root . '/app/Repositories/Intelligence/IntelligenceRepository.php');
$recurrenceSource = (string) file_get_contents($root . '/app/Services/RecurrenceIntelligenceService.php');
$occurrenceCreateSource = (string) file_get_contents($root . '/app/Views/pages/occurrences/create.php');
$severityServiceSource = (string) file_get_contents($root . '/app/Services/Occurrence/SeverityService.php');
$classPageSource = (string) file_get_contents($root . '/app/Views/pages/students/class.php');
$assert(str_contains($occurrenceValidationSource, 'SeverityService::NOT_APPLICABLE') && str_contains($occurrenceValidationSource, "\$status = 'RESOLVED'"),'Elogio deve ser normalizado como registro positivo concluído e com gravidade não aplicável.');
$assert(str_contains($severityServiceSource, "public const NOT_APPLICABLE = 'NOT_APPLICABLE'") && str_contains($severityServiceSource, "self::NOT_APPLICABLE => 0"),'Gravidade não aplicável deve existir e ter peso adverso zero.');
$assert(substr_count($intelligenceRepositorySource, "nature = 'ADVERSE'") >= 7,'Somente intervenções adversas devem alimentar as consultas de risco de alunos e turmas.');
$assert(str_contains($recurrenceSource, "so.nature = 'ADVERSE'"),'Respostas pedagógicas e elogios não devem gerar reincidência negativa.');
$assert(str_contains($occurrenceCreateSource, 'responseRecordNotice') && str_contains($occurrenceCreateSource, 'NOT_APPLICABLE') && str_contains($occurrenceCreateSource, 'updatePositiveRecordMode'),'Formulário deve distinguir resposta pedagógica e reconhecimento positivo dos registros adversos.');
$assert(str_contains($intelligenceRepositorySource, 'classAttendanceResponse') && str_contains($intelligenceRepositorySource, "sor.nature = 'RESPONSE'") && str_contains($classIntelligenceViewSource, 'Intervenções sobre frequência'),'Inteligência da turma deve somar respostas pontuais e ações em acompanhamentos sem alterar as faltas.');

$positiveIndicatorServiceSource = file_get_contents(__DIR__ . '/../app/Services/Intelligence/PositiveIndicatorService.php');
$defaultSettingsSource = file_get_contents(__DIR__ . '/../app/Core/Settings/DefaultSettings.php');
$snapshotServiceSource = file_get_contents(__DIR__ . '/../app/Services/Intelligence/IntelligenceDailySnapshotService.php');
$studentIntelligenceViewSource = file_get_contents(__DIR__ . '/../app/Views/components/students/profile/intelligence.php');
$dashboardPageSource = file_get_contents(__DIR__ . '/../app/Views/components/dashboard/page.php');
$insightGeneratorSource = file_get_contents(__DIR__ . '/../app/Services/Intelligence/InsightGenerator.php');
$intelligenceIndexSource = file_get_contents(__DIR__ . '/../app/Views/pages/intelligence/index.php');
$assert(str_contains($positiveIndicatorServiceSource, 'protection_max_reduction') && !str_contains($positiveIndicatorServiceSource, "'code' => 'RECOGNITION'"), 'Fatores de proteção devem possuir redução máxima configurável e não usar elogios como compensação automática.');
$assert(!str_contains($positiveIndicatorServiceSource, "'code' => 'ATTENDANCE_RESPONSE'") && !str_contains($positiveIndicatorServiceSource, "'code' => 'BEHAVIOR_RESPONSE'"), 'Registrar uma resposta sem melhora comprovada não deve reduzir o risco.');
$assert(str_contains($positiveIndicatorServiceSource, 'ATTENDANCE_IMPROVEMENT') && str_contains($positiveIndicatorServiceSource, 'NO_RECURRENCE'), 'Atenuação deve exigir evolução relacionada à frequência ou às ocorrências.');
$assert(str_contains($defaultSettingsSource, "'protection_max_reduction', 15") && str_contains($snapshotServiceSource, "risk['raw_score']"), 'Limite de proteção deve ser configurável e snapshots devem preservar o risco observado.');
$assert(str_contains($studentIntelligenceViewSource, 'Evidências positivas') && str_contains($studentIntelligenceViewSource, 'Risco observado'), 'Perfil do aluno deve explicar indicadores positivos e a diferença entre risco observado e ajustado.');
$assert(str_contains($classIntelligenceViewSource, 'Reconhecimentos positivos') && str_contains($classIntelligenceViewSource, 'Alunos com resposta pedagógica'), 'Painel da turma deve exibir métricas positivas e cobertura pedagógica.');
$assert(!str_contains($dashboardPageSource, "component('dashboard/positive-indicators'") && str_contains($dashboardPageSource, "'insights' => \$dailyIntelligence['insights']") && str_contains($dashboardPageSource, 'registrados hoje'), 'Página Inicial deve usar somente sinais diários e não manter um painel positivo separado.');
$assert(str_contains($insightGeneratorSource, 'school.pedagogical_responses') && str_contains($insightGeneratorSource, 'school.positive_recognitions') && str_contains($insightGeneratorSource, 'school.resolved_situations'), 'Painel de insights deve integrar respostas, reconhecimentos e situações resolvidas.');
$assert(str_contains($intelligenceRepositorySource, 'latest_resolved_occurrence_id') && str_contains($insightGeneratorSource, "'&ocorrencia='") && str_contains($insightGeneratorSource, "'#occurrence-'"), 'Insight de situação resolvida deve abrir e destacar a ocorrência correspondente no calendário.');
$assert(str_contains($insightGeneratorSource, "inteligencia/casos?tipo=unjustified_absences") && str_contains($insightGeneratorSource, "inteligencia/casos?tipo=occurrences_period"), 'Insights de piora devem abrir as listas específicas de faltas e ocorrências que sustentam o alerta.');
$assert(str_contains($insightGeneratorSource, "'allow_compare' => true") && str_contains($insightGeneratorSource, 'Ver faltas sem justificativa') && str_contains($insightGeneratorSource, 'Ver casos do período'), 'Ações e comparações dos insights devem ser configuradas conforme a situação apresentada.');
$assert(str_contains($intelligenceServiceSource, 'public function dailyInsights()') && str_contains($dashboardServiceSource, 'dailyIntelligence'), 'Página Inicial deve receber uma leitura diária independente da janela da Central de Inteligência.');
$assert(str_contains($intelligenceIndexSource, 'Evoluções e respostas positivas') && str_contains($intelligenceIndexSource, '$actionInsights') && str_contains($intelligenceIndexSource, '$positiveInsights'), 'Central de Inteligência deve separar situações de ação dos resultados positivos do período.');
$assert(str_contains($intelligenceCasesSource, 'Resposta pontual') && str_contains($intelligenceServiceSource, "'label' => 'Com resposta pontual'"),'Visão analítica de infrequência deve informar e permitir registrar respostas pontuais.');
$assert(str_contains($classPageSource, "'ocorrencias/nova?aluno='") && str_contains($classPageSource, 'Intervenção'),'Painel da turma deve oferecer intervenção individual para cada aluno autorizado.');

$studentRepositorySource = file_get_contents(__DIR__ . '/../app/Repositories/StudentRepository.php');
$studentReportSource = file_get_contents(__DIR__ . '/../app/Views/pages/students/report.php');
$studentProfileActionsSource = file_get_contents(__DIR__ . '/../app/Controllers/Concerns/Student/StudentProfileActions.php');
$assert(str_contains($studentRepositorySource, 'attendanceSummaryByYear') && str_contains($studentRepositorySource, 'total_atestados_medicos') && str_contains($studentRepositorySource, 'total_faltas_onibus'), 'Relatório individual deve consolidar todos os estados de frequência por ano.');
$assert(str_contains($studentReportSource, '<h2>Resumo mensal</h2>') && str_contains($studentReportSource, '<th>Atestados</th>') && str_contains($studentReportSource, '<th>Transporte</th>') && !str_contains($studentReportSource, '<h2>Histórico de frequência</h2>') && !str_contains($studentReportSource, '<h2>Totais de frequência</h2>'), 'Relatório individual deve consolidar cada situação no resumo mensal sem criar uma lista histórica separada.');
$assert(str_contains($studentReportSource, 'Intervenções e registros escolares') && str_contains($studentReportSource, "['subject_name']") && str_contains($studentReportSource, 'Reconhecimentos positivos') && str_contains($studentReportSource, 'focus_area_label'), 'Relatório individual deve preservar disciplina, finalidade e aspectos positivos das intervenções.');
$assert(str_contains($studentProfileActionsSource, 'attendanceSummaryByYear($id, $year)'), 'Relatório individual deve usar totais limitados ao ano selecionado.');

$occurrenceActionControllerSource = (string) file_get_contents($root . '/app/Controllers/Concerns/Occurrence/Actions/OccurrenceActionActions.php');
$occurrenceHelperSource = (string) file_get_contents($root . '/app/Controllers/Concerns/Occurrence/Shared/OccurrenceHelpers.php');
$occurrenceActionRepositorySource = (string) file_get_contents($root . '/app/Repositories/Occurrence/ActionRepository.php');
$occurrenceProfileSource = (string) file_get_contents($root . '/app/Views/components/students/profile/occurrences.php');
$occurrencePurposeSource = (string) file_get_contents($root . '/app/Views/components/occurrences/intervention-purpose.php');
$routesSource = (string) file_get_contents($root . '/routes/web.php');
$assert(str_contains($routesSource, "'/ocorrencias/providencia/editar'") && str_contains($occurrenceActionControllerSource, 'function actionUpdate'),'Providências devem possuir fluxo próprio de edição e atualização.');
$assert(str_contains($occurrenceHelperSource, 'canManageOccurrenceAction') && str_contains($occurrenceHelperSource, "\$action['created_by']"),'Edição de providência por usuário restrito deve validar a autoria do registro.');
$assert(str_contains($occurrenceProfileSource, '$canManageThisAction') && str_contains($occurrenceProfileSource, 'Editar providência'),'Perfil do aluno deve exibir a edição apenas para gestão ou autor da providência.');
$assert(str_contains($occurrenceActionRepositorySource, 'latestStatusForOccurrence'),'Editar providência antiga deve preservar como situação atual o estado definido pela providência mais recente.');
$assert(str_contains($occurrencePurposeSource, 'Situação que exige atenção — Ocorrência'),'Finalidade adversa deve ser identificada claramente como ocorrência.');

$studentTrendService = (new ReflectionClass(App\Services\Intelligence\TrendAnalysisService::class))->newInstanceWithoutConstructor();
$studentTrendResult = $studentTrendService->fromSnapshots([
    ['snapshot_date' => '2026-10-03', 'analysis_window_days' => 30, 'attendance_percentage' => 80, 'total_occurrences' => 3, 'risk_score' => 40],
    ['snapshot_date' => '2026-10-01', 'analysis_window_days' => 30, 'attendance_percentage' => 95, 'total_occurrences' => 1, 'risk_score' => 20],
    ['snapshot_date' => '2026-10-02', 'analysis_window_days' => 15, 'attendance_percentage' => 60, 'total_occurrences' => 8, 'risk_score' => 80],
])->toArray();
$assert(($studentTrendResult['samples'] ?? 0) === 2 && ($studentTrendResult['frequency']['status'] ?? '') === 'WORSENING','Tendência do aluno deve ordenar as datas e comparar somente snapshots da janela de análise vigente.');

$classTrendService = (new ReflectionClass(App\Services\Intelligence\ClassTrendAnalysisService::class))->newInstanceWithoutConstructor();
$classTrendResult = $classTrendService->fromSnapshots([
    ['snapshot_date' => '2026-10-02', 'analysis_window_days' => 30, 'attendance_percentage' => 80, 'total_occurrences' => 2, 'average_risk_score' => 25, 'priority_students' => 0, 'covered_students' => 0, 'pending_recommendations' => 0],
    ['snapshot_date' => '2026-10-01', 'analysis_window_days' => 30, 'attendance_percentage' => 95, 'total_occurrences' => 1, 'average_risk_score' => 15, 'priority_students' => 0, 'covered_students' => 0, 'pending_recommendations' => 0],
]);
$assert(($classTrendResult['frequency']['status'] ?? '') === 'WORSENING','Tendência da turma deve ordenar snapshots cronologicamente antes de comparar os segmentos.');
$assert(($classTrendResult['coverage']['status'] ?? '') === 'INSUFFICIENT','Ausência de alunos prioritários não deve ser apresentada falsamente como cobertura de 0%.');

$intelligenceTrendSource = (string) file_get_contents($root . '/app/Services/Intelligence/IntelligenceService.php');
$dailySnapshotRepositorySource = (string) file_get_contents($root . '/app/Repositories/Intelligence/IntelligenceDailySnapshotRepository.php');
$occurrenceStatisticsSource = (string) file_get_contents($root . '/app/Repositories/Occurrence/Concerns/OccurrenceStatisticsQueries.php');
$assert(str_contains($intelligenceTrendSource, "'NEW_SIGNAL'") && str_contains($intelligenceTrendSource, '!$hasBaseline'),'Primeiro registro após uma base zerada deve ser classificado como novo sinal, não como piora percentual confirmada.');
$assert(str_contains($dailySnapshotRepositorySource, '$interval = $days - 1') && str_contains($dailySnapshotRepositorySource, "risk_level IN ('MODERATE','HIGH','CRITICAL')"),'Snapshots históricos devem respeitar a janela exata e cobertura apenas de alunos prioritários.');
$assert(str_contains($attendanceChartSource, '$trendWeightedAverage') && str_contains($attendanceChartSource, '$trendSegmentSize'),'Tendência visual da frequência deve comparar segmentos ponderados, não apenas dois dias isolados.');
$assert(str_contains($occurrenceStatisticsSource, 'LEAST(') && str_contains($occurrenceStatisticsSource, 'LAST_DAY('),'Variação mensal de ocorrências deve comparar intervalos equivalentes dos dois meses.');
$schoolIndexCardSource = (string) file_get_contents($root . '/app/Views/components/dashboard/school-index-card.php');
$assert(str_contains($schoolIndexSource, "'difference'=>null") && str_contains($schoolIndexCardSource, 'Sem histórico suficiente'),'Índice geral não deve declarar estabilidade quando existe apenas uma captura histórica.');

echo sprintf("Testes executados: %d\n", $tests);
if ($failures !== []) {
    foreach ($failures as $failure) fwrite(STDERR, "FALHA: {$failure}\n");
    exit(1);
}
echo "Resultado: aprovado\n";
