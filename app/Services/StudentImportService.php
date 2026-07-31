<?php

declare(strict_types=1);

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;

class StudentImportService
{
    public function __construct(
        private StudentService $students,
        private EnrollmentService $enrollments
    ) {
    }

    public function preview(array $file): array
    {
        if (
            empty($file['tmp_name']) ||
            !is_uploaded_file($file['tmp_name'])
        ) {
            return [];
        }

        $spreadsheet = IOFactory::load($file['tmp_name']);

        $rows = $spreadsheet
            ->getActiveSheet()
            ->toArray();

        $preview = [];

        $nextRegistration = $this->students->nextRegistration();
        $nextNumber = (int) substr($nextRegistration, 4);
        $year = substr($nextRegistration, 0, 4);

        foreach ($rows as $index => $row) {

            $name = trim((string) ($row[0] ?? ''));

            if ($name === '') {
                continue;
            }

            if (
                $index === 0 &&
                str_contains(mb_strtolower($name), 'nome')
            ) {
                continue;
            }

            $student = $this->students->findByName($name);

            $registration = $student['registration'] ?? (
                $year . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT)
            );

            if ($student === null) {
                $nextNumber++;
            }

            $preview[] = [
                'name' => $name,
                'exists' => $student !== null,
                'student_id' => $student['id'] ?? null,
                'registration' => $registration,
            ];
        }

        return $preview;
    }

    public function confirm(array $preview, int $classId): array
    {
        $created = 0;
        $existing = 0;
        $enrolled = 0;
        $ignored = 0;

        foreach ($preview as $item) {

            $name = trim((string) ($item['name'] ?? ''));

            if ($name === '') {
                $ignored++;
                continue;
            }

            $student = $this->students->findByName($name);

            if ($student === null) {

                $this->students->create([
                    'name' => $name,
                    'registration' => $item['registration'],
                    'birth_date' => null,
                    'guardian_name' => '',
                    'guardian_phone' => '',
                ]);

                $student = $this->students->findByName($name);

                $created++;

            } else {

                $existing++;

            }

            if (!$student) {
                $ignored++;
                continue;
            }

            $studentId = (int) $student['id'];

            if (!$this->enrollments->exists($studentId, $classId)) {

                $this->enrollments->create([
                    'student_id' => $studentId,
                    'school_class_id' => $classId,
                    'enrollment_date' => date('Y-m-d'),
                ]);

                $enrolled++;
            }
        }

        return [
            'created' => $created,
            'existing' => $existing,
            'enrolled' => $enrolled,
            'ignored' => $ignored,
        ];
    }
}