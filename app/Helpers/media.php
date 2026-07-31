<?php

declare(strict_types=1);

use App\Database\Connection;

if (!function_exists('attachment_image_carousels_enabled')) {
    function attachment_image_carousels_enabled(): bool
    {
        static $enabled = null;
        if ($enabled !== null) {
            return $enabled;
        }

        try {
            $stmt = Connection::getInstance()->prepare(
                "SELECT value FROM system_settings WHERE setting_key = 'intelligence.show_attachment_image_carousels' LIMIT 1"
            );
            $stmt->execute();
            $value = $stmt->fetchColumn();
            $enabled = $value === false
                ? true
                : in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
        } catch (Throwable) {
            $enabled = true;
        }

        return $enabled;
    }
}
