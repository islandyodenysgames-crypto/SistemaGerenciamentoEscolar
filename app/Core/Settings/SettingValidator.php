<?php

declare(strict_types=1);

namespace App\Core\Settings;

use InvalidArgumentException;

final class SettingValidator
{
    public function cast(mixed $value, string $type): mixed
    {
        return match ($type) {
            'integer' => $this->integer($value),
            'float' => $this->floating($value),
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => $this->json($value),
            default => trim((string) $value),
        };
    }

    public function validateIntelligence(array $values): void
    {
        $moderate = (int) ($values['risk_moderate_threshold'] ?? 20);
        $high = (int) ($values['risk_high_threshold'] ?? 45);
        $critical = (int) ($values['risk_critical_threshold'] ?? 70);

        if (!($moderate < $high && $high < $critical && $critical <= 100)) {
            throw new InvalidArgumentException('Os níveis de risco devem estar em ordem crescente e o nível crítico não pode ultrapassar 100.');
        }

        foreach (['analysis_window_days', 'recurrence_limit', 'stale_critical_days'] as $key) {
            if ((int) ($values[$key] ?? 0) < 1) {
                throw new InvalidArgumentException('Os períodos e limites devem ser maiores que zero.');
            }
        }


        $weightTotal = (int)($values['risk_weight_frequency'] ?? 40)
            + (int)($values['risk_weight_occurrences'] ?? 35)
            + (int)($values['risk_weight_severity'] ?? 25);
        if ($weightTotal !== 100) {
            throw new InvalidArgumentException('Os pesos de frequência, ocorrências e gravidade devem somar exatamente 100%.');
        }

        if ((int)($values['class_attention_threshold'] ?? 0) < 1) {
            throw new InvalidArgumentException('O limite de atenção da turma deve ser maior que zero.');
        }
    }

    private function integer(mixed $value): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new InvalidArgumentException('Valor inteiro inválido.');
        }
        return (int) $value;
    }

    private function floating(mixed $value): float
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('Valor numérico inválido.');
        }
        return (float) $value;
    }

    private function json(mixed $value): array
    {
        $decoded = json_decode((string) $value, true);
        if (!is_array($decoded)) {
            throw new InvalidArgumentException('JSON inválido.');
        }
        return $decoded;
    }
}
