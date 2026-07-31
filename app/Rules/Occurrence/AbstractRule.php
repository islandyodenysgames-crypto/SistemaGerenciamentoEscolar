<?php

declare(strict_types=1);

namespace App\Rules\Occurrence;

use App\DTOs\Occurrence\AnalysisContextDTO;
use App\DTOs\Occurrence\RuleResultDTO;

abstract class AbstractRule implements RuleInterface
{
    abstract public function name(): string;

    abstract protected function analyze(
        AnalysisContextDTO $context
    ): RuleResultDTO;

    public function priority(): int
    {
        return 100;
    }

    public function supports(
        AnalysisContextDTO $context
    ): bool {
        return true;
    }

    final public function evaluate(
        AnalysisContextDTO $context
    ): RuleResultDTO {
        if (!$this->supports($context)) {
            return $this->notSupported();
        }

        return $this->analyze($context);
    }

    protected function notSupported(): RuleResultDTO
    {
        return new RuleResultDTO(
            rule: $this->name(),
            matched: false,
            type: strtoupper($this->name()),
            title: 'Regra não executada',
            message:
                'O contexto informado não possui os dados necessários.',
            severity: 'INFO',
            metadata: [
                'supported' => false,
            ]
        );
    }

    protected function success(
        string $type,
        string $title,
        string $message,
        string $severity = 'INFO',
        int $score = 0,
        array $factors = [],
        array $metadata = []
    ): RuleResultDTO {
        return new RuleResultDTO(
            rule: $this->name(),
            matched: true,
            type: $type,
            title: $title,
            message: $message,
            severity: $severity,
            score: $score,
            factors: $factors,
            metadata: array_merge(
                ['supported' => true],
                $metadata
            )
        );
    }

    protected function fail(
        string $type,
        string $title,
        string $message,
        string $severity = 'INFO',
        array $metadata = []
    ): RuleResultDTO {
        return new RuleResultDTO(
            rule: $this->name(),
            matched: false,
            type: $type,
            title: $title,
            message: $message,
            severity: $severity,
            metadata: array_merge(
                ['supported' => true],
                $metadata
            )
        );
    }
}