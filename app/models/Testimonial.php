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
     * Submit a new testimonial from a customer.
     * Status defaults to 'pending' — admin must approve before it goes public.
     *
     * @param string $customerName  Customer's display name
     * @param string $email         Customer's email address
     * @param string $content       The testimonial message
     * @return string               The new testimonial_id
     */
    public function create(string $customerName, string $email, string $content): string
    {
        $this->db->execute(
            "INSERT INTO testimonial (customer_name, email, content, status, submitted_at)
             VALUES (?, ?, ?, 'pending', NOW())",
            [$customerName, $email, $content]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Get all approved testimonials for the public page.
     * Ordered newest first.
     *
     * @return array
     */
    public function getApproved(): array
    {
        return $this->db->fetchAll(
            "SELECT testimonial_id, customer_name, content, submitted_at
             FROM testimonial
             WHERE status = 'approved'
             ORDER BY submitted_at DESC"
        );
    }

    /**
     * Get all pending testimonials for admin moderation.
     * Ordered oldest first so the admin clears the queue in order.
     *
     * @return array
     */
    public function getPending(): array
    {
        return $this->db->fetchAll(
            "SELECT testimonial_id, customer_name, email, content, submitted_at
             FROM testimonial
             WHERE status = 'pending'
             ORDER BY submitted_at ASC"
        );
    }

    /**
     * Approve a testimonial — it will now appear on the public page.
     * Records which admin approved it and when.
     *
     * @param int $id       The testimonial_id to approve
     * @param int $adminId  The admin_id of the moderator
     * @return int          Number of rows affected
     */
    public function approve(int $id, int $adminId): int
    {
        return $this->db->execute(
            "UPDATE testimonial
             SET status = 'approved', moderated_by = ?, moderated_at = NOW()
             WHERE testimonial_id = ?",
            [$adminId, $id]
        );
    }

    /**
     * Reject a testimonial — it will not appear publicly.
     * Records which admin rejected it and when.
     *
     * @param int $id       The testimonial_id to reject
     * @param int $adminId  The admin_id of the moderator
     * @return int          Number of rows affected
     */
    public function reject(int $id, int $adminId): int
    {
        return $this->db->execute(
            "UPDATE testimonial
             SET status = 'rejected', moderated_by = ?, moderated_at = NOW()
             WHERE testimonial_id = ?",
            [$adminId, $id]
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