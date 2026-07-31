<?php

declare(strict_types=1);

namespace App\Core\Settings;

use App\Core\Settings\Contracts\SettingCacheInterface;

final class SettingCache implements SettingCacheInterface
{
    private array $items = [];

    public function get(string $key): mixed
    {
        return $this->items[$key] ?? null;
    }

    public function put(string $key, mixed $value): void
    {
        $this->items[$key] = $value;
    }

    public function forget(string $key): void
    {
        unset($this->items[$key]);
    }

    public function flush(): void
    {
        $this->items = [];
    }
}
