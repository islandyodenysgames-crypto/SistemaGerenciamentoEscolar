<?php

declare(strict_types=1);

namespace App\Services\Occurrence\Concerns;

trait OccurrenceStatisticsOperations
{
    /**
         * Contagem geral por gravidade.
         */
        public function severityCounts(): array
        {
            return $this->repository
                ->countBySeverity();
        }

    /**
         * Contagem por gravidade no mês atual.
         */
        public function severityCountsCurrentMonth(): array
        {
            return $this->repository
                ->countBySeverityCurrentMonth();
        }

    public function mostFrequentTypes(
            int $limit = 6
        ): array {
            $items = $this->repository
                ->mostFrequentTypes($limit);

            $types = $this->types();

            foreach ($items as &$item) {
                $code = strtoupper(
                    (string) (
                        $item['type'] ?? 'OTHER'
                    )
                );

                $item['type'] = $code;

                $item['type_label'] =
                    $types[$code] ?? 'Outro';

                $item['total'] = (int) (
                    $item['total'] ?? 0
                );
            }

            unset($item);

            return $items;
        }

    /**
         * Gravidades mais frequentes.
         */
        public function mostFrequentSeverities(
            int $limit = 4
        ): array {
            $items = $this->repository
                ->mostFrequentSeverities($limit);

            foreach ($items as &$item) {
                $severity = $this->severityService
                    ->normalize(
                        $item['severity'] ?? 'LOW'
                    );

                $item['severity'] = $severity;

                $item['severity_label'] =
                    $this->severityService
                        ->label($severity);

                $item['severity_class'] =
                    $this->severityService
                        ->cssClass($severity);

                $item['severity_icon'] =
                    $this->severityService
                        ->icon($severity);

                $item['severity_weight'] =
                    $this->severityService
                        ->weight($severity);

                $item['total'] = (int) (
                    $item['total'] ?? 0
                );
            }

            unset($item);

            return $items;
        }

    public function studentsWithMostOccurrences(
            int $limit = 5
        ): array {
            $items = $this->repository
                ->studentsWithMostOccurrences(
                    $limit
                );

            foreach ($items as &$item) {
                $item['id'] = (int) (
                    $item['id'] ?? 0
                );

                $item['total_occurrences'] = (int) (
                    $item['total_occurrences'] ?? 0
                );

                $item['open_occurrences'] = (int) (
                    $item['open_occurrences'] ?? 0
                );

                $item['critical_occurrences'] = (int) (
                    $item['critical_occurrences'] ?? 0
                );
            }

            unset($item);

            return $items;
        }

    public function classesWithMostOccurrences(
            int $limit = 5
        ): array {
            $items = $this->repository
                ->classesWithMostOccurrences(
                    $limit
                );

            foreach ($items as &$item) {
                $item['id'] = (int) (
                    $item['id'] ?? 0
                );

                $item['year'] = (int) (
                    $item['year'] ?? 0
                );

                $item['total_occurrences'] = (int) (
                    $item['total_occurrences'] ?? 0
                );

                $item['critical_occurrences'] = (int) (
                    $item['critical_occurrences'] ?? 0
                );

                $item['open_occurrences'] = (int) (
                    $item['open_occurrences'] ?? 0
                );
            }

            unset($item);

            return $items;
        }

    public function subjectsWithMostOccurrences(
            int $limit = 10
        ): array {
            $items = $this->repository
                ->subjectsWithMostOccurrences(
                    $limit
                );

            foreach ($items as &$item) {
                $item['subject_name'] = trim(
                    (string) (
                        $item['subject_name']
                        ?? 'Sem disciplina'
                    )
                );

                if ($item['subject_name'] === '') {
                    $item['subject_name'] =
                        'Sem disciplina';
                }

                $item['total_occurrences'] = (int) (
                    $item['total_occurrences'] ?? 0
                );

                $item['critical_occurrences'] = (int) (
                    $item['critical_occurrences'] ?? 0
                );

                $item['open_occurrences'] = (int) (
                    $item['open_occurrences'] ?? 0
                );
            }

            unset($item);

            return $items;
        }

}
