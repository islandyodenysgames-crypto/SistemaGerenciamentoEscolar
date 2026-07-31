<?php

declare(strict_types=1);

namespace App\Services\Occurrence;

use App\DTOs\Occurrence\AnalysisContextDTO;
use App\DTOs\Occurrence\OccurrenceAnalysisDTO;
use App\DTOs\Occurrence\RuleResultDTO;
use App\Rules\Occurrence\RuleRegistry;

final class OccurrenceAnalysisService
{
    public function __construct(
        private readonly RuleRegistry $ruleRegistry
    ) {
    }

    public function analyze(
        AnalysisContextDTO $context
    ): OccurrenceAnalysisDTO {
        $allResults = [];

        foreach ($this->ruleRegistry->all() as $rule) {
            $allResults[] = $rule->evaluate($context);
        }

        return $this->buildAnalysis(
            $context,
            $allResults
        );
    }

    /**
     * @param RuleResultDTO[] $allResults
     */
    private function buildAnalysis(
        AnalysisContextDTO $context,
        array $allResults
    ): OccurrenceAnalysisDTO {
        $matchedResults = array_values(
            array_filter(
                $allResults,
                static fn (
                    RuleResultDTO $result
                ): bool => $result->hasMatched()
            )
        );

        $alerts = [];

        $insights = [];

        $recommendations = [];

        $riskScore = 0;

        $recurrent = false;
        $growingSeverity = false;
        $growingFrequency = false;
        $positiveEvolution = false;

        foreach ($matchedResults as $result) {
            $riskScore += $result->score();

            $alerts[] = $result;
            $insights[] = $result->toInsightDTO();

            match ($result->type()) {
                'RECURRENT_STUDENT' =>
                    $recurrent = true,

                'GROWING_SEVERITY' =>
                    $growingSeverity = true,

                'GROWING_FREQUENCY' =>
                    $growingFrequency = true,

                'POSITIVE_EVOLUTION' =>
                    $positiveEvolution = true,

                default => null,
            };
        }

        $riskScore = $this->normalizeRiskScore(
            $riskScore
        );

        return new OccurrenceAnalysisDTO(
            studentId: $context->studentId(),

            recurrent: $recurrent,

            growingSeverity: $growingSeverity,

            growingFrequency: $growingFrequency,

            positiveEvolution: $positiveEvolution,

            riskLevel: $this->calculateRiskLevel(
                $riskScore
            ),

            riskScore: $riskScore,

            alerts: $alerts,

            insights: $insights,

            recommendations: $recommendations,

            metadata: [
                'registered_rules' =>
                    $this->ruleRegistry->count(),

                'evaluated_rules' =>
                    count($allResults),

                'matched_rules' =>
                    count($matchedResults),

                'rule_results' =>
                    array_map(
                        static fn (
                            RuleResultDTO $result
                        ): array => $result->toArray(),
                        $allResults
                    ),
            ]
        );
    }

    private function normalizeRiskScore(
        int $score
    ): int {
        return max(
            0,
            min(100, $score)
        );
    }

    private function calculateRiskLevel(
        int $score
    ): string {
        return match (true) {
            $score >= 80 => 'CRITICAL',
            $score >= 60 => 'HIGH',
            $score >= 30 => 'ATTENTION',
            default => 'LOW',
        };
    }
}