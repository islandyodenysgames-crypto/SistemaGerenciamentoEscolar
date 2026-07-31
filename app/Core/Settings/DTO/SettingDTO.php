<?php

declare(strict_types=1);

namespace App\Core\Settings\DTO;

final readonly class SettingDTO
{
    public function __construct(
        public string $group,
        public string $key,
        public mixed $value,
        public mixed $defaultValue,
        public string $type,
        public string $category,
        public string $label,
        public string $description,
        public bool $editable = true,
        public bool $requiresRestart = false,
        public int $sortOrder = 0,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            group: (string) ($data['group_name'] ?? $data['group'] ?? 'general'),
            key: (string) ($data['key_name'] ?? $data['key'] ?? ''),
            value: $data['value'] ?? null,
            defaultValue: $data['default_value'] ?? null,
            type: (string) ($data['type'] ?? 'string'),
            category: (string) ($data['category'] ?? 'Geral'),
            label: (string) ($data['label'] ?? $data['key_name'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            editable: (bool) ($data['editable'] ?? true),
            requiresRestart: (bool) ($data['requires_restart'] ?? false),
            sortOrder: (int) ($data['sort_order'] ?? 0),
        );
    }
}
