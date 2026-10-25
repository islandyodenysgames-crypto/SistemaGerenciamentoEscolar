<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Auth\Roles;
use App\Core\Authorization;
use App\Core\Request;
use App\Core\Session;
use App\Database\Connection;
use App\Services\FileSecurityService;

final class FileAccessController extends BaseController
{
    public function show(): void
    {
        if (!$this->guard()) return;
        $relative = str_replace('\\', '/', ltrim((string) Request::get('path', ''), '/'));
        $permission = $this->permissionFor($relative);
        if ($permission === null || !Authorization::can($permission) || !$this->withinRecordScope($relative)) {
            http_response_code(404); echo 'Arquivo não encontrado.'; return;
        }
        $absolute = FileSecurityService::safePath(public_path(), $relative);
        if ($absolute === null || !is_file($absolute)) {
            http_response_code(404); echo 'Arquivo não encontrado.'; return;
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($absolute) ?: 'application/octet-stream';
        $inline = str_starts_with($mime, 'image/') || str_starts_with($mime, 'audio/') || str_starts_with($mime, 'video/') || $mime === 'application/pdf';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($absolute));
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . rawurlencode(basename($absolute)) . '"');
        header('Cache-Control: private, no-store, max-age=0');
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');
        readfile($absolute);
    }

    private function permissionFor(string $path): ?string
    {
        return match (true) {
            preg_match('#^uploads/students/#', $path) === 1 => Permissions::STUDENTS_VIEW,
            preg_match('#^uploads/users/#', $path) === 1 => Permissions::DASHBOARD_VIEW,
            preg_match('#^uploads/(occurrences|occurrence-actions)/#', $path) === 1 => Permissions::OCCURRENCES_VIEW,
            preg_match('#^uploads/monitoring-actions/#', $path) === 1 => Permissions::STUDENT_MONITORING_VIEW,
            preg_match('#^uploads/notices/.+/attachments/#', $path) === 1 => Permissions::NOTICES_VIEW,
            default => null,
        };
    }

    private function withinRecordScope(string $path): bool
    {
        $user = (array) Session::get('user', []);
        $userId = (int) ($user['id'] ?? 0);
        $role = (string) ($user['role'] ?? '');
        if (preg_match('#^uploads/users/(\d+)/#', $path, $match) === 1) {
            return $userId === (int) $match[1] || Authorization::can(Permissions::USERS_MANAGE);
        }
        if (preg_match('#^uploads/monitoring-actions/\d{4}/\d{2}/(\d+)/#', $path, $match) === 1) {
            if (Roles::hasFullAccess($role)) return true;
            $statement = Connection::getInstance()->prepare('SELECT 1 FROM student_monitoring_actions a INNER JOIN student_monitoring_users u ON u.monitoring_id=a.monitoring_id WHERE a.id=? AND u.user_id=? LIMIT 1');
            $statement->execute([(int) $match[1], $userId]);
            return (bool) $statement->fetchColumn();
        }
        return true;
    }
}
