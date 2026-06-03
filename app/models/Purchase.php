<?php
/**
 * Purchase model
 *
 * Manages customer orders. Uses transactions to ensure data consistency
 * when creating an order with multiple line items.
 */

require_once __DIR__ . '/../core/Database.php';

class Purchase
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get a purchase by ID.
     *
     * @param int $purchaseId
     * @return array|null Purchase row or null if not found
     */
    public function getById(int $purchaseId): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM purchase WHERE purchase_id = ?",
            [$purchaseId]
        );
    }

    /**
     * Get all purchases by customer ID.
     *
     * @param int $customerId
     * @return array List of purchases
     */
    public function getByCustomerId(int $customerId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM purchase WHERE customer_id = ? ORDER BY purchase_date DESC",
            [$customerId]
        );
    }

    /**
     * Create a new purchase (order header).
     * This is typically paired with PurchaseItem::createMany() to add line items.
     *
     * @param int $customerId
     * @param float $totalAmount
     * @param string $deliveryAddress
     * @param string $status 'pending' or 'confirmed'
     * @return int The new purchase_id
     */
    public function create(
        int $customerId,
        float $totalAmount,
        string $deliveryAddress,
        string $status = 'pending'
    ): int {
        $this->db->execute(
            "INSERT INTO purchase (customer_id, total_amount, delivery_address, status)
             VALUES (?, ?, ?, ?)",
            [$customerId, $totalAmount, $deliveryAddress, $status]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Update purchase status.
     *
     * @param int $purchaseId
     * @param string $status 'pending' or 'confirmed'
     * @return bool True if successful
     */
    public function updateStatus(int $purchaseId, string $status): bool
    {
        $result = $this->db->execute(
            "UPDATE purchase SET status = ? WHERE purchase_id = ?",
            [$status, $purchaseId]
        );
        return $result > 0;
    }

    /**
     * Begin a transaction for multi-step writes.
     * Call this before creating a purchase and its items, then commit() or rollback().
     */
    public function beginTransaction(): void
    {
        $this->db->beginTransaction();
    }

    /**
     * Commit the current transaction.
     */
    public function commit(): void
    {
        $this->db->commit();
    }

    /**
     * Rollback the current transaction.
     */
    public function rollback(): void
    {
        $this->db->rollback();
    }
}
