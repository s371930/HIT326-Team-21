<?php
/**
 * Admin Model
 * Handles database queries for administrator accounts.
 * Passwords are stored as bcrypt hashes using PHP's password_hash().
 */

class Admin
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find an admin by username.
     * Returns the admin record or null if not found.
     * Includes the password_hash for authentication.
     */
    public function findByUsername(string $username): ?array
    {
        return $this->db->fetchOne(
            "SELECT admin_id, username, password_hash, created_at
             FROM admin
             WHERE username = ?",
            [$username]
        );
    }

    /**
     * Get an admin by ID.
     */
    public function getById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT admin_id, username, created_at
             FROM admin
             WHERE admin_id = ?",
            [$id]
        );
    }

    /**
     * Get all administrator accounts (for admin management).
     * Does NOT include password hashes to prevent accidental exposure.
     */
    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT admin_id, username, created_at
             FROM admin
             ORDER BY created_at ASC"
        );
    }

    /**
     * Create a new admin account.
     * The password must be pre-hashed using Auth::hashPassword().
     * Returns the new admin_id.
     */
    public function create(string $username, string $password_hash): int
    {
        $this->db->execute(
            "INSERT INTO admin (username, password_hash)
             VALUES (?, ?)",
            [$username, $password_hash]
        );

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an admin's password.
     * The new password must be pre-hashed using Auth::hashPassword().
     * Returns the number of rows affected.
     */
    public function updatePassword(int $admin_id, string $password_hash): int
    {
        return $this->db->execute(
            "UPDATE admin SET password_hash = ? WHERE admin_id = ?",
            [$password_hash, $admin_id]
        );
    }

    /**
     * Delete an admin account (rarely used, typically soft-delete via foreign key policy).
     */
    public function delete(int $admin_id): int
    {
        return $this->db->execute(
            "DELETE FROM admin WHERE admin_id = ?",
            [$admin_id]
        );
    }
}
