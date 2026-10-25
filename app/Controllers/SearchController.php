<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Core\Authorization;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\FavoriteService;
use App\Services\SearchService;

class SearchController extends BaseController
{
    public function __construct(
        private SearchService $service,
        private FavoriteService $favoriteService
    ) {
    }

    public function suggestions(): void
    {
        if (!$this->guard()) return;
        $term = trim((string) Request::get('q', ''));
        Response::json([
            'success' => true,
            'term' => $term,
            'results' => $this->withFavoriteState($this->service->search($term, 6, $this->allowedGroups(), $this->accessContext())),
        ]);
    }

    public function index(): void
    {
        if (!$this->guard()) return;

        $term = trim((string) Request::get('q', ''));

        $this->view('pages/search/index', [
            'title' => 'Busca - ' . app_name(),
            'term' => $term,
            'results' => $this->withFavoriteState($this->service->search($term, 8, $this->allowedGroups(), $this->accessContext())),
        ]);
    }

    private function withFavoriteState(array $results): array
    {
        $userId = (int) (((array) Session::get('user', []))['id'] ?? 0);
        $keys = $this->favoriteService->keysForUser($userId);

        foreach ($results as $group => $items) {
            foreach ((array) $items as $index => $item) {
                $type = (string) ($item['type'] ?? '');
                $id = (int) ($item['id'] ?? 0);
                $results[$group][$index]['is_favorite'] = isset($keys[$type . ':' . $id]);
            }
        }

        return $results;
    }

    private function allowedGroups(): array
    {
        $groups = [];
        if (Authorization::can(Permissions::STUDENTS_VIEW)) array_push($groups, 'students', 'classes');
        if (Authorization::can(Permissions::OCCURRENCES_VIEW)) $groups[] = 'occurrences';
        if (Authorization::can(Permissions::STUDENT_MONITORING_VIEW)) $groups[] = 'monitoring';
        if (Authorization::can(Permissions::NOTICES_VIEW)) $groups[] = 'notices';
        if (Authorization::can(Permissions::USERS_MANAGE)) $groups[] = 'users';
        return $groups;
    }

    private function accessContext(): array
    {
        $user = (array) Session::get('user', []);
        return [
            'user_id' => (int) ($user['id'] ?? 0),
            'view_all_monitoring' => \App\Auth\Roles::hasFullAccess((string) ($user['role'] ?? '')),
            'manage_notices' => Authorization::can(Permissions::NOTICES_MANAGE),
        ];
    }
}
