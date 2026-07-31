<?php

declare(strict_types=1);

namespace App\Helpers;

use DateTimeImmutable;
use DateTimeInterface;
use Throwable;

final class DateHelper
{
    private const WEEKDAYS = [
        0 => 'Domingo',
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
    ];

    private const MONTHS = [
        1 => 'janeiro',
        2 => 'fevereiro',
        3 => 'março',
        4 => 'abril',
        5 => 'maio',
        6 => 'junho',
        7 => 'julho',
        8 => 'agosto',
        9 => 'setembro',
        10 => 'outubro',
        11 => 'novembro',
        12 => 'dezembro',
    ];

    public static function parse(DateTimeInterface|string|null $value = null): DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        try {
            return new DateTimeImmutable($value ?: 'now');
        } catch (Throwable) {
            return new DateTimeImmutable('now');
        }
    }

    public static function shortDate(DateTimeInterface|string|null $value = null): string
    {
        return self::parse($value)->format('d/m/Y');
    }

    public static function weekday(DateTimeInterface|string|null $value = null): string
    {
        $date = self::parse($value);
        return self::WEEKDAYS[(int) $date->format('w')] ?? '';
    }

    public static function monthName(DateTimeInterface|string|null $value = null): string
    {
        $date = self::parse($value);
        return self::MONTHS[(int) $date->format('n')] ?? '';
    }

    public static function shortMonth(DateTimeInterface|string|null $value = null): string
    {
        return mb_strtoupper(mb_substr(self::monthName($value), 0, 3));
    }

    public static function longDate(DateTimeInterface|string|null $value = null): string
    {
        $date = self::parse($value);
        return sprintf('%d de %s de %s', (int) $date->format('d'), self::monthName($date), $date->format('Y'));
    }

    public static function fullDate(DateTimeInterface|string|null $value = null): string
    {
        return self::weekday($value) . ', ' . self::longDate($value);
    }

    public static function fullDateTime(DateTimeInterface|string|null $value = null): string
    {
        $date = self::parse($value);
        return self::fullDate($date) . ' às ' . $date->format('H:i');
    }
}
