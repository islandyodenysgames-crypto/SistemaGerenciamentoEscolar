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

                $nature = strtoupper((string) ($item['nature'] ?? 'ADVERSE'));
                $focusArea = strtoupper((string) ($item['focus_area'] ?? ''));
                $item['nature'] = $nature;
                $item['focus_area'] = $focusArea !== '' ? $focusArea : null;

                $item['type_label'] =
                    match ($nature) {
                        'RESPONSE' => 'Resposta pedagógica',
                        'POSITIVE' => 'Reconhecimento positivo',
                        default => $types[$type] ?? 'Outro',
                    };

                $item['focus_area_label'] = match ($focusArea) {
                    'ATTENDANCE' => 'Frequência / infrequência',
                    'BEHAVIOR' => 'Comportamento e convivência',
                    'LEARNING' => 'Aprendizagem',
                    'WELLBEING' => 'Bem-estar socioemocional',
                    'HEALTH' => 'Saúde',
                    'TRANSPORT' => 'Transporte escolar',
                    'OTHER' => 'Outra área',
                    default => null,
                };

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
