<?php

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

    /**
     * Get all products including unavailable ones (for admin panel).
     */
    public function getAllForAdmin(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM product ORDER BY created_at DESC"
        );
    }

    /**
     * Get a product by ID regardless of availability (for admin edit).
     */
    public function getByIdForAdmin(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM product WHERE product_id = ?",
            [$id]
        );
    }

    /**
     * Create a new product.
     */
    public function create(
        string $name,
        ?string $description,
        float $price,
        ?string $category,
        ?string $color,
        ?string $size,
        ?string $imageFilename
    ): int {
        $this->db->execute(
            "INSERT INTO product (name, description, price, category, color, size, image_filename, is_available)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1)",
            [$name, $description, $price, $category, $color, $size, $imageFilename]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Update a product.
     */
    public function update(
        int $id,
        string $name,
        ?string $description,
        float $price,
        ?string $category,
        ?string $color,
        ?string $size,
        ?string $imageFilename
    ): bool {
        $result = $this->db->execute(
            "UPDATE product SET name = ?, description = ?, price = ?, category = ?, color = ?, size = ?, image_filename = ?
             WHERE product_id = ?",
            [$name, $description, $price, $category, $color, $size, $imageFilename, $id]
        );
        return $result > 0;
    }

    /**
     * Soft-delete a product (mark as unavailable).
     */
    public function softDelete(int $id): bool
    {
        $result = $this->db->execute(
            "UPDATE product SET is_available = 0 WHERE product_id = ?",
            [$id]
        );
        return $result > 0;
    }
}