<?php

declare(strict_types=1);

namespace App\Services\Occurrence\Concerns;

use App\Database\Connection;
use InvalidArgumentException;
use Throwable;

trait OccurrenceCrudOperations
{
    public function byStudent(
            int $studentId
        ): array {
            if ($studentId <= 0) {
                return [];
            }

            $items = $this->repository->byStudent(
                $studentId
            );

            $decorated = $this->decorateOccurrences(
                $items
            );

            $attachments = $this->attachmentService->groupedByOccurrences(
                array_column($decorated, 'id')
            );

            foreach ($decorated as &$item) {
                $item['attachments'] = $attachments[(int) ($item['id'] ?? 0)] ?? [];
            }
            unset($item);

            return $decorated;
        }

    public function find(int $id): ?array
        {
            $item = $this->repository->find($id);
            if (!$item) {
                return null;
            }
            $item['attachments'] = $this->attachmentService->byOccurrence($id);
            return $item;
        }

    public function create(array $data): int
        {
            return $this->repository->create(
                $this->normalizeData($data)
            );
        }

    /**
         * Cria a mesma ocorrência para vários alunos.
         *
         * Cada aluno recebe um registro individual,
         * preservando o histórico de cada estudante.
         */
        public function createMultiple(
            array $studentIds,
            array $data
        ): int {
            $studentIds = $this->normalizeStudentIds(
                $studentIds
            );

            if (empty($studentIds)) {
                throw new InvalidArgumentException(
                    'Selecione pelo menos um aluno.'
                );
            }

            if (count($studentIds) > 100) {
                throw new InvalidArgumentException(
                    'Selecione no máximo 100 alunos por ocorrência.'
                );
            }

            /*
             * O primeiro ID é utilizado apenas para
             * reaproveitar toda a validação do normalizeData().
             */
            $normalizedData = $this->normalizeData([
                ...$data,

                'student_id' => $studentIds[0],
            ]);

            /*
             * Os IDs dos alunos são enviados separadamente
             * para o repository.
             */
            unset($normalizedData['student_id']);

            $db = Connection::getInstance();

            try {
                $db->beginTransaction();

                $created = $this->repository
                    ->createMultiple(
                        $studentIds,
                        $normalizedData
                    );

                if ($created !== count($studentIds)) {
                    throw new InvalidArgumentException(
                        'Não foi possível registrar a ocorrência para todos os alunos.'
                    );
                }

                $db->commit();

                return $created;
            } catch (Throwable $exception) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }

                throw $exception;
            }
        }

    /**
         * Atualiza uma ocorrência sem alterar
         * os dados de autoria originais.
         */
        public function update(
            int $id,
            array $data
        ): bool {
            $normalizedData = $this->normalizeData(
                $data
            );

            unset(
                $normalizedData['created_by'],
                $normalizedData['created_by_name'],
                $normalizedData['created_by_role']
            );

            return $this->repository->update(
                $id,
                $normalizedData
            );
        }

    public function resolve(int $id): bool
        {
            return $this->repository->updateStatus(
                $id,
                'RESOLVED'
            );
        }

    public function reopen(int $id): bool
        {
            return $this->repository->updateStatus(
                $id,
                'OPEN'
            );
        }

    public function delete(int $id): bool
        {
            return $this->repository->delete($id);
        }

}
