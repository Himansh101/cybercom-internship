<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * SavedView model — PDO queries for the saved_views table.
 */
class SavedViewModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all saved views for a user, ordered by default first, then newest.
     *
     * @param int $userId
     * @return array
     */
    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM saved_views WHERE user_id = ? ORDER BY is_default DESC, created_at DESC'
        );
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();

        // Decode config JSON for each view
        return array_map(function (array $row) {
            $row['config'] = json_decode($row['config'], true) ?? [];
            return $row;
        }, $rows);
    }

    /**
     * Get all saved views that can be applied by any authenticated user.
     *
     * @return array
     */
    public function findAllVisible(): array
    {
        $stmt = $this->db->query(
            'SELECT sv.*, u.name AS owner_name
             FROM saved_views sv
             INNER JOIN users u ON u.id = sv.user_id
             ORDER BY sv.is_default DESC, sv.created_at DESC'
        );
        $rows = $stmt->fetchAll();

        return array_map(function (array $row) {
            $row['config'] = json_decode($row['config'], true) ?? [];
            return $row;
        }, $rows);
    }

    /**
     * Find a single saved view by ID.
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM saved_views WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $row['config'] = json_decode($row['config'], true) ?? [];
        return $row;
    }

    /**
     * Create a new saved view.
     *
     * @param array $data  user_id, report_id, name, config (JSON string), is_default
     * @return int         Inserted ID.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO saved_views (user_id, report_id, name, config, is_default)
             VALUES (:user_id, :report_id, :name, :config, :is_default)'
        );
        $stmt->execute([
            ':user_id'    => $data['user_id'],
            ':report_id'  => $data['report_id'] ?? 'sales_report',
            ':name'       => $data['name'],
            ':config'     => $data['config'],
            ':is_default' => $data['is_default'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update a saved view by ID.
     *
     * @param int   $id
     * @param array $data  name, config, is_default
     */
    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE saved_views SET name = :name, config = :config, is_default = :is_default WHERE id = :id'
        );
        $stmt->execute([
            ':name'       => $data['name'],
            ':config'     => $data['config'],
            ':is_default' => $data['is_default'] ?? 0,
            ':id'         => $id,
        ]);
    }

    /**
     * Delete a saved view by ID.
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM saved_views WHERE id = ?');
        $stmt->execute([$id]);
    }

    /**
     * Clear all default flags for a user (before setting a new default).
     *
     * @param int $userId
     */
    public function clearDefaults(int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE saved_views SET is_default = 0 WHERE user_id = ?');
        $stmt->execute([$userId]);
    }

    /**
     * Find the default view for a user, if one exists.
     *
     * @param int $userId
     * @return array|null
     */
    public function findDefault(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM saved_views WHERE user_id = ? AND is_default = 1 LIMIT 1'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $row['config'] = json_decode($row['config'], true) ?? [];
        return $row;
    }
}
