<?php
/**
 * User Model
 * Handles user data and authentication
 */
require_once __DIR__ . '/../../core/Model.php';

class User extends Model {
    protected string $table = 'users';

    /**
     * Get all employees (non-admin users)
     */
    public function getEmployees(): array {
        return $this->where('role', 'employee');
    }

    /**
     * Get all admins
     */
    public function getAdmins(): array {
        return $this->where('role', 'admin');
    }

    /**
     * Find user by name (username)
     */
    public function findByName(string $name): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE name = ?";
        return Database::fetchOne($sql, [$name]);
    }

    /**
     * Find user by credentials
     */
    public function findByCredentials(string $name, string $password): ?array {
        $user = $this->findByName($name);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return null;
    }

    /**
     * Check if username exists
     */
    public function nameExists(string $name, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE name = ?";
        $params = [$name];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = Database::fetchOne($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Create user with password hashing
     */
    public function createUser(array $data): int {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return $this->create($data);
    }

    /**
     * Update user with optional password change
     */
    public function updateUser(int $id, array $data): bool {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']); // Don't update if empty
        }
        return $this->update($id, $data);
    }
}
