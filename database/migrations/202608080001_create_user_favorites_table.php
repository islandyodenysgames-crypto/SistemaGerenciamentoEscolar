<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Connection::getInstance()->exec("CREATE TABLE IF NOT EXISTS user_favorites (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT NOT NULL,
            favorite_type VARCHAR(40) NOT NULL,
            favorite_id BIGINT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_user_favorite (user_id, favorite_type, favorite_id),
            KEY idx_user_favorites_user (user_id, created_at),
            CONSTRAINT fk_user_favorites_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function down(): void
    {
        Connection::getInstance()->exec('DROP TABLE IF EXISTS user_favorites');
    }
};
