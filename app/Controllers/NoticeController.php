<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\NoticeService;
use App\Services\NoticeMediaService;
use App\Services\SchoolClassService;
use InvalidArgumentException;
use Throwable;

class NoticeController extends BaseController
{
    public function __construct(
        private NoticeService $service,
        private SchoolClassService $classService,
        private NoticeMediaService $mediaService
    ) {
    }

    public function index(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::NOTICES_VIEW
            )
        ) {
            return;
        }

        $noticeSuccess = Session::get(
            'notice_success'
        );

        $noticeError = Session::get(
            'notice_error'
        );

        Session::remove('notice_success');
        Session::remove('notice_error');

        $this->view('pages/notices/index', [
            'title' => 'Avisos da Gestão - ' . app_name(),

            'notices' => $this->service->all(),

            'priorities' => $this->service
                ->priorities(),

            'targets' => $this->service->targets(),
            'categories' => $this->service->categories(),

            'totalActive' => $this->service
                ->countActive(),

            'totalScheduled' => $this->service
                ->countScheduled(),

            'totalExpired' => $this->service
                ->countExpired(),

            'noticeSuccess' => $noticeSuccess,

            'noticeError' => $noticeError,
        ]);
    }

    public function create(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::NOTICES_MANAGE,
                base_url('avisos')
            )
        ) {
            return;
        }

        $noticeError = Session::get(
            'notice_error'
        );

        $oldInput = Session::get(
            'notice_old_input',
            []
        );

        Session::remove('notice_error');
        Session::remove('notice_old_input');

        $this->view('pages/notices/create', [
            'title' => 'Novo Aviso - ' . app_name(),

            'priorities' => $this->service
                ->priorities(),

            'targets' => $this->service->targets(),
            'categories' => $this->service->categories(),

            'classes' => $this->classService
                ->all(),

            'noticeError' => $noticeError,

            'oldInput' => is_array($oldInput)
                ? $oldInput
                : [],
        ]);
    }

    public function store(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::NOTICES_MANAGE,
                base_url('avisos')
            )
        ) {
            return;
        }

        $data = $this->requestData();

        Session::set(
            'notice_old_input',
            $data
        );

        try {
            if (strtoupper((string)($data['target'] ?? '')) === 'TV_PANEL') {
                if (trim((string)Request::post('banner_cropped_data', '')) === '') {
                    throw new InvalidArgumentException('Para o público Painel-TV, envie obrigatoriamente a Imagem do cartão.');
                }
                $data['title'] = 'Banner do Painel TV';
                $data['summary'] = '';
                $data['content'] = 'Conteúdo apresentado integralmente na imagem do cartão.';
                $data['youtube_url'] = '';
            }
            $id = $this->service->create([
                ...$data,

                'created_by' =>
                    $this->currentUserId(),

                'created_by_name' =>
                     $this->currentUserName(),

                'created_by_role' =>
                     $this->currentUserRole(),
            ]);

            $banner = $this->mediaService->saveBanner($id, (string)Request::post('banner_cropped_data', ''));
            if ($banner) $this->service->update($id, [...$data, 'banner_path' => $banner]);
            $this->mediaService->saveAttachments($id, $_FILES['attachments'] ?? [], $this->currentUserId());

            Session::remove(
                'notice_old_input'
            );

            Session::set(
                'notice_success',
                'Aviso cadastrado com sucesso.'
            );

            Response::redirect(
                base_url('avisos')
            );
        } catch (InvalidArgumentException $exception) {
            Session::set(
                'notice_error',
                $exception->getMessage()
            );

            Response::redirect(
                base_url('avisos/novo')
            );
        } catch (Throwable $exception) {
            Session::set(
                'notice_error',
                'Não foi possível cadastrar o aviso.'
            );

            Response::redirect(
                base_url('avisos/novo')
            );
        }
    }

    public function edit(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::NOTICES_MANAGE,
                base_url('avisos')
            )
        ) {
            return;
        }

        $id = (int) Request::get('id');

        if ($id <= 0) {
            Session::set(
                'notice_error',
                'Aviso inválido.'
            );

            Response::redirect(
                base_url('avisos')
            );

            return;
        }

        $notice = $this->service->find($id);

        if (!$notice) {
            Session::set(
                'notice_error',
                'Aviso não encontrado.'
            );

            Response::redirect(
                base_url('avisos')
            );

            return;
        }

        $noticeError = Session::get(
            'notice_error'
        );

        $oldInput = Session::get(
            'notice_old_input',
            []
        );

        Session::remove('notice_error');
        Session::remove('notice_old_input');

        if (
            is_array($oldInput)
            && !empty($oldInput)
        ) {
            $notice = array_merge(
                $notice,
                $oldInput
            );
        }

        $this->view('pages/notices/edit', [
            'title' => 'Editar Aviso - ' . app_name(),

            'notice' => $notice,

            'priorities' => $this->service
                ->priorities(),

            'targets' => $this->service->targets(),
            'categories' => $this->service->categories(),

            'classes' => $this->classService
                ->all(),

            'noticeError' => $noticeError,
            'attachments' => $this->mediaService->attachments($id),
        ]);
    }

    public function update(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::NOTICES_MANAGE,
                base_url('avisos')
            )
        ) {
            return;
        }

        $id = (int) Request::post('id');

        if ($id <= 0) {
            Session::set(
                'notice_error',
                'Aviso inválido.'
            );

            Response::redirect(
                base_url('avisos')
            );

            return;
        }

        $notice = $this->service->find($id);

        if (!$notice) {
            Session::set(
                'notice_error',
                'Aviso não encontrado.'
            );

            Response::redirect(
                base_url('avisos')
            );

            return;
        }

        $data = $this->requestData();

        Session::set(
            'notice_old_input',
            $data
        );

        try {
            if (strtoupper((string)($data['target'] ?? '')) === 'TV_PANEL') {
                $hasExisting = trim((string)($notice['banner_path'] ?? '')) !== '';
                $hasNew = trim((string)Request::post('banner_cropped_data', '')) !== '';
                if (!$hasExisting && !$hasNew) throw new InvalidArgumentException('Para o público Painel-TV, envie obrigatoriamente a Imagem do cartão.');
                $data['title'] = 'Banner do Painel TV';
                $data['summary'] = '';
                $data['content'] = 'Conteúdo apresentado integralmente na imagem do cartão.';
                $data['youtube_url'] = '';
            }
            $banner = $this->mediaService->saveBanner($id, (string)Request::post('banner_cropped_data', ''), $notice['banner_path'] ?? null);
            $data['banner_path'] = $banner;
            $this->service->update($id, $data);
            $this->mediaService->saveAttachments($id, $_FILES['attachments'] ?? [], $this->currentUserId());

            Session::remove(
                'notice_old_input'
            );

            Session::set(
                'notice_success',
                'Aviso atualizado com sucesso.'
            );

            Response::redirect(
                base_url('avisos')
            );
        } catch (InvalidArgumentException $exception) {
            Session::set(
                'notice_error',
                $exception->getMessage()
            );

            Response::redirect(
                base_url(
                    'avisos/editar?id=' . $id
                )
            );
        } catch (Throwable $exception) {
            Session::set(
                'notice_error',
                'Não foi possível atualizar o aviso.'
            );

            Response::redirect(
                base_url(
                    'avisos/editar?id=' . $id
                )
            );
        }
    }

    public function activate(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::NOTICES_MANAGE,
                base_url('avisos')
            )
        ) {
            return;
        }

        $id = (int) Request::post('id');

        if (!$this->noticeExists($id)) {
            return;
        }

        if ($this->service->activate($id)) {
            Session::set(
                'notice_success',
                'Aviso ativado com sucesso.'
            );
        } else {
            Session::set(
                'notice_error',
                'Não foi possível ativar o aviso.'
            );
        }

        Response::redirect(
            base_url('avisos')
        );
    }

    public function archive(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::NOTICES_MANAGE,
                base_url('avisos')
            )
        ) {
            return;
        }

        $id = (int) Request::post('id');

        if (!$this->noticeExists($id)) {
            return;
        }

        if ($this->service->archive($id)) {
            Session::set(
                'notice_success',
                'Aviso arquivado com sucesso.'
            );
        } else {
            Session::set(
                'notice_error',
                'Não foi possível arquivar o aviso.'
            );
        }

        Response::redirect(
            base_url('avisos')
        );
    }

    public function pin(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::NOTICES_MANAGE,
                base_url('avisos')
            )
        ) {
            return;
        }

        $id = (int) Request::post('id');

        if (!$this->noticeExists($id)) {
            return;
        }

        if ($this->service->pin($id)) {
            Session::set(
                'notice_success',
                'Aviso fixado no topo.'
            );
        } else {
            Session::set(
                'notice_error',
                'Não foi possível fixar o aviso.'
            );
        }

        Response::redirect(
            base_url('avisos')
        );
    }

    public function unpin(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::NOTICES_MANAGE,
                base_url('avisos')
            )
        ) {
            return;
        }

        $id = (int) Request::post('id');

        if (!$this->noticeExists($id)) {
            return;
        }

        if ($this->service->unpin($id)) {
            Session::set(
                'notice_success',
                'Aviso desafixado com sucesso.'
            );
        } else {
            Session::set(
                'notice_error',
                'Não foi possível desafixar o aviso.'
            );
        }

        Response::redirect(
            base_url('avisos')
        );
    }

    public function delete(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::NOTICES_MANAGE,
                base_url('avisos')
            )
        ) {
            return;
        }

        $id = (int) Request::post('id');

        if (!$this->noticeExists($id)) {
            return;
        }

        if ($this->service->delete($id)) {
            Session::set(
                'notice_success',
                'Aviso excluído com sucesso.'
            );
        } else {
            Session::set(
                'notice_error',
                'Não foi possível excluir o aviso.'
            );
        }

        Response::redirect(
            base_url('avisos')
        );
    }


    public function deleteAttachment(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::NOTICES_MANAGE, base_url('avisos'))) return;
        $noticeId = $this->mediaService->deleteAttachment((int)Request::post('attachment_id'));
        Session::set($noticeId ? 'notice_success' : 'notice_error', $noticeId ? 'Anexo excluído com sucesso.' : 'Anexo não encontrado.');
        Response::redirect(base_url($noticeId ? 'avisos/editar?id=' . $noticeId : 'avisos'));
    }

    private function requestData(): array
    {
        return [
            'title' => trim(
                (string) Request::post('title')
            ),

            'summary' => trim((string) Request::post('summary')),
            'category' => trim((string) Request::post('category', 'GENERAL')),
            'youtube_url' => trim((string) Request::post('youtube_url')),
            'banner_path' => trim((string) Request::post('existing_banner_path')),
            'featured' => (int) Request::post('featured', 0),

            'content' => trim(
                (string) Request::post('content')
            ),

            'priority' => trim(
                (string) Request::post(
                    'priority',
                    'INFO'
                )
            ),

            'target' => trim(
                (string) Request::post(
                    'target',
                    'ALL'
                )
            ),

            'target_class_id' => (int) Request::post(
                'target_class_id',
                0
            ),

            'published_at' => trim(
                (string) Request::post(
                    'published_at'
                )
            ),

            'expires_at' => trim(
                (string) Request::post(
                    'expires_at'
                )
            ),

            'pinned' => (int) Request::post(
                'pinned',
                0
            ),

            'active' => (int) Request::post(
                'active',
                0
            ),
        ];
    }

    private function noticeExists(int $id): bool
    {
        if (
            $id <= 0
            || !$this->service->find($id)
        ) {
            Session::set(
                'notice_error',
                'Aviso não encontrado.'
            );

            Response::redirect(
                base_url('avisos')
            );

            return false;
        }

        return true;
    }

    private function currentUserId(): ?int
    {
        $user = Session::get('user');

        if (is_array($user)) {
            $id = (int) (
                $user['id'] ?? 0
            );

            return $id > 0
                ? $id
                : null;
        }

        if (is_object($user)) {
            $id = (int) (
                $user->id ?? 0
            );

            return $id > 0
                ? $id
                : null;
        }

        return null;
    }

    private function currentUserName(): string
    {
        $user = Session::get('user');
        
        if (is_array($user)) {
            return trim(
                (string) (
                    $user['name'] ?? ''
                )
            );
        }
        
        if (is_object($user)) {
            return trim(
                (string) (
                    $user->name ?? ''
                )
            );
        }
        
        return '';
    }
    
    private function currentUserRole(): string
    {
        $user = Session::get('user');
        
        if (is_array($user)) {
            return trim(
                (string) (
                    $user['role_label']
                    ?? $user['role']
                    ?? ''
                )
            );
        }
        
        if (is_object($user)) {
            return trim(
                (string) (
                    $user->role_label
                    ?? $user->role
                    ?? ''
                )
            );
        }
        
        return '';
    }
}