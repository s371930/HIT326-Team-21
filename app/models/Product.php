<?php
/**
 * Product Model
 * Handles all database queries related to artworks/products.
 */

class Product
{
    private Database $db;

    public function __construct()
    {
        // Use the existing singleton — no new connection created
        $this->db = Database::getInstance();
    }

    /**
     * Get all available products for the public listing.
     * Only shows products where is_available = 1.
     */
    public function getAllAvailable(): array
    {
        return $this->db->fetchAll(
            "SELECT product_id, name, description, price, category, color, size, image_filename
             FROM product
             WHERE is_available = 1
             ORDER BY created_at DESC"
        );
    }

    /**
     * Get a single product by its ID.
     * Returns null if not found or not available.
     */
    public function getById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT product_id, name, description, price, category, color, size, image_filename
             FROM product
             WHERE product_id = ? AND is_available = 1",
            [$id]
        );
    }

    /**
     * Get products filtered by category.
     */
    public function getByCategory(string $category): array
    {
        return $this->db->fetchAll(
            "SELECT product_id, name, description, price, category, color, size, image_filename
             FROM product
             WHERE is_available = 1 AND category = ?
             ORDER BY created_at DESC",
            [$category]
        );
    }

    /**
     * Get all unique categories (for filter buttons).
     */
    public function getCategories(): array
    {
        return $this->db->fetchAll(
            "SELECT DISTINCT category
             FROM product
             WHERE is_available = 1 AND category IS NOT NULL
             ORDER BY category"
        );
    }
}