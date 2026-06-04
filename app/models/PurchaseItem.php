<?php
/**
 * PurchaseItem Model
 * Handles database queries for line items within orders.
 * Each row represents one artwork purchased in one order.
 */

class PurchaseItem
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get a single purchase item by ID.
     */
    public function getById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT purchase_item_id, purchase_id, product_id, quantity, unit_price
             FROM purchase_item
             WHERE purchase_item_id = ?",
            [$id]
        );
    }

    /**
     * Get all line items for a specific purchase.
     * Ordered by item ID for consistent output.
     */
    public function getByPurchase(int $purchase_id): array
    {
        return $this->db->fetchAll(
            "SELECT purchase_item_id, purchase_id, product_id, quantity, unit_price
             FROM purchase_item
             WHERE purchase_id = ?
             ORDER BY purchase_item_id ASC",
            [$purchase_id]
        );
    }

    /**
     * Create a single purchase line item.
     * Returns the new purchase_item_id.
     */
    public function create(int $purchase_id, int $product_id, int $quantity, float $unit_price): int
    {
        $this->db->execute(
            "INSERT INTO purchase_item (purchase_id, product_id, quantity, unit_price)
             VALUES (?, ?, ?, ?)",
            [$purchase_id, $product_id, $quantity, $unit_price]
        );

        return (int) $this->db->lastInsertId();
    }

    /**
     * Create multiple purchase items at once.
     * Useful within a transaction to add several items to a purchase.
     * 
     * @param int $purchase_id
     * @param array $items Array of ['product_id' => int, 'quantity' => int, 'unit_price' => float]
     */
    public function createMany(int $purchase_id, array $items): void
    {
        foreach ($items as $item) {
            $this->create(
                $purchase_id,
                $item['product_id'],
                $item['quantity'],
                $item['unit_price']
            );
        }
    }

    /**
     * Delete a purchase item (rarely used, typically via cascade on purchase delete).
     */
    public function delete(int $purchase_item_id): int
    {
        return $this->db->execute(
            "DELETE FROM purchase_item WHERE purchase_item_id = ?",
            [$purchase_item_id]
        );
    }
}
