<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Session;
use App\Services\DashboardService;
use App\Services\AttendanceAnalyticsService;
use App\Core\Settings\SettingManager;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $service,
        private AttendanceAnalyticsService $analyticsService,
        private SettingManager $settings
    ) {
    }

    private function guard(): void
    {
        if (!Session::has('user')) {
            Response::redirect(base_url('login'));
        }
    }

    public function frequencyEvolution(): void
    {
        $this->guard();

        $allowed = [
            '7d', '15d', '30d', '60d', '90d',
            'month', 'previous_month', 'semester', 'year',
        ];

        $period = strtolower(trim((string) ($_GET['period'] ?? '30d')));

        if (!in_array($period, $allowed, true)) {
            Response::json([
                'success' => false,
                'message' => 'Período de frequência inválido.',
            ]);
        }

        $items = $this->analyticsService->schoolFrequencyByPreset($period);

        ob_start();
        component('dashboard/attendance-chart-pro', [
            'frequencyLast30Days' => $items,
            'frequencyPeriod' => $period,
            'goalPercentage' => (float)$this->settings->get('school_goals.frequency_goal', 95.0),
        ]);
        $html = (string) ob_get_clean();

        $labels = [
            '7d' => 'Últimos 7 dias',
            '15d' => 'Últimos 15 dias',
            '30d' => 'Últimos 30 dias',
            '60d' => 'Últimos 60 dias',
            '90d' => 'Últimos 90 dias',
            'month' => 'Este mês',
            'previous_month' => 'Mês anterior',
            'semester' => 'Este semestre',
            'year' => 'Ano letivo',
        ];

        Response::json([
            'success' => true,
            'period' => $period,
            'periodLabel' => $labels[$period] ?? $labels['30d'],
            'html' => $html,
        ]);
    }


    public function frequencyHeatmap(): void
    {
        $this->guard();

        $allowed = ['week', 'month', 'bimester', 'semester'];
        $period = strtolower(trim((string) ($_GET['period'] ?? 'month')));
        if (!in_array($period, $allowed, true)) {
            Response::json([
                'success' => false,
                'message' => 'Período do mapa de calor inválido.',
            ]);
        }

        $data = $this->analyticsService->schoolFrequencyHeatmapByPreset($period);

        ob_start();
        component('dashboard/frequency-heatmap', ['heatmap' => $data, 'goalPercentage' => (float)$this->settings->get('school_goals.frequency_goal', 95.0)]);
        $html = (string) ob_get_clean();

        Response::json([
            'success' => true,
            'period' => (string) ($data['period'] ?? $period),
            'periodLabel' => (string) ($data['period_label'] ?? ''),
            'html' => $html,
        ]);
    }

    public function index(): void
    {
        $this->guard();

        $this->view('pages/dashboard/index', array_merge(
            [
                'title' => 'Dashboard - ' . app_name(),
            ],
            $this->service->data()
        ));
    }
}