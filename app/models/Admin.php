<?php
/**
 * Admin model
 *
 * Manages admin user accounts.
 */

require_once __DIR__ . '/../core/Database.php';

class Admin
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find an admin by username.
     *
     * @param string $username
     * @return array|null Admin row or null if not found
     */
    public function findByUsername(string $username): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM admin WHERE username = ?",
            [$username]
        );
    }

    /**
     * Get an admin by ID.
     *
     * @param int $adminId
     * @return array|null Admin row or null if not found
     */
    public function getById(int $adminId): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM admin WHERE admin_id = ?",
            [$adminId]
        );
    }

    /**
     * Get all admins.
     *
     * @return array List of admin records
     */
    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT admin_id, username, created_at FROM admin ORDER BY created_at DESC"
        );
    }

    /**
     * Create a new admin user.
     *
     * @param string $username
     * @param string $passwordHash (use Auth::hashPassword() to generate this)
     * @return int The new admin_id
     */
    public function create(string $username, string $passwordHash): int
    {
        $this->db->execute(
            "INSERT INTO admin (username, password_hash) VALUES (?, ?)",
            [$username, $passwordHash]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Update an admin's password.
     *
     * @param int $adminId
     * @param string $passwordHash (use Auth::hashPassword() to generate this)
     * @return bool True if successful
     */
    public function updatePassword(int $adminId, string $passwordHash): bool
    {
        $result = $this->db->execute(
            "UPDATE admin SET password_hash = ? WHERE admin_id = ?",
            [$passwordHash, $adminId]
        );
        return $result > 0;
    }
}
