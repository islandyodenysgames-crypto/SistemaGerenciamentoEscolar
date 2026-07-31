<?php

declare(strict_types=1);

namespace App\Config;

use App\Auth\Permissions;
use App\Core\Authorization;

class Menu
{
    public static function items(): array
    {
        $path = parse_url(
            $_SERVER['REQUEST_URI'] ?? '/',
            PHP_URL_PATH
        );

        $publicPosition = strpos($path, '/public');
        $relativePath = $publicPosition === false
            ? '/' . trim($path, '/')
            : substr($path, $publicPosition + strlen('/public'));
        $relativePath = '/' . trim((string) $relativePath, '/');

        $is = static function (
            string $route,
            array $excludedPrefixes = []
        ) use ($relativePath): bool {
            $route = '/' . trim($route, '/');

            foreach ($excludedPrefixes as $excludedPrefix) {
                $excludedPrefix = '/' . trim((string) $excludedPrefix, '/');

                if ($relativePath === $excludedPrefix
                    || str_starts_with($relativePath, $excludedPrefix . '/')) {
                    return false;
                }
            }

            return $relativePath === $route
                || str_starts_with($relativePath, $route . '/');
        };

        $isDashboard =
            str_ends_with(
                $path,
                '/public'
            )
            || str_ends_with(
                $path,
                '/public/'
            );

        $items = [
            [
                'type' => 'section',
                'title' => 'Principal',
            ],

            [
                'type' => 'item',
                'title' => 'Página Inicial',
                'icon' => 'layout-dashboard',
                'url' => base_url(),
                'active' => $isDashboard,
                'permission' =>
                    Permissions::DASHBOARD_VIEW,
            ],

            [
                'type' => 'item',
                'title' => 'Central de Inteligência',
                'icon' => 'brain-circuit',
                'url' => base_url(
                    'inteligencia'
                ),
                'active' => $is(
                    'inteligencia',
                    ['configuracoes/inteligencia']
                ),
                'permission' =>
                    Permissions::INTELLIGENCE_VIEW,
            ],

            [
                'type' => 'section',
                'title' => 'Acadêmico',
            ],

            [
                'type' => 'item',
                'title' => 'Turmas',
                'icon' => 'school',
                'url' => base_url(
                    'alunos'
                ),
                'active' => $is(
                    'alunos'
                ),
                'permission' =>
                    Permissions::STUDENTS_VIEW,
            ],

            [
                'type' => 'item',
                'title' => 'Acompanhamentos',
                'icon' => 'user-round-check',
                'url' => base_url('acompanhamentos'),
                'active' => $is('acompanhamentos'),
                'permission' => Permissions::STUDENT_MONITORING_VIEW,
            ],

            [
                'type' => 'item',
                'title' => 'Matrículas',
                'icon' => 'clipboard-list',
                'url' => base_url(
                    'matriculas'
                ),
                'active' => $is(
                    'matriculas'
                ),
                'permission' =>
                    Permissions::ENROLLMENTS_MANAGE,
            ],

            [
                'type' => 'item',
                'title' => 'Frequência',
                'icon' => 'clipboard-check',
                'url' => base_url(
                    'frequencia'
                ),
                'active' => $is(
                    'frequencia'
                ),
                'permission' =>
                    Permissions::ATTENDANCE_MANAGE,
            ],

            [
                'type' => 'section',
                'title' => 'Gestão Escolar',
            ],

            [
                'type' => 'item',
                'title' => 'Ocorrências',
                'icon' => 'triangle-alert',
                'url' => base_url(
                    'ocorrencias'
                ),
                'active' => $is(
                    'ocorrencias'
                ),
                'permission' =>
                    Permissions::OCCURRENCES_VIEW,
            ],

            [
                'type' => 'item',
                'title' => 'Avisos',
                'icon' => 'megaphone',
                'url' => base_url(
                    'avisos'
                ),
                'active' => $is(
                    'avisos'
                ),
                'permission' =>
                    Permissions::NOTICES_VIEW,
            ],

            [
                'type' => 'item',
                'title' => 'Calendário',
                'icon' => 'calendar-days',
                'url' => base_url('calendario'),
                'active' => $is('calendario'),
                'permission' => Permissions::CALENDAR_VIEW,
            ],

            [
                'type' => 'item',
                'title' => 'Painel TV',
                'icon' => 'chart-line',
                'url' => base_url('painel-tv/configuracoes'),
                'active' => $is('painel-tv'),
                'permission' => Permissions::SETTINGS_MANAGE,
            ],

            [
                'type' => 'section',
                'title' => 'Relatórios',
            ],

            [
                'type' => 'item',
                'title' => 'Relatórios',
                'icon' => 'chart-column',
                'url' => base_url(
                    'relatorios'
                ),
                'active' => $is(
                    'relatorios'
                ),
                'permission' =>
                    Permissions::REPORTS_VIEW,
            ],

            [
                'type' => 'section',
                'title' => 'Sistema',
            ],

            [
                'type' => 'item',
                'title' => 'Usuários',
                'icon' => 'users',
                'url' => base_url(
                    'usuarios'
                ),
                'active' => $is(
                    'usuarios'
                ),
                'permission' =>
                    Permissions::USERS_MANAGE,
            ],
            
            [
                'type' => 'item',
                'title' => 'Disciplinas',
                'icon' => 'book-open',
                'url' => base_url(
                    'disciplinas'
                ),
                'active' => $is(
                    'disciplinas'
                ),
                'permission' =>
                    Permissions::SUBJECTS_VIEW,
            ],

            [
                'type' => 'item',
                'title' => 'Configurações',
                'icon' => 'settings',
                'url' => base_url(
                    'configuracoes'
                ),
                'active' => $is(
                    'configuracoes'
                ),
                'permission' =>
                    Permissions::SETTINGS_MANAGE,
            ],
        ];

        return self::filterAuthorizedItems(
            $items
        );
    }

    private static function filterAuthorizedItems(
        array $items
    ): array {
        $result = [];
        $pendingSection = null;

        foreach ($items as $item) {
            $type = (string) (
                $item['type']
                ?? 'item'
            );

            if ($type === 'section') {
                $pendingSection = $item;

                continue;
            }

            $permission = (string) (
                $item['permission']
                ?? ''
            );

            if (
                $permission !== ''
                && Authorization::cannot(
                    $permission
                )
            ) {
                continue;
            }

            if ($pendingSection !== null) {
                $result[] = $pendingSection;

                $pendingSection = null;
            }

            unset(
                $item['permission']
            );

            $result[] = $item;
        }

        return $result;
    }
}