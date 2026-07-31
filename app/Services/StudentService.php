<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use App\Repositories\EnrollmentRepository;
use App\Repositories\StudentRepository;
use DateTime;
use InvalidArgumentException;
use Throwable;

class StudentService
{
    public function __construct(
        private StudentRepository $repository,
        private EnrollmentRepository $enrollmentRepository
    ) {
    }

    public function all(): array
    {
        return $this->repository->all();
    }

    public function countActive(): int
    {
        return $this->repository->countActive();
    }

    public function countInAlert(float $threshold = 95.0): int
    {
        return $this->repository->countInAlert($threshold);
    }

    public function availableForEnrollment(): array
    {
        return $this->repository
            ->availableForEnrollment();
    }

    public function find(int $id): ?array
    {
        return $this->repository->find($id);
    }

    public function activeEnrollment(
        int $studentId
    ): ?array {
        return $this->enrollmentRepository
            ->activeByStudent($studentId);
    }

    public function enrollmentHistory(
        int $studentId
    ): array {
        return $this->enrollmentRepository
            ->historyByStudent($studentId);
    }

    public function create(array $data): int
    {
        return $this->repository->create(
            $this->normalizeCreateData($data)
        );
    }

    public function createWithEnrollment(
        array $studentData,
        ?int $schoolClassId = null,
        ?string $enrollmentDate = null
    ): int {
        $studentData = $this->normalizeCreateData(
            $studentData
        );

        if (
            $schoolClassId !== null
            && $schoolClassId <= 0
        ) {
            throw new InvalidArgumentException(
                'A turma selecionada é inválida.'
            );
        }

        $enrollmentDate = trim(
            (string) ($enrollmentDate ?? '')
        );

        if ($enrollmentDate === '') {
            $enrollmentDate = date('Y-m-d');
        }

        if (!$this->isValidDate($enrollmentDate)) {
            throw new InvalidArgumentException(
                'A data da matrícula é inválida.'
            );
        }

        $db = Connection::getInstance();

        try {
            $db->beginTransaction();

            $studentId = $this->repository->create(
                $studentData
            );

            if ($schoolClassId !== null) {
                $this->enrollmentRepository->create([
                    'student_id' => $studentId,

                    'school_class_id' =>
                        $schoolClassId,

                    'enrollment_date' =>
                        $enrollmentDate,
                ]);
            }

            $db->commit();

            return $studentId;
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw $exception;
        }
    }

    public function update(
        int $id,
        array $data
    ): void {
        $this->repository->update(
            $id,
            $this->normalizeUpdateData($data)
        );
    }

    public function updateWithClassTransfer(
        int $studentId,
        array $studentData,
        int $newClassId,
        ?string $transferDate = null
    ): void {
        if ($studentId <= 0) {
            throw new InvalidArgumentException(
                'Aluno inválido.'
            );
        }

        if ($newClassId <= 0) {
            throw new InvalidArgumentException(
                'Selecione uma turma válida.'
            );
        }

        $studentData = $this->normalizeUpdateData(
            $studentData
        );

        $transferDate = trim(
            (string) ($transferDate ?? '')
        );

        if ($transferDate === '') {
            $transferDate = date('Y-m-d');
        }

        if (!$this->isValidDate($transferDate)) {
            throw new InvalidArgumentException(
                'A data da mudança de turma é inválida.'
            );
        }

        $currentEnrollment = $this->enrollmentRepository
            ->activeByStudent($studentId);

        $currentClassId = (int) (
            $currentEnrollment['school_class_id'] ?? 0
        );

        $db = Connection::getInstance();

        try {
            $db->beginTransaction();

            $this->repository->update(
                $studentId,
                $studentData
            );

            /*
             * Só altera a matrícula quando a turma
             * selecionada for diferente da turma atual.
             */
            if ($currentClassId !== $newClassId) {
                if ($currentEnrollment) {
                    $this->enrollmentRepository
                        ->cancelActiveByStudent(
                            $studentId
                        );
                }

                $this->enrollmentRepository->create([
                    'student_id' => $studentId,

                    'school_class_id' =>
                        $newClassId,

                    'enrollment_date' =>
                        $transferDate,
                ]);
            }

            $db->commit();
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw $exception;
        }
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function registrationExists(
        string $registration,
        ?int $ignoreId = null
    ): bool {
        return $this->repository->registrationExists(
            $registration,
            $ignoreId
        );
    }

    public function findByName(
        string $name
    ): ?array {
        return $this->repository
            ->findByName($name);
    }

    public function nextRegistration(): string
    {
        return $this->repository
            ->nextRegistration();
    }

    public function randomRegistration(): string
    {
        $year = date('Y');

        do {
            $registration = $year
                . '-'
                . str_pad(
                    (string) random_int(
                        0,
                        999999
                    ),
                    6,
                    '0',
                    STR_PAD_LEFT
                );
        } while (
            $this->repository
                ->registrationExists($registration)
        );

        return $registration;
    }

    public function byClass(int $classId): array
    {
        return $this->repository->byClass($classId);
    }

    public function statistics(
        int $studentId
    ): array {
        return $this->repository
            ->statistics($studentId);
    }

    public function attendanceHistory(
        int $studentId
    ): array {
        return $this->repository
            ->attendanceHistory($studentId);
    }

    public function calendar(
        int $studentId,
        int $year,
        int $month
    ): array {
        return $this->repository->calendar(
            $studentId,
            $year,
            $month
        );
    }

    public function attendanceEvolutionDaily(
        int $studentId
    ): array {
        return $this->repository
            ->attendanceEvolutionDaily($studentId);
    }

    public function attendanceEvolutionMonthly(
        int $studentId
    ): array {
        return $this->repository
            ->attendanceEvolutionMonthly($studentId);
    }

    private function normalizeCreateData(
        array $data
    ): array {
        $name = trim(
            (string) ($data['name'] ?? '')
        );

        $registration = trim(
            (string) ($data['registration'] ?? '')
        );

        $birthDate = trim(
            (string) ($data['birth_date'] ?? '')
        );

        $guardianName = trim(
            (string) ($data['guardian_name'] ?? '')
        );

        $guardianPhone = trim(
            (string) ($data['guardian_phone'] ?? '')
        );

        if ($name === '') {
            throw new InvalidArgumentException(
                'Informe o nome do aluno.'
            );
        }

        if ($registration === '') {
            throw new InvalidArgumentException(
                'Informe a matrícula do aluno.'
            );
        }

        if (
            $birthDate !== ''
            && !$this->isValidDate($birthDate)
        ) {
            throw new InvalidArgumentException(
                'A data de nascimento é inválida.'
            );
        }

        return [
            'name' => $name,

            'registration' => $registration,

            'birth_date' => $birthDate !== ''
                ? $birthDate
                : null,

            'guardian_name' => $guardianName !== ''
                ? $guardianName
                : null,

            'guardian_phone' => $guardianPhone !== ''
                ? $guardianPhone
                : null,
        ];
    }

    private function normalizeUpdateData(
        array $data
    ): array {
        $normalized = $this->normalizeCreateData(
            $data
        );

        $normalized['active'] =
            (int) ($data['active'] ?? 0) === 1
                ? 1
                : 0;

        return $normalized;
    }

    private function isValidDate(
        string $date
    ): bool {
        $parsed = DateTime::createFromFormat(
            'Y-m-d',
            $date
        );

        return $parsed !== false
            && $parsed->format('Y-m-d') === $date;
    }
}