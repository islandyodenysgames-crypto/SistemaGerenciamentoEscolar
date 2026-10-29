<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Connection::getInstance()->exec(<<<'SQL'
UPDATE student_occurrences
SET severity = 'NOT_APPLICABLE',
    status = 'RESOLVED',
    updated_at = NOW()
WHERE type = 'PRAISE'
  AND (severity <> 'NOT_APPLICABLE' OR status <> 'RESOLVED')
SQL);
    }

    public function down(): void
    {
        // Normalização sem reversão: os valores anteriores não representam
        // uma regra válida para registros positivos e não são recuperáveis.
    }
};
