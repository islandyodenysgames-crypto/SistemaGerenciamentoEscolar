<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use PDO;
use RuntimeException;
use Throwable;
use ZipArchive;

final class SchoolDataMaintenanceService
{
    private PDO $db;
    private string $root;

    public function __construct()
    {
        $this->db = Connection::getInstance();
        $this->root = dirname(__DIR__, 2);
    }

    public function summary(): array
    {
        $counts = [];
        foreach (['students','school_classes','enrollments','attendance','student_occurrences','school_notices'] as $table) {
            $counts[$table] = $this->tableExists($table)
                ? (int)$this->db->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn()
                : 0;
        }

        return [
            'counts' => $counts,
            'lastBackup' => $this->lastBackupInfo(),
            'zipAvailable' => class_exists(ZipArchive::class),
        ];
    }

    public function createBackup(): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('A extensão ZIP do PHP não está habilitada. Ative extension=zip no php.ini do Laragon.');
        }

        $dir = $this->root . '/storage/backups';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Não foi possível criar a pasta de backups.');
        }

        $filename = 'backup-escolar-' . date('Y-m-d_H-i-s') . '.zip';
        $path = $dir . '/' . $filename;
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Não foi possível criar o arquivo de backup.');
        }

        $tables = $this->tables();
        $database = [];
        foreach ($tables as $table) {
            $database[$table] = $this->db->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        }

        $manifest = [
            'format' => 'sge-school-backup',
            'version' => 1,
            'created_at' => date(DATE_ATOM),
            'database' => (string)$this->db->query('SELECT DATABASE()')->fetchColumn(),
            'tables' => array_map(fn(string $table): array => [
                'name' => $table,
                'rows' => count($database[$table]),
            ], $tables),
        ];

        $zip->addFromString('manifest.json', $this->json($manifest));
        $zip->addFromString('database.json', $this->json($database));
        $this->addDirectoryToZip($zip, $this->root . '/public/uploads', 'uploads');
        $zip->close();

        file_put_contents($dir . '/last-backup.json', $this->json([
            'filename' => $filename,
            'created_at' => $manifest['created_at'],
            'size' => filesize($path) ?: 0,
        ]));

        return $path;
    }

    public function restoreBackup(array $file): void
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('A extensão ZIP do PHP não está habilitada.');
        }
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string)($file['tmp_name'] ?? ''))) {
            throw new RuntimeException('Selecione um arquivo de backup ZIP válido.');
        }
        if ((int)($file['size'] ?? 0) > 500 * 1024 * 1024) {
            throw new RuntimeException('O arquivo excede o limite de 500 MB.');
        }

        $zip = new ZipArchive();
        if ($zip->open((string)$file['tmp_name']) !== true) {
            throw new RuntimeException('Não foi possível abrir o arquivo ZIP.');
        }

        $manifestRaw = $zip->getFromName('manifest.json');
        $databaseRaw = $zip->getFromName('database.json');
        if ($manifestRaw === false || $databaseRaw === false) {
            $zip->close();
            throw new RuntimeException('O arquivo não é um backup válido deste sistema.');
        }

        $manifest = json_decode($manifestRaw, true);
        $database = json_decode($databaseRaw, true);
        if (!is_array($manifest) || ($manifest['format'] ?? '') !== 'sge-school-backup' || !is_array($database)) {
            $zip->close();
            throw new RuntimeException('O conteúdo do backup é inválido ou incompatível.');
        }

        $currentTables = array_flip($this->tables());
        $this->db->exec('SET FOREIGN_KEY_CHECKS=0');
        try {
            $this->db->beginTransaction();
            foreach (array_keys($currentTables) as $table) {
                $this->db->exec("DELETE FROM `{$table}`");
            }

            foreach ($database as $table => $rows) {
                if (!isset($currentTables[$table]) || !is_array($rows) || $rows === []) continue;
                $columns = array_keys((array)$rows[0]);
                if ($columns === []) continue;
                $quoted = implode(',', array_map(fn(string $c): string => '`' . str_replace('`', '', $c) . '`', $columns));
                $placeholders = implode(',', array_fill(0, count($columns), '?'));
                $statement = $this->db->prepare("INSERT INTO `{$table}` ({$quoted}) VALUES ({$placeholders})");
                foreach ($rows as $row) {
                    $statement->execute(array_map(fn(string $column) => $row[$column] ?? null, $columns));
                }
            }
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw new RuntimeException('Falha ao restaurar o banco de dados: ' . $e->getMessage(), 0, $e);
        } finally {
            $this->db->exec('SET FOREIGN_KEY_CHECKS=1');
        }

        $uploads = $this->root . '/public/uploads';
        $this->clearDirectory($uploads, []);
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string)$zip->getNameIndex($i);
            if (!str_starts_with($name, 'uploads/') || str_contains($name, '..')) continue;
            $relative = substr($name, 8);
            if ($relative === '' || str_ends_with($name, '/')) continue;
            $target = $uploads . '/' . ltrim(str_replace('\\', '/', $relative), '/');
            $dir = dirname($target);
            if (!is_dir($dir)) mkdir($dir, 0775, true);
            $stream = $zip->getStream($name);
            if ($stream !== false) {
                $out = fopen($target, 'wb');
                if ($out !== false) { stream_copy_to_stream($stream, $out); fclose($out); }
                fclose($stream);
            }
        }
        $zip->close();
    }

    public function clearSchoolData(array $options): array
    {
        $keepStudents = !empty($options['keep_students']);
        $keepClasses = $keepStudents || !empty($options['keep_classes']);
        $keepStudentPhotos = $keepStudents && !empty($options['keep_student_photos']);
        $keepClassPhotos = $keepClasses && !empty($options['keep_class_photos']);

        $alwaysKeep = ['migrations', 'users', 'schools', 'school_settings', 'system_settings', 'system_setting_revisions'];
        $preserve = $alwaysKeep;
        if ($keepClasses) $preserve[] = 'school_classes';
        if ($keepStudents) {
            $preserve[] = 'students';
            $preserve[] = 'enrollments';
        }

        $tables = $this->tables();
        $cleared = [];
        $this->db->exec('SET FOREIGN_KEY_CHECKS=0');
        try {
            $this->db->beginTransaction();
            foreach ($tables as $table) {
                if (in_array($table, $preserve, true)) continue;
                $this->db->exec("DELETE FROM `{$table}`");
                try { $this->db->exec("ALTER TABLE `{$table}` AUTO_INCREMENT = 1"); } catch (Throwable) {}
                $cleared[] = $table;
            }
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw new RuntimeException('Não foi possível limpar os dados: ' . $e->getMessage(), 0, $e);
        } finally {
            $this->db->exec('SET FOREIGN_KEY_CHECKS=1');
        }

        $uploads = $this->root . '/public/uploads';
        $keepDirs = ['schools', 'users'];
        if ($keepStudentPhotos) $keepDirs[] = 'students';
        if ($keepClassPhotos) $keepDirs[] = 'classes';
        $this->clearDirectory($uploads, $keepDirs);

        if ($keepStudents && !$keepStudentPhotos && $this->tableExists('students')) {
            $this->nullExistingColumn('students', ['photo_path', 'photo', 'image_path']);
        }
        if ($keepClasses && !$keepClassPhotos && $this->tableExists('school_classes')) {
            $this->nullExistingColumn('school_classes', ['photo_path', 'photo', 'image_path']);
        }

        return [
            'cleared_tables' => count($cleared),
            'kept_students' => $keepStudents,
            'kept_classes' => $keepClasses,
            'kept_student_photos' => $keepStudentPhotos,
            'kept_class_photos' => $keepClassPhotos,
        ];
    }

    private function tables(): array
    {
        $statement = $this->db->query('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = \'BASE TABLE\' ORDER BY TABLE_NAME');
        return array_values(array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN)));
    }

    private function tableExists(string $table): bool
    {
        $statement = $this->db->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
        $statement->execute([$table]);
        return (int)$statement->fetchColumn() > 0;
    }

    private function nullExistingColumn(string $table, array $candidates): void
    {
        foreach ($candidates as $column) {
            $statement = $this->db->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?');
            $statement->execute([$table, $column]);
            if ((int)$statement->fetchColumn() > 0) {
                $this->db->exec("UPDATE `{$table}` SET `{$column}` = NULL");
                return;
            }
        }
    }

    private function addDirectoryToZip(ZipArchive $zip, string $directory, string $prefix): void
    {
        if (!is_dir($directory)) return;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile()) continue;
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($directory) + 1));
            $zip->addFile($file->getPathname(), $prefix . '/' . $relative);
        }
    }

    private function clearDirectory(string $directory, array $keepTopLevel): void
    {
        if (!is_dir($directory)) return;
        foreach (scandir($directory) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..' || in_array($entry, $keepTopLevel, true)) continue;
            $this->removePath($directory . '/' . $entry);
        }
    }

    private function removePath(string $path): void
    {
        if (is_file($path) || is_link($path)) { @unlink($path); return; }
        if (!is_dir($path)) return;
        foreach (scandir($path) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            $this->removePath($path . '/' . $entry);
        }
        @rmdir($path);
    }

    private function lastBackupInfo(): ?array
    {
        $file = $this->root . '/storage/backups/last-backup.json';
        if (!is_file($file)) return null;
        $data = json_decode((string)file_get_contents($file), true);
        return is_array($data) ? $data : null;
    }

    private function json(array $data): string
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) throw new RuntimeException('Não foi possível serializar os dados do backup.');
        return $json;
    }
}
