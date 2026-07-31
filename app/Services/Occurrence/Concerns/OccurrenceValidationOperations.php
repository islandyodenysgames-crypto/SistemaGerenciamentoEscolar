<?php

declare(strict_types=1);

namespace App\Services\Occurrence\Concerns;

use InvalidArgumentException;

trait OccurrenceValidationOperations
{
    /**
         * Normaliza e valida os dados da ocorrência.
         */
        private function normalizeData(
            array $data
        ): array {
            $studentId = (int) (
                $data['student_id'] ?? 0
            );

            $subjectId = (int) (
                $data['subject_id'] ?? 0
            );

            $occurrenceDate = trim(
                (string) (
                    $data['occurrence_date'] ?? ''
                )
            );

            $type = strtoupper(
                trim(
                    (string) (
                        $data['type']
                        ?? 'OBSERVATION'
                    )
                )
            );

            $severity = $this->severityService
                ->validate(
                    $data['severity'] ?? 'LOW'
                );

            $title = trim(
                (string) (
                    $data['title'] ?? ''
                )
            );

            $description = trim(
                (string) (
                    $data['description'] ?? ''
                )
            );

            $actionsTaken = trim(
                (string) (
                    $data['actions_taken'] ?? ''
                )
            );

            $status = strtoupper(
                trim(
                    (string) (
                        $data['status'] ?? 'OPEN'
                    )
                )
            );

            /*
             * Dados de autoria.
             */
            $createdByValue = (int) (
                $data['created_by'] ?? 0
            );

            $createdByNameValue = trim(
                (string) (
                    $data['created_by_name'] ?? ''
                )
            );

            $createdByRoleValue = trim(
                (string) (
                    $data['created_by_role'] ?? ''
                )
            );

            if ($studentId <= 0) {
                throw new InvalidArgumentException(
                    'O aluno informado é inválido.'
                );
            }

            if ($subjectId <= 0) {
                throw new InvalidArgumentException(
                    'Selecione uma disciplina.'
                );
            }

            if (
                !$this->isValidDate(
                    $occurrenceDate
                )
            ) {
                throw new InvalidArgumentException(
                    'A data da ocorrência é inválida.'
                );
            }

            if (
                !in_array(
                    $type,
                    self::TYPES,
                    true
                )
            ) {
                throw new InvalidArgumentException(
                    'O tipo da ocorrência é inválido.'
                );
            }

            if ($title === '') {
                throw new InvalidArgumentException(
                    'Informe o título da ocorrência.'
                );
            }

            if (mb_strlen($title) > 150) {
                throw new InvalidArgumentException(
                    'O título da ocorrência deve ter no máximo 150 caracteres.'
                );
            }

            if ($description === '') {
                throw new InvalidArgumentException(
                    'Informe a descrição da ocorrência.'
                );
            }

            if (
                !in_array(
                    $status,
                    self::STATUSES,
                    true
                )
            ) {
                $status = 'OPEN';
            }

            return [
                'student_id' =>
                    $studentId,

                'subject_id' =>
                    $subjectId,

                'occurrence_date' =>
                    $occurrenceDate,

                'type' =>
                    $type,

                'severity' =>
                    $severity,

                'title' =>
                    $title,

                'description' =>
                    $description,

                'actions_taken' =>
                    $actionsTaken !== ''
                        ? $actionsTaken
                        : null,

                'status' =>
                    $status,

                /*
                 * Fotografia do autor no momento
                 * do registro da ocorrência.
                 */
                'created_by' =>
                    $createdByValue > 0
                        ? $createdByValue
                        : null,

                'created_by_name' =>
                    $createdByNameValue !== ''
                        ? $createdByNameValue
                        : null,

                'created_by_role' =>
                    $createdByRoleValue !== ''
                        ? $createdByRoleValue
                        : null,
            ];
        }

    /**
         * Remove IDs inválidos e repetidos.
         */
        private function normalizeStudentIds(
            array $studentIds
        ): array {
            $normalized = [];

            foreach ($studentIds as $studentId) {
                $studentId = (int) $studentId;

                if ($studentId <= 0) {
                    continue;
                }

                $normalized[$studentId] =
                    $studentId;
            }

            return array_values($normalized);
        }

    /**
         * Valida datas no formato Y-m-d.
         */
        private function isValidDate(
            string $date
        ): bool {
            $object = \DateTime::createFromFormat(
                'Y-m-d',
                $date
            );

            return $object !== false
                && $object->format('Y-m-d')
                    === $date;
        }

}
