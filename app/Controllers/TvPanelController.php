<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\Settings\SettingRepository;
use App\Services\DashboardService;
use App\Services\NoticeService;
use App\Services\SchoolCalendarService;
use App\Services\TvHallOfFameService;
use App\Services\TvIntelligentContentService;
use App\Services\TvPanelAccessService;
use App\Services\FileSecurityService;

final class TvPanelController extends BaseController
{
    private const KEY = 'tv_panel.configuration';

    public function __construct(
        private DashboardService $dashboard,
        private SettingRepository $settings,
        private NoticeService $notices,
        private SchoolCalendarService $calendar,
        private TvHallOfFameService $hallOfFame,
        private TvIntelligentContentService $intelligentContent,
        private TvPanelAccessService $access
    ) {}

    public function show(): void
    {
        if (!$this->allowDisplay()) return;
        $this->privateDisplayHeaders();
        $config = $this->configuration();
        $data = $this->publicDashboardData();
        $data['tvConfig'] = $config;
        $data['title'] = 'Painel TV - ' . app_name();
        $this->view('pages/tv-panel/show', $data, 'tv');
    }

    public function data(): void
    {
        if (!$this->allowDisplay()) return;
        $this->privateDisplayHeaders();
        $payload = $this->publicDashboardData();
        Response::json([
            'success' => true,
            'generatedAt' => date(DATE_ATOM),
            'frequency' => $payload['schoolFrequencyToday'] ?? [],
            'ranking' => array_slice((array)($payload['ranking'] ?? []), 0, 10),
            'notices' => $payload['activeNotices'] ?? [],
            'calendar' => $payload['schoolCalendar'] ?? [],
            'executive' => $payload['executive'] ?? [],
            'hallOfFame' => $payload['hallOfFame'] ?? [],
            'intelligentContent' => $payload['intelligentContent'] ?? [],
        ]);
    }

    public function pair(): void
    {
        header('Referrer-Policy: no-referrer');
        if (!$this->access->pair((string) ($_GET['token'] ?? ''))) {
            http_response_code(403); echo 'Não foi possível autorizar este dispositivo.'; return;
        }
        header('Location: ' . base_url('painel-tv'), true, 303);
    }

