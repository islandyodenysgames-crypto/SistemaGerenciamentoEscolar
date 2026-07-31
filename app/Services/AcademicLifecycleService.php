<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AcademicLifecycleRepository;
use App\Repositories\SchoolPeriodRepository;
use App\Repositories\SchoolYearRepository;
use DateTimeImmutable;
use InvalidArgumentException;

final class AcademicLifecycleService
{
    public function __construct(
        private AcademicLifecycleRepository $repository,
        private SchoolYearRepository $years,
        private SchoolPeriodRepository $periods,
        private SchoolDataMaintenanceService $maintenance
    ) {
    }

    public function closePeriod(int $id, int $userId, ?string $ip): void
    {
        $period = $this->repository->period($id);
        if ($period === null) {
            throw new InvalidArgumentException('Período não encontrado.');
        }
        if ($period['status'] === 'CLOSED') {
            throw new InvalidArgumentException('O período já está fechado.');
        }

        $metrics = $this->repository->metrics($period['start_date'], $period['end_date']);
        $this->maintenance->createBackup();
        $this->repository->transaction(function () use ($period, $id, $metrics, $userId, $ip): void {
            $this->repository->snapshot((int) $period['school_year_id'], $id, 'PERIOD_CLOSE', $metrics, $userId);
            $this->repository->setPeriodStatus($id, 'CLOSED');
            $this->repository->audit((int) $period['school_year_id'], $id, 'PERIOD_CLOSED', 'Período letivo fechado.', $metrics, $userId, $ip);
        });
    }

    public function reopenPeriod(int $id, int $userId, ?string $ip): void
    {
        $period = $this->repository->period($id);
        if ($period === null) {
            throw new InvalidArgumentException('Período não encontrado.');
        }
        if ($period['status'] !== 'CLOSED') {
            throw new InvalidArgumentException('Somente um período fechado pode ser reaberto.');
        }

        $this->repository->transaction(function () use ($period, $id, $userId, $ip): void {
            $this->repository->setPeriodStatus($id, 'REOPENED');
            $this->repository->audit((int) $period['school_year_id'], $id, 'PERIOD_REOPENED', 'Período letivo reaberto.', [], $userId, $ip);
        });
    }

    public function closeYear(int $id, int $userId, ?string $ip): void
    {
        $year = $this->years->find($id);
        if ($year === null) {
            throw new InvalidArgumentException('Ano letivo não encontrado.');
        }
        if ($year['status'] !== 'ACTIVE') {
            throw new InvalidArgumentException('Somente o ano letivo ativo pode ser encerrado.');
        }

        foreach ($this->periods->forYear($id) as $period) {
            if ($period['status'] !== 'CLOSED') {
                throw new InvalidArgumentException('Feche todos os períodos antes de encerrar o ano.');
            }
        }

        $metrics = $this->repository->metrics($year['start_date'], $year['end_date']);
        $this->maintenance->createBackup();
        $this->repository->transaction(function () use ($id, $metrics, $userId, $ip): void {
            $this->repository->snapshot($id, null, 'YEAR_CLOSE', $metrics, $userId);
            $this->years->setStatus($id, 'CLOSED');
            $this->repository->audit($id, null, 'YEAR_CLOSED', 'Ano letivo encerrado.', $metrics, $userId, $ip);
        });
    }

    public function createNextYear(int $sourceId, array $input, int $userId, ?string $ip): int
    {
        $source = $this->years->find($sourceId);
        if ($source === null) {
            throw new InvalidArgumentException('Ano de origem não encontrado.');
        }

        $year = (int) ($input['year'] ?? ((int) $source['year'] + 1));
        $start = $this->date((string) ($input['start_date'] ?? $year . '-02-01'), 'início');
        $end = $this->date((string) ($input['end_date'] ?? $year . '-12-20'), 'término');
        if ($year < 2000 || $year > 2200) {
            throw new InvalidArgumentException('Informe um ano letivo válido.');
        }
        if ($end < $start) {
            throw new InvalidArgumentException('A data final não pode ser anterior à data inicial.');
        }
        if ((int) $start->format('Y') !== $year || (int) $end->format('Y') !== $year) {
            throw new InvalidArgumentException('A vigência deve pertencer ao novo ano informado.');
        }
        if ($this->years->yearExists($year)) {
            throw new InvalidArgumentException('O novo ano já está cadastrado.');
        }

        return $this->repository->transaction(function () use ($source, $sourceId, $input, $userId, $ip, $year, $start, $end): int {
            $id = $this->years->create([
                'name' => trim((string) ($input['name'] ?? '')) ?: 'Ano Letivo ' . $year,
                'year' => $year,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'status' => 'PREPARATION',
                'is_active' => 0,
                'notes' => 'Criado pelo Assistente de Novo Ano',
            ]);

            if (!empty($input['copy_periods'])) {
                foreach ($this->periods->forYear($sourceId) as $period) {
                    $offset = $year - (int) $source['year'];
                    $this->periods->save(null, [
                        'school_year_id' => $id,
                        'name' => $period['name'],
                        'short_name' => $period['short_name'],
                        'type' => $period['type'],
                        'order_number' => $period['order_number'],
                        'start_date' => date('Y-m-d', strtotime($period['start_date'] . " {$offset} year")),
                        'end_date' => date('Y-m-d', strtotime($period['end_date'] . " {$offset} year")),
                        'color' => $period['color'],
                        'description' => $period['description'],
                        'status' => 'OPEN',
                    ]);
                }
            }

            $this->repository->audit($id, null, 'YEAR_CREATED_BY_ASSISTANT', 'Novo ano criado pelo assistente.', [
                'source_year_id' => $sourceId,
                'periods_copied' => !empty($input['copy_periods']),
            ], $userId, $ip);

            return $id;
        });
    }

    public function history(): array
    {
        return $this->repository->history();
    }

    public function audits(): array
    {
        return $this->repository->audits();
    }

    private function date(string $value, string $label): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = DateTimeImmutable::getLastErrors();
        if (!$date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            throw new InvalidArgumentException("Informe uma data de {$label} válida.");
        }
        return $date;
    }
}
