<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\FavoriteService;
use App\Services\SearchService;

class SearchController extends Controller
{
    public function __construct(
        private SearchService $service,
        private FavoriteService $favoriteService
    ) {
    }

    private function guard(): void
    {
        if (!Session::has('user')) {
            Response::redirect(base_url('login'));
        }
    }

    public function suggestions(): void
    {
        $this->guard();
        $term = trim((string) Request::get('q', ''));
        Response::json([
            'success' => true,
            'term' => $term,
            'results' => $this->withFavoriteState($this->service->search($term, 6)),
        ]);
    }

    public function index(): void
    {
        $this->guard();

        $term = trim((string) Request::get('q', ''));

        $this->view('pages/search/index', [
            'title' => 'Busca - ' . app_name(),
            'term' => $term,
            'results' => $this->withFavoriteState($this->service->search($term)),
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
}
