<?php

declare(strict_types=1);

namespace App\Core\Settings;

use App\Core\Settings\Contracts\SettingCacheInterface;
use App\Core\Settings\Contracts\SettingProviderInterface;

final class SettingManager
{
    public function __construct(
        private SettingProviderInterface $provider,
        private SettingCacheInterface $cache,
        private SettingValidator $validator,
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $cached = $this->cache->get($key);
        if ($cached !== null) {
            return $cached;
        }

        $row = $this->provider->find($key);
        if ($row === null) {
            return $default;
        }

        $value = $this->decode((string) $row['value'], (string) $row['type']);
        $this->cache->put($key, $value);
        return $value;
    }

    public function group(string $group): array
    {
        return $this->provider->all($group);
    }

    public function forget(string $key): void
    {
        $this->cache->forget($key);
    }

    public function flush(): void
    {
        $this->cache->flush();
    }

    public function validator(): SettingValidator
    {
        return $this->validator;
    }

    private function decode(string $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'float' => (float) $value,
            'boolean' => in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true),
            'json' => json_decode($value, true) ?: [],
            default => $value,
        };
    }
}
