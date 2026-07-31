<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\Occurrence\NotificationService;
use App\Services\Intelligence\StudentIntelligenceAlertService;
use App\Services\Intelligence\PreventiveAlertService;

final class NotificationController extends BaseController
{
    public function __construct(
        private readonly NotificationService $service,
        private readonly StudentIntelligenceAlertService $intelligenceAlertService,
        private readonly PreventiveAlertService $preventiveAlertService
    ) {
    }

    public function index(): void
    {
        if (!$this->guard()) {
            return;
        }

        $userId = $this->currentUserId();
        $this->intelligenceAlertService->syncForUser($userId);
        $this->preventiveAlertService->syncForUser($userId);

        $this->view('pages/notifications/index', [
            'title' => 'Notificações - ' . app_name(),
            'notifications' => $this->service
                ->allForUser(
                    $userId,
                    100,
                    (string) Request::get('categoria', 'ALL'),
                    (string) Request::get('status', 'ALL')
                ),
            'selectedCategory' => strtoupper((string) Request::get('categoria', 'ALL')),
            'selectedStatus' => strtoupper((string) Request::get('status', 'ALL')),
            'unreadCount' => $this->service
                ->summaryForUser($userId)['unread_count'],
            'notificationSuccess' => Session::get(
                'notification_success'
            ),
        ]);

        Session::remove('notification_success');
    }

    public function summary(): never
    {
        if (!$this->guard()) {
            Response::json([
                'ok' => false,
                'unread_count' => 0,
                'items' => [],
            ]);
        }

        $this->intelligenceAlertService->syncForUser($this->currentUserId());
        $this->preventiveAlertService->syncForUser($this->currentUserId());

        Response::json([
            'ok' => true,
            ...$this->service->summaryForUser(
                $this->currentUserId()
            ),
        ]);
    }


    public function open(): void
    {
        if (!$this->guard()) return;
        $destination = $this->service->openDestination(
            (int) Request::get('id', 0),
            $this->currentUserId()
        );
        Response::redirect($destination);
    }

    public function read(): void
    {
        if (!$this->guard()) {
            return;
        }

        $this->service->markAsRead(
            (int) Request::post('id', 0),
            $this->currentUserId()
        );

        Response::redirect(base_url('notificacoes'));
    }

    public function readAll(): void
    {
        if (!$this->guard()) {
            return;
        }

        $this->service->markAllAsRead(
            $this->currentUserId()
        );

        Session::set(
            'notification_success',
            'Todas as notificações foram marcadas como lidas.'
        );

        Response::redirect(base_url('notificacoes'));
    }

    public function delete(): void
    {
        if (!$this->guard()) {
            return;
        }

        $this->service->delete(
            (int) Request::post('id', 0),
            $this->currentUserId()
        );

        Response::redirect(base_url('notificacoes'));
    }

    private function currentUserId(): int
    {
        $user = Session::get('user', []);

        return is_array($user)
            ? (int) ($user['id'] ?? 0)
            : (int) ($user->id ?? 0);
    }
}
