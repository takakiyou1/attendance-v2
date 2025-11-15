<?php
/**
 * PaySetting Model
 * Handles pay rate settings and calculations
 */
require_once __DIR__ . '/../../core/Model.php';

class PaySetting extends Model {
    protected string $table = 'pay_settings';

    /**
     * Get active pay setting for user
     */
    public function getActiveForUser(int $userId): ?array {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE user_id = ?
              AND start_date <= CURDATE()
              AND (end_date IS NULL OR end_date >= CURDATE())
            ORDER BY start_date DESC
            LIMIT 1
        ";
        return Database::fetchOne($sql, [$userId]);
    }

    /**
     * Get all pay settings for user
     */
    public function getByUser(int $userId): array {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY start_date DESC";
        return Database::fetchAll($sql, [$userId]);
    }

    /**
     * Get pay setting for specific date
     */
    public function getForDate(int $userId, string $date): ?array {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE user_id = ?
              AND start_date <= ?
              AND (end_date IS NULL OR end_date >= ?)
            ORDER BY start_date DESC
            LIMIT 1
        ";
        return Database::fetchOne($sql, [$userId, $date, $date]);
    }
}
