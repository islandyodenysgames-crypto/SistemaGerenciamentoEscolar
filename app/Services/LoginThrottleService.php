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
        try {
            return $this->mutate(function (array &$data, int $now) use ($identity, $origin): int {
                $identityWait = $this->bucketWait($data, 'identity:' . hash('sha256', strtolower(trim($identity))), $now);
                $originWait = $this->bucketWait($data, 'origin:' . hash('sha256', $origin), $now);
                return max($identityWait, $originWait);
            });
        } catch (\Throwable $error) {
            $this->reportStorageFailure($error);
            return 0;
        }
    }

    public function recordFailure(string $identity, string $origin): void
    {
        try {
            $this->mutate(function (array &$data, int $now) use ($identity, $origin): null {
                $this->increment($data, 'identity:' . hash('sha256', strtolower(trim($identity))), (int) Config::get('security.login_identity_limit', 5), $now);
                $this->increment($data, 'origin:' . hash('sha256', $origin), (int) Config::get('security.login_origin_limit', 50), $now);
                return null;
            });
        } catch (\Throwable $error) {
            $this->reportStorageFailure($error);
        }
    }

    public function recordSuccess(string $identity): void
    {
        try {
            $this->mutate(function (array &$data) use ($identity): null {
                unset($data['identity:' . hash('sha256', strtolower(trim($identity)))]);
                return null;
            });
        } catch (\Throwable $error) {
            $this->reportStorageFailure($error);
        }
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

    private function bucketWait(array &$data, string $key, int $now): int
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
        if (!is_dir($directory) && !@mkdir($directory, 0770, true) && !is_dir($directory)) {
            throw new \RuntimeException('Não foi possível criar o diretório do limitador de login.');
        }
        $lockFile = (string) $this->storageFile . '.lock';
        $handle = fopen($lockFile, 'c+');
        if ($handle === false) throw new \RuntimeException('Não foi possível acessar o armazenamento de segurança.');
        @chmod($lockFile, 0600);
        try {
            if (!flock($handle, LOCK_EX)) throw new \RuntimeException('Não foi possível bloquear o armazenamento de segurança.');
            $raw = '';
            if (is_file((string) $this->storageFile)) {
                $raw = @file_get_contents((string) $this->storageFile);
                if ($raw === false) throw new \RuntimeException('Não foi possível ler o armazenamento do limitador de login.');
            }
            $decoded = $raw === '' || $raw === false ? [] : json_decode($raw, true);
            if (!is_array($decoded)) {
                error_log('Armazenamento do limitador de login estava corrompido e foi reinicializado.');
                $decoded = [];
            }
            $now = time();
            $data = $this->prune($decoded, $now);
            $result = $callback($data, $now);
            $this->writeAtomically($data, $directory);
            flock($handle, LOCK_UN);
            return $result;
        } finally {
            fclose($handle);
        }
    }

    private function prune(array $data, int $now): array
    {
        $window = max(60, (int) Config::get('security.login_window', 900));
        foreach ($data as $key => $entry) {
            if (!is_array($entry)) {
                unset($data[$key]);
                continue;
            }
            $lockedUntil = (int) ($entry['locked_until'] ?? 0);
            $startedAt = (int) ($entry['started_at'] ?? 0);
            if (($lockedUntil > 0 && $lockedUntil <= $now) || ($lockedUntil <= 0 && ($startedAt <= 0 || ($now - $startedAt) > $window))) {
                unset($data[$key]);
            }
        }
        if (count($data) > 10000) {
            uasort($data, static fn (array $a, array $b): int => ((int) ($b['started_at'] ?? 0)) <=> ((int) ($a['started_at'] ?? 0)));
            $data = array_slice($data, 0, 10000, true);
        }
        return $data;
    }

    private function writeAtomically(array $data, string $directory): void
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $temporary = tempnam($directory, 'login-attempts-');
        if ($temporary === false || file_put_contents($temporary, $json, LOCK_EX) === false) {
            if (is_string($temporary)) @unlink($temporary);
            throw new \RuntimeException('Não foi possível persistir o limitador de login.');
        }
        @chmod($temporary, 0600);
        if (DIRECTORY_SEPARATOR === '\\' && is_file((string) $this->storageFile)) @unlink((string) $this->storageFile);
        if (!rename($temporary, (string) $this->storageFile)) {
            @unlink($temporary);
            throw new \RuntimeException('Não foi possível substituir o armazenamento do limitador de login.');
        }
    }

    private function reportStorageFailure(\Throwable $error): void
    {
        error_log('Limitador de login indisponível: ' . $error->getMessage());
    }
}
