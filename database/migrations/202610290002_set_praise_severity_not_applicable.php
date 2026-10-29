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
SQL);
    }

    public function down(): void
    {
        Connection::getInstance()->exec(<<<'SQL'
UPDATE student_occurrences
SET severity = 'LOW',
    updated_at = NOW()
WHERE type = 'PRAISE'
  AND severity = 'NOT_APPLICABLE'
SQL);
    }
};
