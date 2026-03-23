<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * ReportModel — persists column config per user per report.
 */
class ReportModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get column configuration for a specific user + report combination.
     *
     * @param int    $userId
     * @param string $reportId
     * @return array|null
     */
    public function getColumnConfig(int $userId, string $reportId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT column_config FROM column_config WHERE user_id = ? AND report_id = ? LIMIT 1'
        );
        $stmt->execute([$userId, $reportId]);
        $row = $stmt->fetch();
        return $row ? json_decode($row['column_config'], true) : null;
    }

    /**
     * Upsert column configuration for a user + report.
     *
     * @param int    $userId
     * @param string $reportId
     * @param array  $config    Column width/order data.
     */
    public function saveColumnConfig(int $userId, string $reportId, array $config): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO column_config (user_id, report_id, column_config)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE column_config = VALUES(column_config)'
        );
        $stmt->execute([$userId, $reportId, json_encode($config)]);
    }
}
