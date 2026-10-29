<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class DashboardRankingExportService
{
    private const WIDTH = 1200;

    /** @param array<int,array<string,mixed>> $ranking */
    public function outputPng(array $ranking, array $summary): never
    {
        if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor')) {
            throw new RuntimeException('A extensão GD é necessária para gerar o Ranking Diário.');
        }

        $rowHeight = 112;
        $height = 410 + max(1, count($ranking)) * $rowHeight;
        $image = imagecreatetruecolor(self::WIDTH, $height);
        if ($image === false) throw new RuntimeException('Não foi possível preparar a imagem do ranking.');

        imageantialias($image, true);
        $white = $this->color($image, '#ffffff');
        $ink = $this->color($image, '#0f172a');
        $muted = $this->color($image, '#64748b');
        $green = $this->color($image, '#15803d');
        $greenSoft = $this->color($image, '#ecfdf3');
        $blueSoft = $this->color($image, '#eff6ff');
        $redSoft = $this->color($image, '#fff1f2');
        $amberSoft = $this->color($image, '#fffbeb');
        $line = $this->color($image, '#e2e8f0');
        imagefill($image, 0, 0, $white);

        $regular = $this->font(false);
        $bold = $this->font(true);
        $school = (string) school('name', app_name());

        $this->text($image, $school, 25, 48, 58, $green, $bold);
        $this->textRight($image, date('d/m/Y H:i'), 17, self::WIDTH - 48, 55, $muted, $bold);
        $this->textCenter($image, 'Ranking Diário', 34, self::WIDTH / 2, 116, $ink, $bold);
        $this->textCenter($image, 'Resumo institucional da frequência escolar', 17, self::WIDTH / 2, 151, $muted, $regular);

        $cards = [
            ['Frequência geral', number_format((float) ($summary['generalPercentage'] ?? 0), 1, ',', '.') . '%', $greenSoft],
            ['Presentes', (string) (int) ($summary['presentes'] ?? 0), $blueSoft],
            ['Faltas', (string) (int) ($summary['faltas'] ?? 0), $redSoft],
            ['Turmas', (string) (int) ($summary['totalClasses'] ?? count($ranking)), $amberSoft],
        ];
        foreach ($cards as $index => [$label, $value, $background]) {
            $x = 48 + $index * 280;
            imagefilledrectangle($image, $x, 180, $x + 260, 290, $background);
            imagerectangle($image, $x, 180, $x + 260, 290, $line);
            $this->textCenter($image, $value, 27, $x + 130, 232, $ink, $bold);
            $this->textCenter($image, $label, 13, $x + 130, 268, $muted, $bold);
        }

        $y = 320;
        if ($ranking === []) {
            imagefilledrectangle($image, 48, $y, self::WIDTH - 48, $y + 82, $greenSoft);
            $this->textCenter($image, 'Nenhuma frequência registrada hoje.', 19, self::WIDTH / 2, $y + 50, $muted, $bold);
        } else {
            foreach ($ranking as $index => $item) {
                $bottom = $y + 92;
                imagefilledrectangle($image, 48, $y, self::WIDTH - 48, $bottom, $index % 2 === 0 ? $greenSoft : $white);
                imagerectangle($image, 48, $y, self::WIDTH - 48, $bottom, $line);

                $hasAttendance = (int) ($item['has_attendance'] ?? 0) === 1;
                $percentage = $hasAttendance ? (float) ($item['attendance_percentage'] ?? 0) : 0.0;
                $position = ($index + 1) . 'º';
                $className = $this->truncate((string) ($item['class_name'] ?? 'Turma'), 34);
                $details = trim((string) ($item['year'] ?? '') . ' • ' . (string) ($item['shift'] ?? ''));

                $this->textCenter($image, $position, 22, 88, $y + 54, $green, $bold);
                $this->text($image, $className, 20, 132, $y + 40, $ink, $bold);
                $this->text($image, $details, 12, 132, $y + 68, $muted, $regular);
                $this->metric($image, 'Presentes', (string) (int) ($item['presentes'] ?? 0), 560, $y, $ink, $muted, $bold, $regular);
                $this->metric($image, 'Faltas', (string) (int) ($item['ranking_absences'] ?? 0), 750, $y, $ink, $muted, $bold, $regular);
                $this->metric($image, 'Frequência', $hasAttendance ? number_format($percentage, 0, ',', '.') . '%' : 'Pendente', 940, $y, $green, $muted, $bold, $regular);
                $y += $rowHeight;
            }
        }

        $footerY = $height - 46;
        imageline($image, 48, $footerY - 28, self::WIDTH - 48, $footerY - 28, $line);
        $this->textCenter($image, 'Gerado pelo Sistema de Gerenciamento Escolar', 12, self::WIDTH / 2, $footerY, $muted, $regular);

        while (ob_get_level() > 0) ob_end_clean();
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="ranking-diario-' . date('Y-m-d') . '.png"');
        header('Cache-Control: private, no-store, max-age=0');
        imagepng($image, null, 6);
        imagedestroy($image);
        exit;
    }

    private function metric($image, string $label, string $value, int $x, int $y, int $valueColor, int $labelColor, string $bold, string $regular): void
    {
        $this->textCenter($image, $value, 18, $x, $y + 42, $valueColor, $bold);
        $this->textCenter($image, $label, 11, $x, $y + 68, $labelColor, $regular);
    }

    private function text($image, string $text, int $size, int $x, int $y, int $color, string $font): void
    {
        imagettftext($image, $size, 0, $x, $y, $color, $font, $text);
    }

    private function textCenter($image, string $text, int $size, float $centerX, int $y, int $color, string $font): void
    {
        $box = imagettfbbox($size, 0, $font, $text);
        $width = is_array($box) ? $box[2] - $box[0] : 0;
        $this->text($image, $text, $size, (int) round($centerX - $width / 2), $y, $color, $font);
    }

    private function textRight($image, string $text, int $size, int $right, int $y, int $color, string $font): void
    {
        $box = imagettfbbox($size, 0, $font, $text);
        $width = is_array($box) ? $box[2] - $box[0] : 0;
        $this->text($image, $text, $size, $right - $width, $y, $color, $font);
    }

    private function color($image, string $hex): int
    {
        $hex = ltrim($hex, '#');
        return imagecolorallocate($image, hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
    }

    private function font(bool $bold): string
    {
        $candidates = $bold
            ? ['/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf', 'C:/Windows/Fonts/arialbd.ttf']
            : ['/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf', 'C:/Windows/Fonts/arial.ttf'];
        foreach ($candidates as $candidate) if (is_file($candidate)) return $candidate;
        throw new RuntimeException('Fonte necessária para gerar o Ranking Diário não encontrada.');
    }

    private function truncate(string $value, int $length): string
    {
        return mb_strlen($value) <= $length ? $value : rtrim(mb_substr($value, 0, $length - 1)) . '…';
    }
}
