<?php

declare(strict_types=1);

namespace App\Services\Occurrence\Concerns;

trait OccurrenceDashboardOperations
{
    public function countToday(): int
        {
            return $this->repository->countToday();
        }

    public function countOpen(): int
        {
            return $this->repository->countOpen();
        }

    public function countResolved(): int
        {
            return $this->repository->countResolved();
        }

    public function countCurrentMonth(): int
        {
            return $this->repository
                ->countCurrentMonth();
        }

    /**
         * Total de ocorrências gravíssimas abertas.
         */
        public function countCriticalOpen(): int
        {
            return $this->repository
                ->countCriticalOpen();
        }

    public function recent(
            int $limit = 8
        ): array {
            $items = $this->repository->recent(
                $limit
            );

            $decorated = $this->decorateOccurrences(
                $items
            );

            return $this->attachFilesToDashboardItems($decorated);
        }


    /**
     * Retorna ocorrências filtradas para a dashboard.
     */
    public function filteredRecent(
        array $filters,
        int $limit = 50
    ): array {
        $items = $this->repository->filteredRecent(
            $filters,
            $limit
        );

        $decorated = $this->decorateOccurrences($items);

        return $this->attachFilesToDashboardItems($decorated);
    }

    /**
     * Anexa os arquivos em uma única consulta para evitar N+1 no painel.
     */
    private function attachFilesToDashboardItems(array $items): array
    {
        if ($items === []) {
            return [];
        }

        $ids = array_values(array_filter(array_map(
            static fn (array $item): int => (int) ($item['id'] ?? 0),
            $items
        )));

        $grouped = $this->attachmentService->groupedByOccurrences($ids);

        foreach ($items as &$item) {
            $id = (int) ($item['id'] ?? 0);
            $item['attachments'] = $grouped[$id] ?? [];
            $item['attachments_count'] = count($item['attachments']);
        }
        unset($item);

        return $items;
    }

}
