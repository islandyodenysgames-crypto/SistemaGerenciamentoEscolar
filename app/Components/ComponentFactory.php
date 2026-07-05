<?php

declare(strict_types=1);

namespace App\Components;

class ComponentFactory
{
    private static array $map = [
        'base/button' => Button::class,
        'base/card' => Card::class,
        'base/metric' => Metric::class,
        'base/progress' => Progress::class,
        'base/stat-card' => StatCard::class,
        'base/page-header' => PageHeader::class,
    ];

    public static function make(string $name, array $data = []): ?BaseComponent
    {
        if (!isset(self::$map[$name])) {
            return null;
        }

        $class = self::$map[$name];

        return new $class($data);
    }
}