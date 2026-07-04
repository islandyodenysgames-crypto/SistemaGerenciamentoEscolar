<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(
        private UserService $service
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

        $this->view('pages/users/index', [
            'title' => 'Usuários - ' . app_name(),
            'users' => $this->service->all(),
        ]);
    }

    public function create(): void
    {
        $this->guard();

        $this->view('pages/users/create', [
            'title' => 'Novo Usuário - ' . app_name(),
        ]);
    }

    public function store(): void
    {
        $this->guard();

        $email = trim((string) Request::post('email'));

        if ($this->service->emailExists($email)) {
            Session::set(
                'user_error',
                'Já existe um usuário cadastrado com este e-mail.'
            );

            Response::redirect(base_url('usuarios/novo'));
            return;
        }

        $this->service->create([
            'name'     => trim((string) Request::post('name')),
            'email'    => $email,
            'password' => (string) Request::post('password'),
        ]);

        Session::set(
            'user_success',
            'Usuário cadastrado com sucesso.'
        );

        Response::redirect(base_url('usuarios'));
    }

    public function edit(): void
    {
        $this->guard();

        $id = (int) Request::get('id');

        $user = $this->service->find($id);

        if (!$user) {
            Response::redirect(base_url('usuarios'));
            return;
        }

        $this->view('pages/users/edit', [
            'title' => 'Editar Usuário - ' . app_name(),
            'user' => $user,
        ]);
    }

    public function update(): void
    {
        $this->guard();

        $id = (int) Request::post('id');

        $email = trim((string) Request::post('email'));

        if ($this->service->emailExists($email, $id)) {
            Session::set(
                'user_error',
                'Já existe outro usuário utilizando este e-mail.'
            );

            Response::redirect(
                base_url('usuarios/editar?id=' . $id)
            );

            return;
        }

        $this->service->update($id, [
            'name'     => trim((string) Request::post('name')),
            'email'    => $email,
            'password' => (string) Request::post('password'),
            'active'   => (int) Request::post('active', 0),
        ]);

        Session::set(
            'user_success',
            'Usuário atualizado com sucesso.'
        );

        Response::redirect(base_url('usuarios'));
    }

    public function delete(): void
    {
        $this->guard();

        $id = (int) Request::post('id');

        $loggedUser = Session::get('user');

        if ((int) $loggedUser['id'] === $id) {
            Session::set(
                'user_error',
                'Você não pode excluir o usuário que está logado.'
            );

            Response::redirect(base_url('usuarios'));

            return;
        }

        if ($this->service->delete($id)) {
            Session::set(
                'user_success',
                'Usuário excluído com sucesso.'
            );
        } else {
            Session::set(
                'user_error',
                'Não foi possível excluir o usuário.'
            );
        }

        Response::redirect(base_url('usuarios'));
    }
}