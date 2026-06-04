<?php
/**
 * Customer Model
 * Handles database queries for customer records.
 * One row per unique email (guest checkout model).
 */

class Customer
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find a customer by email address.
     * Returns the customer record or null if not found.
     */
    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne(
            "SELECT customer_id, email, first_name, last_name, phone, created_at
             FROM customer
             WHERE email = ?",
            [$email]
        );
    }

    /**
     * Get a customer by ID.
     */
    public function getById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT customer_id, email, first_name, last_name, phone, created_at
             FROM customer
             WHERE customer_id = ?",
            [$id]
        );
    }

    /**
     * Get all customers (for admin reporting).
     */
    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT customer_id, email, first_name, last_name, phone, created_at
             FROM customer
             ORDER BY created_at DESC"
        );
    }

    /**
     * Create a new customer record.
     * Returns the new customer_id.
     * Throws exception if email already exists (enforced by UNIQUE constraint).
     */
    public function create(string $email, string $first_name, string $last_name, ?string $phone = null): int
    {
        $this->db->execute(
            "INSERT INTO customer (email, first_name, last_name, phone)
             VALUES (?, ?, ?, ?)",
            [$email, $first_name, $last_name, $phone]
        );

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update customer details.
     * Returns the number of rows affected.
     */
    public function update(int $customer_id, array $data): int
    {
        $updates = [];
        $params = [];

        // Allow updates to first_name, last_name, phone
        if (isset($data['first_name'])) {
            $updates[] = "first_name = ?";
            $params[] = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $updates[] = "last_name = ?";
            $params[] = $data['last_name'];
        }
        if (isset($data['phone'])) {
            $updates[] = "phone = ?";
            $params[] = $data['phone'];
        }

        if (empty($updates)) {
            return 0;
        }

        $params[] = $customer_id;
        $sql = "UPDATE customer SET " . implode(", ", $updates) . " WHERE customer_id = ?";

        return $this->db->execute($sql, $params);
    }
}
