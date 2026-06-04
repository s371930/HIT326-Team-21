<?php
/**
 * Purchase Model
 * Handles database queries for order (purchase) records.
 * Supports transactions for atomicity when inserting purchases + line items together.
 */

class Purchase
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get a purchase by ID.
     * Returns the purchase record or null if not found.
     */
    public function getById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT purchase_id, customer_id, purchase_date, total_amount, 
                    delivery_address, status
             FROM purchase
             WHERE purchase_id = ?",
            [$id]
        );
    }

    /**
     * Get all purchases (for admin reporting).
     */
    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT purchase_id, customer_id, purchase_date, total_amount, 
                    delivery_address, status
             FROM purchase
             ORDER BY purchase_date DESC"
        );
    }

    /**
     * Get purchases for a specific customer.
     */
    public function getByCustomer(int $customer_id): array
    {
        return $this->db->fetchAll(
            "SELECT purchase_id, customer_id, purchase_date, total_amount, 
                    delivery_address, status
             FROM purchase
             WHERE customer_id = ?
             ORDER BY purchase_date DESC",
            [$customer_id]
        );
    }

    /**
     * Create a new purchase record (typically as part of a transaction).
     * Returns the new purchase_id.
     * 
     * @param int $customer_id
     * @param decimal $total_amount
     * @param string $delivery_address
     * @return int The new purchase_id
     */
    public function create(int $customer_id, float $total_amount, string $delivery_address): int
    {
        $this->db->execute(
            "INSERT INTO purchase (customer_id, total_amount, delivery_address, status)
             VALUES (?, ?, ?, 'pending')",
            [$customer_id, $total_amount, $delivery_address]
        );

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update the status of a purchase.
     * Valid statuses: 'pending', 'confirmed'
     * Returns the number of rows affected.
     */
    public function updateStatus(int $purchase_id, string $status): int
    {
        return $this->db->execute(
            "UPDATE purchase SET status = ? WHERE purchase_id = ?",
            [$status, $purchase_id]
        );
    }

    /**
     * Begin a transaction.
     * Use when creating a purchase + its line items atomically.
     */
    public function beginTransaction(): void
    {
        $this->db->beginTransaction();
    }

    /**
     * Commit the active transaction.
     */
    public function commit(): void
    {
        $this->db->commit();
    }

    /**
     * Roll back the active transaction.
     */
    public function rollback(): void
    {
        $this->db->rollback();
    }
}
