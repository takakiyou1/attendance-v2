<?php
/**
 * News Model
 * Handles news/announcements data
 */

class News extends Model
{
    protected string $table = 'news';
    protected string $primaryKey = 'id';

    /**
     * Get all news posts ordered by newest first
     */
    public function getAllNews(): array
    {
        $sql = "
            SELECT n.*, u.name as author_name
            FROM {$this->table} n
            LEFT JOIN users u ON n.user_id = u.id
            ORDER BY n.created_at DESC
        ";
        return Database::fetchAll($sql);
    }

    /**
     * Get single news post with author info
     */
    public function getNewsWithAuthor(int $id): ?array
    {
        $sql = "
            SELECT n.*, u.name as author_name
            FROM {$this->table} n
            LEFT JOIN users u ON n.user_id = u.id
            WHERE n.id = ?
        ";
        return Database::fetchOne($sql, [$id]);
    }

    /**
     * Create news post
     */
    public function createNews(array $data): int
    {
        $sql = "
            INSERT INTO {$this->table} (title, content, user_id, display_date, is_pinned)
            VALUES (?, ?, ?, CURDATE(), 0)
        ";

        $stmt = Database::query($sql, [
            $data['title'],
            $data['content'],
            $data['author_id']
        ]);

        return (int) Database::getInstance()->lastInsertId();
    }

    /**
     * Delete news post
     */
    public function deleteNews(int $id): bool
    {
        return $this->delete($id);
    }

    /**
     * Get recent news (limit)
     */
    public function getRecentNews(int $limit = 5): array
    {
        $sql = "
            SELECT n.*, u.name as author_name
            FROM {$this->table} n
            LEFT JOIN users u ON n.user_id = u.id
            ORDER BY n.created_at DESC
            LIMIT ?
        ";
        return Database::fetchAll($sql, [$limit]);
    }
}
