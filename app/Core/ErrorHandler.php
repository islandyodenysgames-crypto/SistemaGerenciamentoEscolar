<?php

declare(strict_types=1);

namespace App\Core;

use ErrorException;
use Throwable;

final class ErrorHandler
{
    public static function register(): void
    {
        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) return false;
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
        set_exception_handler([self::class, 'handle']);
        register_shutdown_function(static function (): void {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                self::handle(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
            }
        });
    }

    public static function handle(Throwable $exception): void
    {
        $id = strtoupper(bin2hex(random_bytes(6)));
        self::log($id, $exception);
        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, self::isDebug() ? (string) $exception . PHP_EOL : "Erro interno. Identificador: {$id}" . PHP_EOL);
            exit(1);
        }
        if (headers_sent()) return;
        http_response_code(500);
        if (Request::expectsJson()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => self::isDebug() ? $exception->getMessage() : 'Ocorreu um erro interno.', 'error_id' => $id]);
            return;
        }
        header('Content-Type: text/html; charset=utf-8');
        $detail = self::isDebug() ? '<pre>' . htmlspecialchars((string) $exception, ENT_QUOTES, 'UTF-8') . '</pre>' : '';
        echo '<!doctype html><html lang="pt-BR"><meta charset="utf-8"><title>Erro interno</title><body><main><h1>Não foi possível concluir a solicitação</h1><p>Tente novamente. Se o problema continuar, informe o código <strong>' . $id . '</strong>.</p>' . $detail . '</main></body></html>';
    }

    private static function isDebug(): bool
    {
        $environment = strtolower((string) Config::get('app.environment', 'production'));
        return filter_var(Config::get('app.debug', false), FILTER_VALIDATE_BOOL)
            && in_array($environment, ['local', 'development', 'testing'], true);
    }

    private static function log(string $id, Throwable $exception): void
    {
        $directory = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($directory)) @mkdir($directory, 0775, true);
        $message = sprintf("[%s] [%s] %s\n%s\n", date('c'), $id, (string) $exception, str_repeat('-', 80));
        @error_log($message, 3, $directory . '/app-' . date('Y-m-d') . '.log');
    }
}
