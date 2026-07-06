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
            'name' => $data['name'] ?? 'Sistema de Frequência Escolar',
            'short_name' => $data['short_name'] ?? 'SFE',
            'inep_code' => $data['inep_code'] ?? null,
            'principal' => $data['principal'] ?? null,
            'vice_principal' => $data['vice_principal'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'zip_code' => $data['zip_code'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'website' => $data['website'] ?? null,
            'instagram' => $data['instagram'] ?? null,
            'facebook' => $data['facebook'] ?? null,
            'youtube' => $data['youtube'] ?? null,
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
            'active' => 1,
            'name' => 'Sistema de Frequência Escolar',
            'short_name' => 'SFE',
            'inep_code' => null,
            'principal' => null,
            'vice_principal' => null,
            'address' => null,
            'city' => null,
            'state' => null,
            'zip_code' => null,
            'phone' => null,
            'email' => null,
            'website' => null,
            'instagram' => null,
            'facebook' => null,
            'youtube' => null,
            'logo_path' => null,
            'primary_color' => '#16a34a',
            'secondary_color' => '#f97316',
        ];
    }
}