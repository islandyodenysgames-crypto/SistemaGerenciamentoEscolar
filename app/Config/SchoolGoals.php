<?php

declare(strict_types=1);

namespace App\Config;

class SchoolGoals
{
    public static function defaults(): array
    {
        return [
            'frequency_goal' => 95,
            'attendance_goal' => 100,
            'max_students_alert' => 10,
            'period' => 'Ano letivo atual',
            'title' => 'Objetivo da Escola',
            'icon' => 'trophy',
        ];
    }
}