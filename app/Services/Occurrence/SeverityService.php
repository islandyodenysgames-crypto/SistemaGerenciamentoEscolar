<?php

declare(strict_types=1);

namespace App\Services\Occurrence;

use InvalidArgumentException;

class SeverityService
{
    public const LOW = 'LOW';

    public const MEDIUM = 'MEDIUM';

    public const HIGH = 'HIGH';

    public const CRITICAL = 'CRITICAL';

    private const LEVELS = [
        self::LOW,
        self::MEDIUM,
        self::HIGH,
        self::CRITICAL,
    ];

    /**
     * Retorna todos os níveis de gravidade.
     */
    public function all(): array
    {
        return [
            self::LOW => 'Baixa',
            self::MEDIUM => 'Média',
            self::HIGH => 'Alta',
            self::CRITICAL => 'Gravíssima',
        ];
    }

    /**
     * Retorna as classes visuais utilizadas
     * nos cards, badges e relatórios.
     */
    public function classes(): array
    {
        return [
            self::LOW => 'severity-low',
            self::MEDIUM => 'severity-medium',
            self::HIGH => 'severity-high',
            self::CRITICAL => 'severity-critical',
        ];
    }

    /**
     * Ícones Lucide utilizados na interface.
     */
    public function icons(): array
    {
        return [
            self::LOW => 'circle-check',
            self::MEDIUM => 'circle-alert',
            self::HIGH => 'triangle-alert',
            self::CRITICAL => 'siren',
        ];
    }

    /**
     * Retorna a ordem numérica da gravidade.
     *
     * Útil para filtros, ordenação e dashboard.
     */
    public function weights(): array
    {
        return [
            self::LOW => 1,
            self::MEDIUM => 2,
            self::HIGH => 3,
            self::CRITICAL => 4,
        ];
    }

    public function normalize(
        mixed $severity,
        string $default = self::LOW
    ): string {
        $severity = strtoupper(
            trim((string) $severity)
        );

        if (
            in_array(
                $severity,
                self::LEVELS,
                true
            )
        ) {
            return $severity;
        }

        return in_array(
            $default,
            self::LEVELS,
            true
        )
            ? $default
            : self::LOW;
    }

    /**
     * Valida a gravidade e lança uma exceção
     * quando o valor recebido não for permitido.
     */
    public function validate(
        mixed $severity
    ): string {
        $severity = strtoupper(
            trim((string) $severity)
        );

        if (
            !in_array(
                $severity,
                self::LEVELS,
                true
            )
        ) {
            throw new InvalidArgumentException(
                'A gravidade informada é inválida.'
            );
        }

        return $severity;
    }

    public function label(
        mixed $severity
    ): string {
        $severity = $this->normalize(
            $severity
        );

        return $this->all()[$severity]
            ?? 'Baixa';
    }

    public function cssClass(
        mixed $severity
    ): string {
        $severity = $this->normalize(
            $severity
        );

        return $this->classes()[$severity]
            ?? 'severity-low';
    }

    public function icon(
        mixed $severity
    ): string {
        $severity = $this->normalize(
            $severity
        );

        return $this->icons()[$severity]
            ?? 'circle-check';
    }

    public function weight(
        mixed $severity
    ): int {
        $severity = $this->normalize(
            $severity
        );

        return $this->weights()[$severity]
            ?? 1;
    }

    public function isCritical(
        mixed $severity
    ): bool {
        return $this->normalize(
            $severity
        ) === self::CRITICAL;
    }

    public function isHighOrCritical(
        mixed $severity
    ): bool {
        return in_array(
            $this->normalize($severity),
            [
                self::HIGH,
                self::CRITICAL,
            ],
            true
        );
    }
}