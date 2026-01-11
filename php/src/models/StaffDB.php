<?php
/**
 * Staff Database Model
 */

require_once __DIR__ . '/../config/database.php';

class StaffDB
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all active staff
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM staff WHERE active = 1 ORDER BY 
             CASE WHEN group_type = 'A' THEN 1 ELSE 2 END,
             CAST(SUBSTRING(code, 2) AS UNSIGNED)"
        );
        return $stmt->fetchAll();
    }

    /**
     * Get staff by group
     */
    public function getByGroup(string $group): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM staff WHERE group_type = ? AND active = 1 
             ORDER BY CAST(SUBSTRING(code, 2) AS UNSIGNED)"
        );
        $stmt->execute([$group]);
        return $stmt->fetchAll();
    }

    /**
     * Get staff by ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM staff WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get staff by code
     */
    public function getByCode(string $code): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM staff WHERE code = ?");
        $stmt->execute([$code]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Create new staff member
     */
    public function create(string $code, string $name, string $group): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO staff (code, name, group_type) VALUES (?, ?, ?)"
        );
        $stmt->execute([$code, $name, $group]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update staff name
     */
    public function update(int $id, string $name): bool
    {
        $stmt = $this->db->prepare("UPDATE staff SET name = ? WHERE id = ?");
        return $stmt->execute([$name, $id]);
    }

    /**
     * Delete staff (soft delete)
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE staff SET active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Hard delete staff
     */
    public function hardDelete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM staff WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Check if code exists
     */
    public function codeExists(string $code): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM staff WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get staff count by group
     */
    public function getCountByGroup(string $group): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM staff WHERE group_type = ? AND active = 1");
        $stmt->execute([$group]);
        return (int) $stmt->fetchColumn();
    }
}
