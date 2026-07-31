<?php

declare(strict_types=1);

namespace App\Services\Occurrence\Concerns;

trait OccurrenceFormattingOperations
{
    /**
         * Adiciona rótulos, classes e ícones
         * aos dados retornados pelo repository.
         */
        private function decorateOccurrences(
            array $items
        ): array {
            $types = $this->types();

            foreach ($items as &$item) {
                $type = strtoupper(
                    trim(
                        (string) (
                            $item['type'] ?? 'OTHER'
                        )
                    )
                );

                if (
                    !array_key_exists(
                        $type,
                        $types
                    )
                ) {
                    $type = 'OTHER';
                }

                $severity = $this->severityService
                    ->normalize(
                        $item['severity'] ?? 'LOW'
                    );

                $item['id'] = (int) (
                    $item['id'] ?? 0
                );

                $item['student_id'] = (int) (
                    $item['student_id'] ?? 0
                );

                $item['type'] = $type;

                $item['type_label'] =
                    $types[$type] ?? 'Outro';

                $item['severity'] =
                    $severity;

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
            }

            unset($item);

            return $items;
        }

}
