<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AttendanceRepository;
use App\Repositories\StudentRepository;
use App\Services\Occurrence\NotificationService;

class AttendanceService
{
    public const STATUS_PRESENTE = 'P';
    public const STATUS_FALTA = 'F';
    public const STATUS_FALTA_JUSTIFICADA = 'FJ';
    public const STATUS_ATESTADO_MEDICO = 'AM';
    public const STATUS_FALTA_ONIBUS = 'FO';

    public function __construct(
        private AttendanceRepository $repository,
        private StudentRepository $studentRepository,
        private NotificationService $notificationService
    ) {
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PRESENTE => 'Presença',
            self::STATUS_FALTA => 'Falta',
            self::STATUS_FALTA_JUSTIFICADA => 'Falta Justificada',
            self::STATUS_ATESTADO_MEDICO => 'Atestado Médico',
            self::STATUS_FALTA_ONIBUS => 'Falta de Ônibus',
        ];
    }

    public function all(): array { return $this->repository->all(); }

    public function find(int $id): ?array { return $this->repository->find($id); }

    public function findByClassAndDate(int $classId, string $date): ?array
    {
        return $this->repository->findByClassAndDate($classId, $date);
    }

    public function items(int $attendanceId): array { return $this->repository->items($attendanceId); }

    public function create(array $data): int { return $this->repository->create($data); }

    public function update(int $id, array $data): void { $this->repository->update($id, $data); }

    public function delete(int $id): bool { return $this->repository->delete($id); }

    public function existsForClassAndDate(int $classId, string $date): bool
    {
        return $this->repository->existsForClassAndDate($classId, $date);
    }

    public function classInfo(int $classId): ?array { return $this->repository->classInfo($classId); }

    public function classStudents(int $classId): array { return $this->repository->classStudents($classId); }

    public function classAttendanceHistory(int $classId): array
    {
        return $this->repository->classAttendanceHistory($classId);
    }

    public function insertAttendanceItem(int $attendanceId, int $studentId, string $status): void
    {
        $this->repository->insertAttendanceItem($attendanceId, $studentId, $status);
        $student = $this->studentRepository->find($studentId);
        if ($student) $this->notificationService->notifyAttendanceStatusFollowers($studentId, (string)$student['name'], $status);
    }

    public function updateItemStatus(int $itemId, string $status): void
    {
        $studentId = $this->repository->studentIdForItem($itemId);
        $this->repository->updateItemStatus($itemId, $status);
        if ($studentId) {
            $student = $this->studentRepository->find($studentId);
            if ($student) $this->notificationService->notifyAttendanceStatusFollowers($studentId, (string)$student['name'], $status);
        }
    }

    public function history(?string $date = null, ?int $classId = null): array
    {
        return $this->repository->history($date, $classId);
    }

}