    public function settings(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::SETTINGS_MANAGE)) return;
        $this->view('pages/tv-panel/settings', [
            'title' => 'Configurar Painel TV - ' . app_name(),
            'tvConfig' => $this->configuration(),
            'success' => Session::get('tv_panel_success'),
        ]);
        Session::remove('tv_panel_success');
    }

    public function save(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::SETTINGS_MANAGE)) return;

        $allowedSlides = ['ranking','frequency','notices','highlights','hallOfFame','calendar','indicators','automaticMessages','didYouKnow','studentRecognition','motivation'];
        $slides = array_values(array_intersect($allowedSlides, array_map('strval', (array)($_POST['slides'] ?? []))));
        if ($slides === []) $slides = ['ranking','frequency','notices','highlights'];

        $currentConfig = $this->configuration();
        $dailyTipBanner = (string)($currentConfig['dailyTipBanner'] ?? '');
        if (!empty($_POST['remove_daily_tip_banner'])) {
            $this->removePublicFile($dailyTipBanner);
            $dailyTipBanner = '';
        }
        $croppedTipBanner = trim((string)($_POST['daily_tip_banner_cropped_data'] ?? ''));
        if ($croppedTipBanner !== '') {
            $dailyTipBanner = $this->saveDailyTipBannerData($croppedTipBanner, $dailyTipBanner);
        } elseif (isset($_FILES['daily_tip_banner']) && (int)($_FILES['daily_tip_banner']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $dailyTipBanner = $this->saveDailyTipBanner($_FILES['daily_tip_banner'], $dailyTipBanner);
        }

        $config = [
            'slides' => $slides,
            'duration' => max(5, min(60, (int)($_POST['duration'] ?? 15))),
            'transition' => in_array((string)($_POST['transition'] ?? 'fade'), ['fade','slide','zoom'], true) ? (string)$_POST['transition'] : 'fade',
            'theme' => in_array((string)($_POST['theme'] ?? 'light'), ['light','dark','auto'], true) ? (string)$_POST['theme'] : 'light',
            'colorTheme' => in_array((string)($_POST['color_theme'] ?? 'green'), ['green','blue','purple','red','automatic'], true) ? (string)$_POST['color_theme'] : 'green',
            'animationsEnabled' => !empty($_POST['animations_enabled']),
            'animationDuration' => max(300, min(2000, (int)($_POST['animation_duration'] ?? 800))),
            'showClock' => !empty($_POST['show_clock']),
            'autoRefresh' => max(30, min(600, (int)($_POST['auto_refresh'] ?? 60))),
            'institutionalSlogan' => $this->cleanText($_POST['institutional_slogan'] ?? 'Educação, presença e futuro.', 120, 'Educação, presença e futuro.'),
            'dailyTipTitle' => $this->cleanText($_POST['daily_tip_title'] ?? 'Dica do dia', 80, 'Dica do dia'),
            'dailyTipText' => $this->cleanText($_POST['daily_tip_text'] ?? 'Pequenas atitudes constroem grandes resultados.', 220, 'Pequenas atitudes constroem grandes resultados.'),
            'dailyTipFooter' => $this->cleanText($_POST['support_text'] ?? $_POST['daily_tip_footer'] ?? 'Sua presença transforma o hoje e constrói o amanhã.', 220, 'Sua presença transforma o hoje e constrói o amanhã.'),
            'supportTitle' => $this->cleanText($_POST['support_title'] ?? 'Contamos com você!', 80, 'Contamos com você!'),
            'supportText' => $this->cleanText($_POST['support_text'] ?? 'Sua presença transforma o hoje e constrói o amanhã.', 220, 'Sua presença transforma o hoje e constrói o amanhã.'),
            'dailyTipBanner' => $dailyTipBanner,
            'motivationText' => $this->cleanText($_POST['motivation_text'] ?? 'Cada presença representa uma nova oportunidade de aprender.', 240, 'Cada presença representa uma nova oportunidade de aprender.'),
            'motivationSubtitle' => $this->cleanText($_POST['motivation_subtitle'] ?? 'Educação se faz com presença, respeito e compromisso.', 220, 'Educação se faz com presença, respeito e compromisso.'),
            'footerSlogan' => $this->cleanText($_POST['footer_slogan'] ?? 'Cada presença conta. Cada aluno importa.', 180, 'Cada presença conta. Cada aluno importa.'),
            'intelligentContentEnabled' => !empty($_POST['intelligent_content_enabled']),
            'contentStyle' => in_array((string)($_POST['content_style'] ?? 'institutional'), ['institutional','formal','inspiring','young'], true) ? (string)$_POST['content_style'] : 'institutional',
            'contentFrequency' => in_array((string)($_POST['content_frequency'] ?? 'daily'), ['daily','weekly','manual'], true) ? (string)$_POST['content_frequency'] : 'daily',
            'contentApprovalMode' => in_array((string)($_POST['content_approval_mode'] ?? 'automatic'), ['automatic','review'], true) ? (string)$_POST['content_approval_mode'] : 'automatic',
            'autoInstitutionalSlogan' => !empty($_POST['auto_institutional_slogan']),
            'autoDailyTip' => !empty($_POST['auto_daily_tip']),
            'autoSupportText' => !empty($_POST['auto_support_text']),
            'autoMotivation' => !empty($_POST['auto_motivation']),
            'autoFooterSlogan' => !empty($_POST['auto_footer_slogan']),
            'autoHighlightMessage' => !empty($_POST['auto_highlight_message']),
            'autoAutomaticMessages' => !empty($_POST['auto_automatic_messages']),
            'autoDidYouKnow' => !empty($_POST['auto_did_you_know']),
            'autoHallOfFame' => !empty($_POST['auto_hall_of_fame']),
            'autoCalendarMessage' => !empty($_POST['auto_calendar_message']),
        ];

        $json = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        // SettingRepository::upsert() preserva o valor atual quando a chave já existe,
        // comportamento útil para seeds, mas inadequado para um formulário de edição.
        // Atualizamos explicitamente o valor quando a configuração já foi criada.
        if ($this->settings->find(self::KEY)) {
            $this->settings->updateValue('tv_panel', 'configuration', $json);
        } else {
            $this->settings->upsert([
                'group_name' => 'tv_panel',
                'key_name' => 'configuration',
                'setting_key' => self::KEY,
                'value' => $json,
                'default_value' => $json,
                'type' => 'json',
                'category' => 'interface',
                'label' => 'Configuração do Painel TV',
                'description' => 'Telas, tempo, transição e atualização do painel institucional.',
                'editable' => 1,
                'requires_restart' => 0,
                'sort_order' => 1,
            ]);
        }

        Session::set('tv_panel_success', 'Configurações do Painel TV salvas com sucesso.');
        Response::redirect(base_url('painel-tv/configuracoes'));
    }


    private function saveDailyTipBannerData(string $dataUri, string $oldPath = ''): string
    {
        if (!preg_match('#^data:image/(jpeg|png|webp);base64,([A-Za-z0-9+/=\r\n]+)$#', $dataUri, $matches)) {
            throw new \InvalidArgumentException('O recorte do banner da Dica do Dia é inválido.');
        }
        $binary = base64_decode(preg_replace('/\s+/', '', $matches[2]), true);
        if ($binary === false || strlen($binary) > 8 * 1024 * 1024) {
            throw new \InvalidArgumentException('O banner recortado da Dica do Dia é inválido ou excede 8 MB.');
        }
        $validated = FileSecurityService::inspectImageBytes($binary, ['jpg', 'png', 'webp'], 8 * 1024 * 1024);
        $info = @getimagesizefromstring($binary);
        if (!$info || (int)$info[0] !== 1200 || (int)$info[1] !== 900) {
            throw new \InvalidArgumentException('Confirme o recorte para gerar o banner em 1200 × 900 px.');
        }
        $relativeDir = 'uploads/tv-panel';
        $absoluteDir = dirname(__DIR__, 2) . '/public/' . $relativeDir;
        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            throw new \InvalidArgumentException('Não foi possível preparar a pasta do Painel TV.');
        }
        $name = FileSecurityService::randomName($validated['extension'], 'dica-do-dia');
        if (file_put_contents($absoluteDir . '/' . $name, $binary, LOCK_EX) === false) {
            throw new \InvalidArgumentException('Não foi possível salvar o banner da Dica do Dia.');
        }
        $path = $relativeDir . '/' . $name;
        if ($oldPath !== '' && $oldPath !== $path) $this->removePublicFile($oldPath);
        return $path;
    }

    private function saveDailyTipBanner(array $file, string $oldPath = ''): string
    {
        $validated = FileSecurityService::inspectUpload($file, ['jpg', 'jpeg', 'png', 'webp'], 8 * 1024 * 1024);
        $tmp = $validated['tmp_name'];
        $info = $tmp !== '' ? @getimagesize($tmp) : false;
        $allowed = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
        if (!$info || !isset($allowed[$info[2]])) {
            throw new \InvalidArgumentException('Envie uma imagem JPG, PNG ou WebP para a Dica do Dia.');
        }
        if ((int)$info[0] < 800 || (int)$info[1] < 600) {
            throw new \InvalidArgumentException('O banner da Dica do Dia deve possuir pelo menos 800 × 600 px.');
        }
        $relativeDir = 'uploads/tv-panel';
        $absoluteDir = dirname(__DIR__, 2) . '/public/' . $relativeDir;
        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            throw new \InvalidArgumentException('Não foi possível preparar a pasta do Painel TV.');
        }
        $name = FileSecurityService::randomName($allowed[$info[2]], 'dica-do-dia');
        if (!move_uploaded_file($tmp, $absoluteDir . '/' . $name)) {
            throw new \InvalidArgumentException('Não foi possível salvar o banner da Dica do Dia.');
        }
        $path = $relativeDir . '/' . $name;
        if ($oldPath !== '' && $oldPath !== $path) $this->removePublicFile($oldPath);
        return $path;
    }

    private function removePublicFile(string $path): void
    {
        $path = str_replace('\\', '/', ltrim($path, '/'));
        if (!preg_match('#^uploads/tv-panel/[A-Za-z0-9.-]+$#', $path)) return;
        FileSecurityService::deleteWithin(dirname(__DIR__, 2) . '/public', $path);
    }

    private function cleanText(mixed $value, int $maxLength, string $fallback): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$value)) ?? '');
        if ($text === '') return $fallback;
        return mb_substr($text, 0, $maxLength);
    }

    private function configuration(): array
    {
        $defaults = [
            'slides' => ['ranking','frequency','notices','highlights','hallOfFame','calendar','indicators','motivation'],
            'duration' => 15,
            'transition' => 'fade',
            'theme' => 'light',
            'colorTheme' => 'green',
            'animationsEnabled' => true,
            'animationDuration' => 800,
            'showClock' => true,
            'autoRefresh' => 60,
            'institutionalSlogan' => 'Educação, presença e futuro.',
            'dailyTipTitle' => 'Dica do dia',
            'dailyTipText' => 'Pequenas atitudes constroem grandes resultados.',
            'dailyTipFooter' => 'Sua presença transforma o hoje e constrói o amanhã.',
            'supportTitle' => 'Contamos com você!',
            'supportText' => 'Sua presença transforma o hoje e constrói o amanhã.',
            'dailyTipBanner' => '',
            'motivationText' => 'Cada presença representa uma nova oportunidade de aprender.',
            'motivationSubtitle' => 'Educação se faz com presença, respeito e compromisso.',
            'footerSlogan' => 'Cada presença conta. Cada aluno importa.',
            'intelligentContentEnabled' => true,
            'contentStyle' => 'institutional',
            'contentFrequency' => 'daily',
            'contentApprovalMode' => 'automatic',
            'autoInstitutionalSlogan' => true,
            'autoDailyTip' => true,
            'autoSupportText' => true,
            'autoMotivation' => true,
            'autoFooterSlogan' => true,
            'autoHighlightMessage' => true,
            'autoAutomaticMessages' => true,
            'autoDidYouKnow' => true,
            'autoHallOfFame' => true,
            'autoCalendarMessage' => true,
        ];
        $row = $this->settings->find(self::KEY);
        if (!$row) return $defaults;
        $decoded = json_decode((string)($row['value'] ?? ''), true);
        return is_array($decoded) ? array_replace($defaults, $decoded) : $defaults;
    }

    private function allowDisplay(): bool
    {
        if ($this->access->canView()) return true;
        $this->privateDisplayHeaders();
        http_response_code(403);
        echo 'Painel TV não autorizado neste dispositivo.';
        return false;
    }

    private function privateDisplayHeaders(): void
    {
        header('Cache-Control: private, no-store, max-age=0');
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: no-referrer');
    }

    private function publicDashboardData(): array
    {
        $source = $this->dashboard->data();
        $ranking = array_map(static fn (array $row): array => array_intersect_key($row, array_flip(['class_name','attendance_percentage','ife_score','has_attendance','class_photo_path','class_photo_updated_at'])), array_slice((array) ($source['ranking'] ?? []), 0, 10));
        $events = array_map(static fn (array $event): array => array_intersect_key($event, array_flip(['title','description','location','type','start_date','end_date'])), $this->calendar->upcoming(24));
        $notices = array_map(static fn (array $notice): array => ['banner_path' => (string) ($notice['banner_path'] ?? '')], $this->notices->activeForTvPanel(8));
        $hall = $this->hallOfFame->build();
        foreach ($hall as &$item) if (is_array($item)) $item = array_intersect_key($item, array_flip(['class_name','attendance_percentage','evolution_percentage','previous_percentage','current_percentage','attendance_days','class_photo_path','class_photo_updated_at']));
        unset($item);
        $data = [
            'schoolName' => (string) ($source['schoolName'] ?? app_name()),
            'totalStudents' => (int) ($source['totalStudents'] ?? 0),
            'totalClasses' => (int) ($source['totalClasses'] ?? 0),
            'schoolGoals' => ['frequency_goal' => (float) ($source['schoolGoals']['frequency_goal'] ?? 95)],
            'schoolFrequencyToday' => array_intersect_key((array) ($source['schoolFrequencyToday'] ?? []), array_flip(['presentes','present','faltas','absent','justificadas','justified','atestados','medical','onibus','bus','percentage','attendance_percentage'])),
            'ranking' => $ranking,
            'executive' => array_intersect_key((array) ($source['executive'] ?? []), array_flip(['doneClasses','totalClasses'])),
            'schoolCalendar' => ['upcoming' => $events],
            'activeNotices' => $notices,
            'activeNoticesCount' => count($notices),
            'hallOfFame' => $hall,
        ];
        $content = $this->intelligentContent->build($data, $hall, $this->configuration());
        unset($content['studentRecognition']);
        $data['intelligentContent'] = $content;
        return $data;
    }
}
