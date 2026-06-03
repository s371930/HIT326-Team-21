<?php
/**
 * Testimonial model
 *
 * Manages customer testimonials with approval workflow.
 */

require_once __DIR__ . '/../core/Database.php';

class Testimonial
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all approved testimonials (public view).
     *
     * @return array List of approved testimonials, sorted by newest first
     */
    public function getApproved(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM testimonial WHERE status = 'approved' ORDER BY submitted_at DESC"
        );
    }

    /**
     * Get all pending testimonials (moderation queue).
     *
     * @return array List of pending testimonials, sorted by oldest first
     */
    public function getPending(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM testimonial WHERE status = 'pending' ORDER BY submitted_at ASC"
        );
    }

    /**
     * Get all testimonials (admin view, including rejected).
     *
     * @return array List of all testimonials
     */
    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, a.username as moderated_by_name FROM testimonial t
             LEFT JOIN admin a ON t.moderated_by = a.admin_id
             ORDER BY t.submitted_at DESC"
        );
    }

    /**
     * Get a testimonial by ID.
     *
     * @param int $testimonialId
     * @return array|null Testimonial row or null if not found
     */
    public function getById(int $testimonialId): ?array
    {
        return $this->db->fetchOne(
            "SELECT t.*, a.username as moderated_by_name FROM testimonial t
             LEFT JOIN admin a ON t.moderated_by = a.admin_id
             WHERE t.testimonial_id = ?",
            [$testimonialId]
        );
    }

    /**
     * Create a new testimonial submission.
     *
     * @param string $customerName
     * @param string|null $email
     * @param string $content
     * @return int The new testimonial_id
     */
    public function create(
        string $customerName,
        ?string $email,
        string $content
    ): int {
        $this->db->execute(
            "INSERT INTO testimonial (customer_name, email, content) VALUES (?, ?, ?)",
            [$customerName, $email, $content]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Approve a testimonial.
     *
     * @param int $testimonialId
     * @param int $adminId The ID of the admin approving this
     * @return bool True if successful
     */
    public function approve(int $testimonialId, int $adminId): bool
    {
        $result = $this->db->execute(
            "UPDATE testimonial
             SET status = 'approved', moderated_by = ?, moderated_at = NOW()
             WHERE testimonial_id = ?",
            [$adminId, $testimonialId]
        );
        return $result > 0;
    }

    /**
     * Reject a testimonial.
     *
     * @param int $testimonialId
     * @param int $adminId The ID of the admin rejecting this
     * @return bool True if successful
     */
    public function reject(int $testimonialId, int $adminId): bool
    {
        $result = $this->db->execute(
            "UPDATE testimonial
             SET status = 'rejected', moderated_by = ?, moderated_at = NOW()
             WHERE testimonial_id = ?",
            [$adminId, $testimonialId]
        );
        return $result > 0;
    }
}
