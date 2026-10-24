<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;

final class LoginThrottleService
{
    public function __construct(private ?string $storageFile = null)
    {
        $this->storageFile ??= dirname(__DIR__, 2) . '/storage/security/login-attempts.json';
    }

    public function retryAfter(string $identity, string $origin): int
    {
        return $this->mutate(function (array &$data, int $now) use ($identity, $origin): int {
            $identityWait = $this->bucketWait($data, 'identity:' . hash('sha256', strtolower(trim($identity))), (int) Config::get('security.login_identity_limit', 5), $now);
            $originWait = $this->bucketWait($data, 'origin:' . hash('sha256', $origin), (int) Config::get('security.login_origin_limit', 20), $now);
            return max($identityWait, $originWait);
        });
    }

    public function recordFailure(string $identity, string $origin): void
    {
        $this->mutate(function (array &$data, int $now) use ($identity, $origin): null {
            $this->increment($data, 'identity:' . hash('sha256', strtolower(trim($identity))), (int) Config::get('security.login_identity_limit', 5), $now);
            $this->increment($data, 'origin:' . hash('sha256', $origin), (int) Config::get('security.login_origin_limit', 20), $now);
            return null;
        });
    }

    public function recordSuccess(string $identity): void
    {
        $this->mutate(function (array &$data) use ($identity): null {
            unset($data['identity:' . hash('sha256', strtolower(trim($identity)))]);
            return null;
        });
    }

    private function increment(array &$data, string $key, int $limit, int $now): void
    {
        $window = max(60, (int) Config::get('security.login_window', 900));
        $entry = $data[$key] ?? ['attempts' => 0, 'started_at' => $now, 'locked_until' => 0];
        if (($now - (int) $entry['started_at']) > $window) {
            $entry = ['attempts' => 0, 'started_at' => $now, 'locked_until' => 0];
        }
        $entry['attempts']++;
        if ($entry['attempts'] >= max(1, $limit)) {
            $entry['locked_until'] = $now + max(60, (int) Config::get('security.login_lockout', 900));
        }
        $data[$key] = $entry;
    }

    private function bucketWait(array &$data, string $key, int $limit, int $now): int
    {
        $entry = $data[$key] ?? null;
        if (!is_array($entry)) return 0;
        if ((int) ($entry['locked_until'] ?? 0) > $now) return (int) $entry['locked_until'] - $now;
        if (($now - (int) ($entry['started_at'] ?? 0)) > max(60, (int) Config::get('security.login_window', 900))) unset($data[$key]);
        return 0;
    }

    private function mutate(callable $callback): mixed
    {
        $directory = dirname((string) $this->storageFile);
        if (!is_dir($directory)) mkdir($directory, 0775, true);
        $handle = fopen((string) $this->storageFile, 'c+');
        if ($handle === false) throw new \RuntimeException('Não foi possível acessar o armazenamento de segurança.');
        try {
            if (!flock($handle, LOCK_EX)) throw new \RuntimeException('Não foi possível bloquear o armazenamento de segurança.');
            rewind($handle);
            $decoded = json_decode((string) stream_get_contents($handle), true);
            $data = is_array($decoded) ? $decoded : [];
            $result = $callback($data, time());
            ftruncate($handle, 0); rewind($handle);
            fwrite($handle, json_encode($data, JSON_UNESCAPED_SLASHES)); fflush($handle); flock($handle, LOCK_UN);
            return $result;
        } finally {
            fclose($handle);
        }
    }
}
