<?php
/**
 * Customer model
 *
 * Manages customer records (guest checkout).
 */

require_once __DIR__ . '/../core/Database.php';

class Customer
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find a customer by email.
     *
     * @param string $email
     * @return array|null Customer row or null if not found
     */
    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM customer WHERE email = ?",
            [$email]
        );
    }

    /**
     * Get a customer by ID.
     *
     * @param int $customerId
     * @return array|null Customer row or null if not found
     */
    public function getById(int $customerId): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM customer WHERE customer_id = ?",
            [$customerId]
        );
    }

    /**
     * Create a new customer.
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string|null $phone
     * @return int The new customer_id
     */
    public function create(
        string $email,
        string $firstName,
        string $lastName,
        ?string $phone = null
    ): int {
        $this->db->execute(
            "INSERT INTO customer (email, first_name, last_name, phone) VALUES (?, ?, ?, ?)",
            [$email, $firstName, $lastName, $phone]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Update customer details.
     *
     * @param int $customerId
     * @param string $firstName
     * @param string $lastName
     * @param string|null $phone
     * @return bool True if successful
     */
    public function update(
        int $customerId,
        string $firstName,
        string $lastName,
        ?string $phone = null
    ): bool {
        $result = $this->db->execute(
            "UPDATE customer SET first_name = ?, last_name = ?, phone = ? WHERE customer_id = ?",
            [$firstName, $lastName, $phone, $customerId]
        );
        return $result > 0;
    }
}
