<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Scheduled report model for schedule CRUD and run logging.
 */
class ScheduledReportModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT sr.*, u.name AS owner_name
             FROM scheduled_reports sr
             INNER JOIN users u ON u.id = sr.user_id
             ORDER BY sr.created_at DESC'
        );

        return array_map([$this, 'decodeRow'], $stmt->fetchAll());
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT sr.*, u.name AS owner_name
             FROM scheduled_reports sr
             INNER JOIN users u ON u.id = sr.user_id
             WHERE sr.id = ?
             LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ? $this->decodeRow($row) : null;
    }

    public function findActive(): array
    {
        $stmt = $this->db->query(
            'SELECT sr.*, u.name AS owner_name
             FROM scheduled_reports sr
             INNER JOIN users u ON u.id = sr.user_id
             WHERE sr.is_active = 1
             ORDER BY sr.id ASC'
        );

        return array_map([$this, 'decodeRow'], $stmt->fetchAll());
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO scheduled_reports
             (user_id, report_name, recipient_email, frequency, send_time, day_of_week, day_of_month, timezone, payload, is_active, last_run_at)
             VALUES
             (:user_id, :report_name, :recipient_email, :frequency, :send_time, :day_of_week, :day_of_month, :timezone, :payload, :is_active, :last_run_at)'
        );
        $stmt->execute([
            ':user_id' => $data['user_id'],
            ':report_name' => $data['report_name'],
            ':recipient_email' => $data['recipient_email'],
            ':frequency' => $data['frequency'],
            ':send_time' => $data['send_time'],
            ':day_of_week' => $data['day_of_week'],
            ':day_of_month' => $data['day_of_month'],
            ':timezone' => $data['timezone'],
            ':payload' => $data['payload'],
            ':is_active' => $data['is_active'] ?? 1,
            ':last_run_at' => $data['last_run_at'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM scheduled_reports WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function markRun(int $id, string $timestamp): void
    {
        $stmt = $this->db->prepare('UPDATE scheduled_reports SET last_run_at = ? WHERE id = ?');
        $stmt->execute([$timestamp, $id]);
    }

    public function logRun(int $scheduleId, string $status, ?string $message, ?string $deliveredTo): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO scheduled_report_runs (scheduled_report_id, status, message, delivered_to)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$scheduleId, $status, $message, $deliveredTo]);
    }

    public function findRuns(int $scheduleId, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM scheduled_report_runs
             WHERE scheduled_report_id = ?
             ORDER BY created_at DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $scheduleId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function decodeRow(array $row): array
    {
        $row['payload'] = json_decode($row['payload'], true) ?? [];
        $row['runs'] = $this->findRuns((int) $row['id']);
        return $row;
    }
}
