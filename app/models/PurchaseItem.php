<?php
/**
 * PurchaseItem model
 *
 * Manages line items within a purchase (order).
 */

require_once __DIR__ . '/../core/Database.php';

class PurchaseItem
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all line items for a purchase.
     *
     * @param int $purchaseId
     * @return array List of purchase items
     */
    public function getByPurchase(int $purchaseId): array
    {
        return $this->db->fetchAll(
            "SELECT pi.*, p.name, p.image_filename FROM purchase_item pi
             JOIN product p ON pi.product_id = p.product_id
             WHERE pi.purchase_id = ?
             ORDER BY pi.purchase_item_id",
            [$purchaseId]
        );
    }

    /**
     * Create a single purchase line item.
     *
     * @param int $purchaseId
     * @param int $productId
     * @param int $quantity
     * @param float $unitPrice
     * @return int The new purchase_item_id
     */
    public function create(
        int $purchaseId,
        int $productId,
        int $quantity,
        float $unitPrice
    ): int {
        $this->db->execute(
            "INSERT INTO purchase_item (purchase_id, product_id, quantity, unit_price)
             VALUES (?, ?, ?, ?)",
            [$purchaseId, $productId, $quantity, $unitPrice]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Create multiple line items at once.
     * Expects an array of arrays, each with keys: product_id, quantity, unit_price.
     *
     * @param int $purchaseId
     * @param array $items Array of arrays with product_id, quantity, unit_price
     * @return void
     */
    public function createMany(int $purchaseId, array $items): void
    {
        foreach ($items as $item) {
            $this->create(
                $purchaseId,
                $item['product_id'],
                $item['quantity'],
                $item['unit_price']
            );
        }
    }

    /**
     * Get a single purchase item by ID.
     *
     * @param int $purchaseItemId
     * @return array|null
     */
    public function getById(int $purchaseItemId): ?array
    {
        return $this->db->fetchOne(
            "SELECT pi.*, p.name FROM purchase_item pi
             JOIN product p ON pi.product_id = p.product_id
             WHERE pi.purchase_item_id = ?",
            [$purchaseItemId]
        );
    }
}
