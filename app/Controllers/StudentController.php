<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Session;
use App\Services\StudentService;

class StudentController extends Controller
{
    private StudentService $service;

    public function __construct()
    {
        $this->service = new StudentService();
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

        $this->view('alunos/index', [
            'title' => 'Alunos - ' . app_name(),
            'students' => $this->service->all(),
        ]);
    }
}