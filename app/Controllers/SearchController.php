<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\SearchService;

class SearchController extends Controller
{
    public function __construct(
        private SearchService $service
    ) {
    }

    private function guard(): void
    {
        if (!Session::has('user')) {
            Response::redirect(base_url('login'));
        }
    }

    public function index(): void
    {
        $this->guard();

        $term = trim((string) Request::get('q', ''));

        $this->view('pages/search/index', [
            'title' => 'Busca - ' . app_name(),
            'term' => $term,
            'results' => $this->service->search($term),
        ]);
    }
}