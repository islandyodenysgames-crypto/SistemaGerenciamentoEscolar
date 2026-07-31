<?php

declare(strict_types=1);

namespace App\Core\Settings\Contracts;

interface SettingProviderInterface
{
    public function all(?string $group = null): array;

    public function find(string $key): ?array;

    public function upsert(array $setting): void;

    public function resetGroup(string $group): void;
}
