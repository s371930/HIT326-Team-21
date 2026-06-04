<?php
/**
 * app/models/Testimonial.php
 *
 * Handles all database queries for customer testimonials.
 *
 * Project requirement (Option 2, Feature B):
 * "These would have to be moderated by the company before they are published."
 *
 * Testimonials have three possible statuses:
 *   - pending  : just submitted, awaiting admin review
 *   - approved : published on the public testimonials page
 *   - rejected : hidden from public, will not be published
 */

class Testimonial
{
    private Database $db;

    public function __construct()
    {
        // Use the existing singleton — no new connection created
        $this->db = Database::getInstance();
    }

    /**
     * Get all approved testimonials (public-facing query).
     * Ordered by most recent first.
     */
    public function getApproved(): array
    {
        return $this->db->fetchAll(
            "SELECT testimonial_id, customer_name, email, content, status,
                    submitted_at, moderated_by, moderated_at
             FROM testimonial
             WHERE status = 'approved'
             ORDER BY moderated_at DESC"
        );
    }

    /**
     * Get all pending testimonials (admin moderation queue).
     * Ordered by oldest-submitted first (FIFO).
     */
    public function getPending(): array
    {
        return $this->db->fetchAll(
            "SELECT testimonial_id, customer_name, email, content, status,
                    submitted_at, moderated_by, moderated_at
             FROM testimonial
             WHERE status = 'pending'
             ORDER BY submitted_at ASC"
        );
    }

    /**
     * Get all testimonials (admin view - unfiltered).
     */
    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT testimonial_id, customer_name, email, content, status,
                    submitted_at, moderated_by, moderated_at
             FROM testimonial
             ORDER BY submitted_at DESC"
        );
    }

    /**
     * Get a testimonial by ID.
     */
    public function getById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT testimonial_id, customer_name, email, content, status,
                    submitted_at, moderated_by, moderated_at
             FROM testimonial
             WHERE testimonial_id = ?",
            [$id]
        );
    }

    /**
     * Create a new testimonial (from public submission form).
     * Status defaults to 'pending' — admin must approve before it goes public.
     *
     * @param string      $customerName
     * @param string|null $email
     * @param string      $content
     * @return int        The new testimonial_id
     */
    public function create(string $customerName, ?string $email, string $content): int
    {
        $this->db->execute(
            "INSERT INTO testimonial (customer_name, email, content, status)
             VALUES (?, ?, ?, 'pending')",
            [$customerName, $email, $content]
        );
        return (int)$this->db->lastInsertId();
    }

    /**
     * Approve a testimonial — it will now appear on the public page.
     * Records which admin approved it and when.
     *
     * @param int $testimonialId
     * @param int $adminId
     * @return int Number of rows affected
     */
    public function approve(int $testimonialId, int $adminId): int
    {
        return $this->db->execute(
            "UPDATE testimonial
             SET status = 'approved', moderated_by = ?, moderated_at = CURRENT_TIMESTAMP
             WHERE testimonial_id = ?",
            [$adminId, $testimonialId]
        );
    }

    /**
     * Reject a testimonial — it will not appear publicly.
     * Records which admin rejected it and when.
     *
     * @param int $testimonialId
     * @param int $adminId
     * @return int Number of rows affected
     */
    public function reject(int $testimonialId, int $adminId): int
    {
        return $this->db->execute(
            "UPDATE testimonial
             SET status = 'rejected', moderated_by = ?, moderated_at = CURRENT_TIMESTAMP
             WHERE testimonial_id = ?",
            [$adminId, $testimonialId]
        );
    }

    /**
     * Count pending testimonials — used for the dashboard badge.
     *
     * @return int
     */
    public function countPending(): int
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) AS total FROM testimonial WHERE status = 'pending'"
        );
        return (int)($row['total'] ?? 0);
    }
}