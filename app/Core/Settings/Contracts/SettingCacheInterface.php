<?php

declare(strict_types=1);

namespace App\Core\Settings\Contracts;

interface SettingCacheInterface
{
    public function get(string $key): mixed;

    public function put(string $key, mixed $value): void;

    public function forget(string $key): void;

    public function flush(): void;
}
