<?php
namespace App\Core;

final class RequiredSchema
{
    public static function assert(\PDO $db, string $module, array $tables, array $indexes = []): void
    {
        $missing = [];
        foreach ($tables as $table => $columns) {
            $stmt = $db->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table');
            $stmt->execute([':table' => $table]);
            $found = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            if ($found === []) {
                $missing[] = 'tabela ' . $table;
                continue;
            }
            foreach ($columns as $column) {
                if (!in_array($column, $found, true)) {
                    $missing[] = 'coluna ' . $table . '.' . $column;
                }
            }
        }
        foreach ($indexes as $table => $required) {
            $stmt = $db->prepare('SELECT DISTINCT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table');
            $stmt->execute([':table' => $table]);
            $found = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($required as $index) {
                if (!in_array($index, $found, true)) {
                    $missing[] = 'indice ' . $table . '.' . $index;
                }
            }
        }
        if ($missing !== []) {
            app_log('Schema obrigatorio ausente', ['modulo' => $module, 'itens' => count($missing)]);
            throw new SchemaOutdatedException('A estrutura do banco de dados desta instalacao esta desatualizada. Contate o administrador para executar a atualizacao.');
        }
    }
}
