<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\SchoolRepository;

class SchoolService
{
    public function __construct(
        private SchoolRepository $repository
    ) {
    }

    public function current(): array
    {
        return $this->repository->first() ?? $this->defaults();
    }

    public function save(array $data): void
    {
        $current = $this->repository->first();

        $payload = [
            'name' => $data['name'] ?? 'Escola',
            'short_name' => $data['short_name'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'address' => $data['address'] ?? null,
            'principal' => $data['principal'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'website' => $data['website'] ?? null,
            'logo_path' => $data['logo_path'] ?? ($current['logo_path'] ?? null),
            'primary_color' => $data['primary_color'] ?? '#16a34a',
            'secondary_color' => $data['secondary_color'] ?? '#f97316',
        ];

        if ($current) {
            $this->repository->update((int) $current['id'], $payload);
            return;
        }

        $this->repository->create($payload);
    }

    public function defaults(): array
    {
        return [
            'id' => null,
            'name' => 'Sistema de Frequência Escolar',
            'short_name' => 'SFE',
            'city' => null,
            'state' => null,
            'address' => null,
            'principal' => null,
            'phone' => null,
            'email' => null,
            'website' => null,
            'logo_path' => null,
            'primary_color' => '#16a34a',
            'secondary_color' => '#f97316',
            'active' => 1,
        ];
    }
}