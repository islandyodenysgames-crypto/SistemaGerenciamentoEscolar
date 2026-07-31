<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Controllers\Concerns\AuthorizesAccess;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\SubjectService;
use App\Services\UserService;
use App\Services\ProfilePhotoService;
use InvalidArgumentException;
use Throwable;

class UserController extends Controller
{
    use AuthorizesAccess;

    public function __construct(
        private UserService $service,
        private SubjectService $subjectService,
        private ProfilePhotoService $profilePhotoService
    ) {
    }

    private function guard(): void
    {
        if (!Session::has('user')) {
            Response::redirect(
                base_url('login')
            );
        }
    }

    public function index(): void
    {
        $this->guard();

        if (
            !$this->authorizeAccess(
                Permissions::USERS_MANAGE
            )
        ) {
            return;
        }

        $this->view('pages/users/index', [
            'title' => 'Usuários - ' . app_name(),
            'users' => $this->service->all(),
        ]);
    }

    public function create(): void
    {
        $this->guard();

        if (
            !$this->authorizeAccess(
                Permissions::USERS_MANAGE
            )
        ) {
            return;
        }

        $userError = Session::get(
            'user_error'
        );

        $oldInput = Session::get(
            'user_old_input',
            []
        );

        Session::remove('user_error');
        Session::remove('user_old_input');

        $oldInput = is_array($oldInput)
            ? $oldInput
            : [];

        $selectedSubjects =
            $this->normalizeSubjectIds(
                $oldInput['subjects'] ?? []
            );

        $this->view('pages/users/create', [
            'title' =>
                'Novo Usuário - ' . app_name(),

            'roles' =>
                $this->service->roles(),

            'subjects' =>
                $this->subjectService->active(),

            'selectedSubjects' =>
                $selectedSubjects,

            'userError' =>
                $userError,

            'oldInput' =>
                $oldInput,
        ]);
    }

    public function store(): void
    {
        $this->guard();

        if (
            !$this->authorizeAccess(
                Permissions::USERS_MANAGE
            )
        ) {
            return;
        }

        $subjectIds =
            $this->subjectIdsFromRequest();

        $data = [
            'name' => trim(
                (string) Request::post('name')
            ),

            'email' => trim(
                (string) Request::post('email')
            ),

            'password' => (string) Request::post(
                'password'
            ),

            'role' => trim(
                (string) Request::post(
                    'role',
                    'TEACHER'
                )
            ),

            'active' => 1,
        ];

        Session::set('user_old_input', [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'active' => $data['active'],
            'subjects' => $subjectIds,
        ]);

        if (
            $this->service->emailExists(
                $data['email']
            )
        ) {
            Session::set(
                'user_error',
                'Já existe um usuário cadastrado com este e-mail.'
            );

            Response::redirect(
                base_url('usuarios/novo')
            );

            return;
        }

        try {
            $userId = $this->service->create(
                $data
            );

            $this->subjectService
                ->syncUserSubjects(
                    $userId,
                    $subjectIds
                );

            Session::remove(
                'user_old_input'
            );

            Session::set(
                'user_success',
                'Usuário cadastrado com sucesso.'
            );

            Response::redirect(
                base_url('usuarios')
            );
        } catch (
            InvalidArgumentException $exception
        ) {
            Session::set(
                'user_error',
                $exception->getMessage()
            );

            Response::redirect(
                base_url('usuarios/novo')
            );
        } catch (Throwable $exception) {
            Session::set(
                'user_error',
                'Não foi possível cadastrar o usuário.'
            );

            Response::redirect(
                base_url('usuarios/novo')
            );
        }
    }

    public function edit(): void
    {
        $this->guard();

        if (
            !$this->authorizeAccess(
                Permissions::USERS_MANAGE
            )
        ) {
            return;
        }

        $id = (int) Request::get('id');

        $user = $this->service->find($id);

        if (!$user) {
            Session::set(
                'user_error',
                'Usuário não encontrado.'
            );

            Response::redirect(
                base_url('usuarios')
            );

            return;
        }

        $userError = Session::get(
            'user_error'
        );

        $oldInput = Session::get(
            'user_old_input',
            []
        );

        Session::remove('user_error');
        Session::remove('user_old_input');

        $oldInput = is_array($oldInput)
            ? $oldInput
            : [];

        $selectedSubjects =
            $this->subjectService
                ->idsByUser($id);

        if (!empty($oldInput)) {
            $user = array_merge(
                $user,
                $oldInput
            );

            $selectedSubjects =
                $this->normalizeSubjectIds(
                    $oldInput['subjects'] ?? []
                );
        }

        $this->view('pages/users/edit', [
            'title' =>
                'Editar Usuário - ' . app_name(),

            'user' =>
                $user,

            'roles' =>
                $this->service->roles(),

            /*
             * Utilizamos todas as disciplinas na edição
             * para preservar eventuais vínculos com uma
             * disciplina que tenha sido inativada.
             */
            'subjects' =>
                $this->subjectService->all(),

            'selectedSubjects' =>
                $selectedSubjects,

            'userError' =>
                $userError,
        ]);
    }

