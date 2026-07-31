<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\SchoolYearRepository;
use DateTimeImmutable;
use InvalidArgumentException;

final class SchoolYearService
{
    private const STATUSES = ['PREPARATION', 'ACTIVE', 'CLOSED', 'ARCHIVED'];

    public function __construct(private SchoolYearRepository $repository)
    {
    }

    public function overview(?int $editingId = null): array
    {
        $years = array_map([$this, 'decorate'], $this->repository->all());

        return [
            'years' => $years,
            'activeYear' => $this->decorateNullable($this->repository->active()),
            'editingYear' => $editingId ? $this->repository->find($editingId) : null,
        ];
    }

    public function create(array $input): int
    {
        $data = $this->validate($input);

        return $this->repository->transaction(function () use ($data): int {
            if ($data['is_active'] === 1) {
                $this->repository->deactivateAllExcept();
                $data['status'] = 'ACTIVE';
            }

            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $input): void
    {
        if ($this->repository->find($id) === null) {
            throw new InvalidArgumentException('Ano letivo não encontrado.');
        }

        $data = $this->validate($input, $id);
        $this->repository->transaction(function () use ($id, $data): void {
            if ($data['is_active'] === 1) {
                $this->repository->deactivateAllExcept($id);
                $data['status'] = 'ACTIVE';
            }

            $this->repository->update($id, $data);
        });
    }

    public function activate(int $id): void
    {
        $year = $this->repository->find($id);
        if ($year === null) {
            throw new InvalidArgumentException('Ano letivo não encontrado.');
        }
        if (in_array($year['status'], ['CLOSED', 'ARCHIVED'], true)) {
            throw new InvalidArgumentException('Reabra o ano antes de defini-lo como ativo.');
        }

        $this->repository->transaction(function () use ($id): void {
            $this->repository->deactivateAllExcept($id);
            $this->repository->activate($id);
        });
    }

    public function close(int $id): void
    {
        $this->changeStatus($id, 'CLOSED');
    }

    public function archive(int $id): void
    {
        $this->changeStatus($id, 'ARCHIVED');
    }

    public function reopen(int $id): void
    {
        $this->changeStatus($id, 'PREPARATION');
    }

    private function changeStatus(int $id, string $status): void
    {
        if ($this->repository->find($id) === null) {
            throw new InvalidArgumentException('Ano letivo não encontrado.');
        }

        $this->repository->setStatus($id, $status);
    }

    private function validate(array $input, ?int $ignoreId = null): array
    {
        $year = (int) ($input['year'] ?? 0);
        $name = trim((string) ($input['name'] ?? ''));
        $start = $this->date((string) ($input['start_date'] ?? ''), 'início');
        $end = $this->date((string) ($input['end_date'] ?? ''), 'término');
        $status = strtoupper(trim((string) ($input['status'] ?? 'PREPARATION')));
        $isActive = !empty($input['is_active']) ? 1 : 0;

        if ($year < 2000 || $year > 2200) {
            throw new InvalidArgumentException('Informe um ano letivo válido.');
        }
        if ($name === '' || mb_strlen($name) > 120) {
            throw new InvalidArgumentException('Informe um nome com até 120 caracteres.');
        }
        if ($end < $start) {
            throw new InvalidArgumentException('A data final não pode ser anterior à data inicial.');
        }
        if (!in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException('Situação do ano letivo inválida.');
        }
        if ($this->repository->yearExists($year, $ignoreId)) {
            throw new InvalidArgumentException('Já existe um cadastro para esse ano letivo.');
        }
        if ($status === 'ACTIVE') {
            $isActive = 1;
        }
        if ($isActive === 1 && in_array($status, ['CLOSED', 'ARCHIVED'], true)) {
            throw new InvalidArgumentException('Um ano encerrado ou arquivado não pode ficar ativo.');
        }

        return [
            'name' => $name,
            'year' => $year,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'status' => $status,
            'is_active' => $isActive,
            'notes' => ($notes = trim((string) ($input['notes'] ?? ''))) !== '' ? $notes : null,
        ];
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

    private function decorateNullable(?array $year): ?array
    {
        return $year === null ? null : $this->decorate($year);
    }

    private function decorate(array $year): array
    {
        $today = new DateTimeImmutable('today');
        $start = new DateTimeImmutable((string) $year['start_date']);
        $end = new DateTimeImmutable((string) $year['end_date']);
        $total = max(1, (int) $start->diff($end)->days + 1);
        $elapsed = $today < $start ? 0 : ($today > $end ? $total : (int) $start->diff($today)->days + 1);

        $year['progress'] = round(min(100, max(0, ($elapsed / $total) * 100)), 1);
        $year['days_remaining'] = $today > $end ? 0 : max(0, (int) $today->diff($end)->days);
        $year['status_label'] = match ((string) $year['status']) {
            'ACTIVE' => 'Em andamento',
            'CLOSED' => 'Encerrado',
            'ARCHIVED' => 'Arquivado',
            default => 'Em preparação',
        };

        return $year;
    }
}
