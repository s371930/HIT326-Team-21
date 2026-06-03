<?php
/**
 * News Model
 * Handles database queries for the news feature.
 *
 * Project requirement (Option 2, Feature A):
 * "Only the most recent message is to appear on the front page
 *  with no option for readers to read older posts."
 */

class News
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get the single most recent news item.
     * Uses ORDER BY posted_at DESC LIMIT 1 to return only the latest.
     * Returns null if no news exists yet.
     */
    public function getLatest(): ?array
    {
        return $this->db->fetchOne(
            "SELECT news_id, title, content, posted_at
             FROM news
             ORDER BY posted_at DESC
             LIMIT 1"
        );
    }

    /**
     * Get all news items (for admin management only).
     * Ordered newest first.
     */
    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT n.news_id, n.title, n.content, n.posted_at,
                    a.username AS author
             FROM news n
             LEFT JOIN admin a ON n.admin_id = a.admin_id
             ORDER BY n.posted_at DESC"
        );
    }

    /**
     * Get a single news item by ID (for editing).
     */
    public function getById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT news_id, title, content, posted_at
             FROM news
             WHERE news_id = ?",
            [$id]
        );
    }

    /**
     * Create a new news item.
     * Returns the new news_id.
     */
    public function create(string $title, string $content, int $adminId): string
    {
        $this->db->execute(
            "INSERT INTO news (title, content, admin_id, posted_at)
             VALUES (?, ?, ?, NOW())",
            [$title, $content, $adminId]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Update an existing news item.
     */
    public function update(int $id, string $title, string $content): int
    {
        return $this->db->execute(
            "UPDATE news SET title = ?, content = ?, posted_at = NOW()
             WHERE news_id = ?",
            [$title, $content, $id]
        );
    }

    /**
     * Delete a news item.
     */
    public function delete(int $id): int
    {
        return $this->db->execute(
            "DELETE FROM news WHERE news_id = ?",
            [$id]
        );
    }
}