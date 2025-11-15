<?php
/**
 * Shift Model
 * Handles shift scheduling and management
 */
require_once __DIR__ . '/../../core/Model.php';

class Shift extends Model {
    protected string $table = 'shifts';

    /**
     * Get all shifts with user information
     */
    public function getAllWithUsers(): array {
        $sql = "
            SELECT
                s.id, s.user_id, s.date, s.shift_start, s.shift_end,
                s.repeat_id, s.color,
                u.name as user_name
            FROM {$this->table} s
            JOIN users u ON s.user_id = u.id
            ORDER BY s.date ASC
        ";
        return Database::fetchAll($sql);
    }

    /**
     * Get shifts by user ID
     */
    public function getByUser(int $userId): array {
        return $this->where('user_id', $userId);
    }

    /**
     * Get shifts by date
     */
    public function getByDate(string $date): array {
        $sql = "
            SELECT s.*, u.name as user_name
            FROM {$this->table} s
            JOIN users u ON s.user_id = u.id
            WHERE s.date = ?
            ORDER BY s.shift_start ASC
        ";
        return Database::fetchAll($sql, [$date]);
    }

    /**
     * Get shifts by date range
     */
    public function getByDateRange(string $startDate, string $endDate): array {
        $sql = "
            SELECT s.*, u.name as user_name
            FROM {$this->table} s
            JOIN users u ON s.user_id = u.id
            WHERE s.date BETWEEN ? AND ?
            ORDER BY s.date ASC, s.shift_start ASC
        ";
        return Database::fetchAll($sql, [$startDate, $endDate]);
    }

    /**
     * Get shift with user info by ID
     */
    public function getWithUser(int $id): ?array {
        $sql = "
            SELECT s.*, u.name as user_name
            FROM {$this->table} s
            JOIN users u ON s.user_id = u.id
            WHERE s.id = ?
        ";
        return Database::fetchOne($sql, [$id]);
    }

    /**
     * Check for overlapping shifts
     */
    public function hasOverlap(int $userId, string $date, string $start, string $end, ?int $excludeId = null): bool {
        $sql = "
            SELECT COUNT(*) as count
            FROM {$this->table}
            WHERE user_id = ?
              AND date = ?
              AND id != ?
              AND (
                (shift_start < ? AND shift_end > ?) OR
                (shift_start < ? AND shift_end > ?) OR
                (shift_start >= ? AND shift_end <= ?)
              )
        ";

        $result = Database::fetchOne($sql, [
            $userId, $date, $excludeId ?: 0,
            $end, $start,
            $start, $end,
            $start, $end
        ]);

        return $result['count'] > 0;
    }

    /**
     * Create shift with validation
     */
    public function createShift(array $data): int {
        // Check for overlap
        if ($this->hasOverlap(
            $data['user_id'],
            $data['date'],
            $data['shift_start'],
            $data['shift_end']
        )) {
            throw new Exception('このスタッフは同時間帯に別シフトがあります。');
        }

        return $this->create($data);
    }

    /**
     * Update shift with validation
     */
    public function updateShift(int $id, array $data): bool {
        // Check for overlap
        if ($this->hasOverlap(
            $data['user_id'],
            $data['date'],
            $data['shift_start'],
            $data['shift_end'],
            $id
        )) {
            throw new Exception('このスタッフは同時間帯に別シフトがあります。');
        }

        return $this->update($id, $data);
    }

    /**
     * Delete shifts by repeat_id
     */
    public function deleteByRepeatId(string $repeatId): int {
        $sql = "DELETE FROM {$this->table} WHERE repeat_id = ?";
        $stmt = Database::query($sql, [$repeatId]);
        return $stmt->rowCount();
    }

    /**
     * Update shifts by repeat_id
     */
    public function updateByRepeatId(string $repeatId, array $data): int {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $params[] = $value;
        }

        $params[] = $repeatId;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE repeat_id = ?";
        $stmt = Database::query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Get shifts by repeat_id
     */
    public function getByRepeatId(string $repeatId): array {
        return $this->where('repeat_id', $repeatId);
    }

    /**
     * Create repeated shifts
     */
    public function createRepeated(array $data): array {
        $userId = $data['user_id'];
        $startDate = new DateTime($data['date']);
        $endDate = new DateTime($data['repeat_end']);
        $shiftStart = $data['shift_start'];
        $shiftEnd = $data['shift_end'];
        $repeatType = $data['repeat_type'];
        $days = $data['days'] ?? [];
        $repeatId = 'REP_' . date('Ymd_His') . '_' . rand(100, 999);
        $color = $data['color'] ?? null;

        $created = [];
        $currentDate = clone $startDate;

        while ($currentDate <= $endDate) {
            // Check day of week for weekly repeats
            if ($repeatType === 'weekly' && !empty($days)) {
                if (!in_array($currentDate->format('N'), $days)) {
                    $currentDate->modify('+1 day');
                    continue;
                }
            }

            // Check for overlap
            if (!$this->hasOverlap($userId, $currentDate->format('Y-m-d'), $shiftStart, $shiftEnd)) {
                $shiftData = [
                    'user_id' => $userId,
                    'date' => $currentDate->format('Y-m-d'),
                    'shift_start' => $shiftStart,
                    'shift_end' => $shiftEnd,
                    'repeat_id' => $repeatId
                ];

                if ($color) {
                    $shiftData['color'] = $color;
                }

                $id = $this->create($shiftData);
                $created[] = $id;
            }

            // Advance date
            if ($repeatType === 'daily') {
                $currentDate->modify('+1 day');
            } elseif ($repeatType === 'weekly') {
                $currentDate->modify('+1 week');
            } elseif ($repeatType === 'monthly') {
                $currentDate->modify('+1 month');
            } else {
                break;
            }
        }

        return [
            'count' => count($created),
            'repeat_id' => $repeatId,
            'created_ids' => $created
        ];
    }
}
