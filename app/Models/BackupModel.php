<?php
namespace App\Models;

use App\Config\Database;

class BackupModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function databaseSql(): string
    {
        $database = app_env('DB_DATABASE', '');
        $generatedAt = date('Y-m-d H:i:s');
        $sql = "-- Backup do banco Conectados\n";
        $sql .= "-- Banco: " . $database . "\n";
        $sql .= "-- Gerado em: " . $generatedAt . "\n\n";
        $sql .= "SET NAMES utf8mb4;\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($this->tables() as $table) {
            $sql .= $this->tableDump($table);
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $sql;
    }

    public function storeSecureCopy(string $content, string $displayFilename): ?string
    {
        $directory = backup_storage_path();
        if ($directory === null) {
            return null;
        }

        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new \RuntimeException('Diretorio seguro de backup indisponivel.');
        }

        $realDirectory = realpath($directory);
        $realPublic = realpath(public_path());
        if ($realDirectory === false || ($realPublic !== false && str_starts_with(
            strtolower($realDirectory . DIRECTORY_SEPARATOR),
            strtolower($realPublic . DIRECTORY_SEPARATOR)
        ))) {
            throw new \RuntimeException('BACKUP_PATH deve apontar para fora do diretorio publico.');
        }

        $base = pathinfo($displayFilename, PATHINFO_FILENAME);
        $filename = $base . '-' . bin2hex(random_bytes(8)) . '.sql';
        $target = $realDirectory . DIRECTORY_SEPARATOR . $filename;
        if (file_put_contents($target, $content, LOCK_EX) !== strlen($content)) {
            throw new \RuntimeException('Nao foi possivel armazenar a copia segura do backup.');
        }

        @chmod($target, 0600);
        return $filename;
    }

    private function tables(): array
    {
        $stmt = $this->db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        $tables = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_NUM) as $row) {
            if (!empty($row[0])) {
                $tables[] = (string) $row[0];
            }
        }
        sort($tables);
        return $tables;
    }

    private function tableDump(string $table): string
    {
        $quotedTable = $this->identifier($table);
        $create = $this->db->query('SHOW CREATE TABLE ' . $quotedTable)->fetch(\PDO::FETCH_ASSOC);
        $createSql = (string) ($create['Create Table'] ?? '');

        $sql = "-- --------------------------------------------------------\n";
        $sql .= "-- Estrutura da tabela {$table}\n";
        $sql .= "DROP TABLE IF EXISTS {$quotedTable};\n";
        $sql .= $createSql . ";\n\n";

        $rows = $this->db->query('SELECT * FROM ' . $quotedTable, \PDO::FETCH_ASSOC);
        $columns = null;
        $batch = [];
        $batchSize = 80;

        foreach ($rows as $row) {
            if ($columns === null) {
                $columns = array_keys($row);
            }
            $batch[] = '(' . implode(', ', array_map(fn($value): string => $this->sqlValue($value), array_values($row))) . ')';
            if (count($batch) >= $batchSize) {
                $sql .= $this->insertSql($quotedTable, $columns, $batch);
                $batch = [];
            }
        }

        if (!empty($batch) && $columns !== null) {
            $sql .= $this->insertSql($quotedTable, $columns, $batch);
        }

        return $sql . "\n";
    }

    private function insertSql(string $quotedTable, array $columns, array $values): string
    {
        $columnSql = implode(', ', array_map(fn($column): string => $this->identifier((string) $column), $columns));
        return "INSERT INTO {$quotedTable} ({$columnSql}) VALUES\n" . implode(",\n", $values) . ";\n\n";
    }

    private function identifier(string $value): string
    {
        return '`' . str_replace('`', '``', $value) . '`';
    }

    private function sqlValue($value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        $value = (string) $value;
        $isUtf8 = function_exists('mb_check_encoding')
            ? mb_check_encoding($value, 'UTF-8')
            : preg_match('//u', $value) === 1;
        if ($value !== '' && (!$isUtf8 || str_contains($value, "\0"))) {
            return '0x' . bin2hex($value);
        }

        return $this->db->quote($value);
    }
}
