<?php

declare(strict_types=1);

namespace App\Services\Content\Providers;

use App\Services\Content\Contracts\ContentProviderInterface;
use App\Services\Content\InstitutionalTemplates;

final class RuleBasedProvider implements ContentProviderInterface
{
    public function name(): string { return 'rules'; }

    public function generate(string $type, array $context, string $style, string $referenceDate): string
    {
        $library = InstitutionalTemplates::all();
        $group = $library[$type] ?? $library['motivation'];
        $templates = $group[$style] ?? $group['institutional'] ?? reset($group);
        if (!is_array($templates) || $templates === []) return '';

        $priority = $this->priorityTemplate($type, $context, $style);
        if ($priority !== null) return $this->replace($priority, $context);

        $seed = abs((int)crc32($referenceDate . '|' . $type . '|' . $style));
        return $this->replace((string)$templates[$seed % count($templates)], $context);
    }

    private function priorityTemplate(string $type, array $c, string $style): ?string
    {
        $change = $c['change'] ?? null;
        if ($type === 'automatic_message') {
            if ((float)($c['class_attendance'] ?? 0) >= 99.9 && !empty($c['class'])) return 'Parabéns à turma {class}: 100% de presença hoje!';
            if (is_numeric($change) && (float)$change >= 1) return 'A frequência geral aumentou {change}% em relação ao último dia letivo. Parabéns!';
            if (is_numeric($change) && (float)$change <= -1) return 'A frequência ficou {change_abs}% abaixo do último dia letivo. Vamos juntos recuperar esse resultado.';
        }
        return null;
    }

    private function replace(string $template, array $context): string
    {
        $values = [];
        foreach ($context as $key => $value) {
            if (is_scalar($value) || $value === null) $values['{' . $key . '}'] = (string)$value;
        }
        return strtr($template, $values);
    }
}
