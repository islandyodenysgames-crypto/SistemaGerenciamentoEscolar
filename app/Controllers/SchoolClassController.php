<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\SchoolClassService;

class SchoolClassController extends Controller
{
    private SchoolClassService $service;

    public function __construct()
    {
        $this->service = new SchoolClassService();
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

        $this->view('turmas/index', [
            'title' => 'Turmas - ' . app_name(),
            'classes' => $this->service->all(),
        ]);
    }

    public function create(): void
    {
        $this->guard();

        $this->view('turmas/create', [
            'title' => 'Nova Turma - ' . app_name(),
        ]);
    }

    public function store(): void
    {
        $this->guard();

        $name = trim((string) Request::post('name'));
        $year = (int) Request::post('year');

        if ($this->service->exists($name, $year)) {
            Session::set(
                'class_error',
                'Já existe uma turma com este nome neste ano letivo.'
            );

            Response::redirect(base_url('turmas/novo'));
        }

        $this->service->create([
            'name' => $name,
            'year' => $year,
            'shift' => trim((string) Request::post('shift')),
        ]);

        Session::set(
            'class_success',
            'Turma cadastrada com sucesso.'
        );

        Response::redirect(base_url('turmas'));
    }

    public function edit(): void
    {
        $this->guard();

        $id = (int) Request::get('id');

        $class = $this->service->find($id);

        if (!$class) {
            Response::redirect(base_url('turmas'));
        }

        $this->view('turmas/edit', [
            'title' => 'Editar Turma - ' . app_name(),
            'class' => $class,
        ]);
    }

    public function update(): void
    {
        $this->guard();

        $id = (int) Request::post('id');

        $name = trim((string) Request::post('name'));
        $year = (int) Request::post('year');

        if ($this->service->exists($name, $year, $id)) {
            Session::set(
                'class_error',
                'Já existe outra turma com este nome neste ano letivo.'
            );

            Response::redirect(base_url('turmas/editar?id=' . $id));
        }

        $this->service->update($id, [
            'name' => $name,
            'year' => $year,
            'shift' => trim((string) Request::post('shift')),
            'active' => (int) Request::post('active', 0),
        ]);

        Session::set(
            'class_success',
            'Turma atualizada com sucesso.'
        );

        Response::redirect(base_url('turmas'));
    }

    public function delete(): void
    {
        $this->guard();

        $id = (int) Request::post('id');

        if ($this->service->delete($id)) {
            Session::set(
                'class_success',
                'Turma excluída com sucesso.'
            );
        } else {
            Session::set(
                'class_error',
                'Não foi possível excluir a turma.'
            );
        }

        Response::redirect(base_url('turmas'));
    }
}