<?php
/**
 * Testimonial Model
 * Handles database queries for customer testimonials.
 * Default status is 'pending'; admins must approve before public display.
 */

class Testimonial
{
    private Database $db;

    public function __construct()
    {
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
     * Status defaults to 'pending'.
     * Returns the new testimonial_id.
     */
    public function create(string $customer_name, ?string $email, string $content): int
    {
        $this->db->execute(
            "INSERT INTO testimonial (customer_name, email, content, status)
             VALUES (?, ?, ?, 'pending')",
            [$customer_name, $email, $content]
        );

        return (int) $this->db->lastInsertId();
    }

    /**
     * Approve a testimonial (move from pending to approved).
     * Records which admin approved it and at what time.
     * Returns the number of rows affected.
     */
    public function approve(int $testimonial_id, int $admin_id): int
    {
        return $this->db->execute(
            "UPDATE testimonial
             SET status = 'approved', moderated_by = ?, moderated_at = CURRENT_TIMESTAMP
             WHERE testimonial_id = ?",
            [$admin_id, $testimonial_id]
        );
    }

    /**
     * Reject a testimonial (move from pending to rejected).
     * Records which admin rejected it and at what time.
     * Returns the number of rows affected.
     */
    public function reject(int $testimonial_id, int $admin_id): int
    {
        return $this->db->execute(
            "UPDATE testimonial
             SET status = 'rejected', moderated_by = ?, moderated_at = CURRENT_TIMESTAMP
             WHERE testimonial_id = ?",
            [$admin_id, $testimonial_id]
        );
    }
}
