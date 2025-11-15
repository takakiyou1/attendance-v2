<?php
/**
 * Base Model Class
 * Provides CRUD operations and database access for all models
 */
require_once __DIR__ . '/Database.php';

class Model {
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    /**
     * Find record by ID
     */
    public function find($id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return Database::fetchOne($sql, [$id]);
    }

    /**
     * Get all records
     */
    public function all(): array {
        $sql = "SELECT * FROM {$this->table}";
        return Database::fetchAll($sql);
    }

    /**
     * Create new record
     */
    public function create(array $data): int {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        Database::query($sql, array_values($data));
        return (int)Database::lastInsertId();
    }

    /**
     * Update record
     */
    public function update($id, array $data): bool {
        $fields = array_keys($data);
        $setClause = implode(' = ?, ', $fields) . ' = ?';

        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?";

        $params = array_values($data);
        $params[] = $id;

        Database::query($sql, $params);
        return true;
    }

    /**
     * Delete record
     */
    public function delete($id): bool {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        Database::query($sql, [$id]);
        return true;
    }

    /**
     * Find records by column value
     */
    public function where(string $column, $value): array {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        return Database::fetchAll($sql, [$value]);
    }

    /**
     * Get PDO instance (for complex queries)
     */
    protected function getPdo(): PDO {
        return $this->pdo;
    }
}