    public function update(): void
    {
        $this->guard();

        if (
            !$this->authorizeAccess(
                Permissions::USERS_MANAGE
            )
        ) {
            return;
        }

        $id = (int) Request::post('id');

        $user = $this->service->find($id);

        if (!$user) {
            Session::set(
                'user_error',
                'Usuário não encontrado.'
            );

            Response::redirect(
                base_url('usuarios')
            );

            return;
        }

        $subjectIds =
            $this->subjectIdsFromRequest();

        $data = [
            'name' => trim(
                (string) Request::post('name')
            ),

            'email' => trim(
                (string) Request::post('email')
            ),

            'password' => (string) Request::post(
                'password'
            ),

            'role' => trim(
                (string) Request::post(
                    'role',
                    'TEACHER'
                )
            ),

            'active' => (int) Request::post(
                'active',
                0
            ),
        ];

        Session::set('user_old_input', [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'active' => $data['active'],
            'subjects' => $subjectIds,
        ]);

        if (
            $this->service->emailExists(
                $data['email'],
                $id
            )
        ) {
            Session::set(
                'user_error',
                'Já existe outro usuário utilizando este e-mail.'
            );

            Response::redirect(
                base_url(
                    'usuarios/editar?id=' . $id
                )
            );

            return;
        }

        try {
            $this->service->update(
                $id,
                $data
            );

            $this->subjectService
                ->syncUserSubjects(
                    $id,
                    $subjectIds
                );

            $newPhotoPath = $this->profilePhotoService->apply(
                'user',
                $id,
                (string) Request::post('photo_cropped_data', ''),
                (int) Request::post('remove_photo', 0) === 1,
                (string) ($user['photo_path'] ?? '')
            );

            Session::remove(
                'user_old_input'
            );

            $loggedUser = Session::get(
                'user'
            );

            if (
                is_array($loggedUser)
                && (int) (
                    $loggedUser['id'] ?? 0
                ) === $id
            ) {
                $updatedUser =
                    $this->service->find($id);

                if ($updatedUser) {
                    Session::set(
                        'user',
                        array_merge(
                            $loggedUser,
                            [
                                'name' =>
                                    $updatedUser['name'],

                                'email' =>
                                    $updatedUser['email'],

                                'role' =>
                                    $updatedUser['role'],

                                'role_label' =>
                                    $updatedUser[
                                        'role_label'
                                    ],

                                'active' => (int) (
                                    $updatedUser[
                                        'active'
                                    ] ?? 0
                                ),
                                'photo_path' => $updatedUser['photo_path'] ?? $newPhotoPath,
                                'photo_updated_at' => $updatedUser['photo_updated_at'] ?? null,
                            ]
                        )
                    );
                }
            }

            Session::set(
                'user_success',
                'Usuário atualizado com sucesso.'
            );

            Response::redirect(
                base_url('usuarios')
            );
        } catch (
            InvalidArgumentException $exception
        ) {
            Session::set(
                'user_error',
                $exception->getMessage()
            );

            Response::redirect(
                base_url(
                    'usuarios/editar?id=' . $id
                )
            );
        } catch (Throwable $exception) {
            Session::set(
                'user_error',
                'Não foi possível atualizar o usuário.'
            );

            Response::redirect(
                base_url(
                    'usuarios/editar?id=' . $id
                )
            );
        }
    }

    public function delete(): void
    {
        $this->guard();

        if (
            !$this->authorizeAccess(
                Permissions::USERS_MANAGE
            )
        ) {
            return;
        }

        $id = (int) Request::post('id');

        $loggedUser = Session::get('user');

        if (
            is_array($loggedUser)
            && (int) (
                $loggedUser['id'] ?? 0
            ) === $id
        ) {
            Session::set(
                'user_error',
                'Você não pode excluir o usuário que está logado.'
            );

            Response::redirect(
                base_url('usuarios')
            );

            return;
        }

        try {
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
        } catch (Throwable $exception) {
            Session::set(
                'user_error',
                'Não foi possível excluir o usuário.'
            );
        }

        Response::redirect(
            base_url('usuarios')
        );
    }

    /**
     * Obtém e normaliza os IDs de disciplinas
     * enviados pelo formulário.
     */
    private function subjectIdsFromRequest(): array
    {
        $subjectIds = Request::post(
            'subjects',
            []
        );

        if (!is_array($subjectIds)) {
            return [];
        }

        return $this->normalizeSubjectIds(
            $subjectIds
        );
    }

    /**
     * Remove IDs inválidos e duplicados.
     */
    private function normalizeSubjectIds(
        mixed $subjectIds
    ): array {
        if (!is_array($subjectIds)) {
            return [];
        }

        $normalized = [];

        foreach ($subjectIds as $subjectId) {
            $subjectId = (int) $subjectId;

            if ($subjectId <= 0) {
                continue;
            }

            $normalized[$subjectId] =
                $subjectId;
        }

        return array_values(
            $normalized
        );
    }
}