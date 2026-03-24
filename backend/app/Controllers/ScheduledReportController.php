<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ScheduledReportModel;

/**
 * CRUD controller for scheduled report definitions.
 */
class ScheduledReportController
{
    private ScheduledReportModel $model;

    public function __construct()
    {
        $this->model = new ScheduledReportModel();
    }

    public function index(): void
    {
        if (!$this->canManageSchedules()) {
            http_response_code(403);
            $this->json(false, null, 'Only admins can manage scheduled reports');
            return;
        }

        $this->json(true, $this->model->findAll());
    }

    public function store(): void
    {
        if (!$this->canManageSchedules()) {
            http_response_code(403);
            $this->json(false, null, 'Only admins can manage scheduled reports');
            return;
        }

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $reportName = trim((string) ($body['report_name'] ?? ''));
        $recipientEmail = trim((string) ($body['recipient_email'] ?? ''));
        $frequency = (string) ($body['frequency'] ?? 'daily');
        $sendTime = trim((string) ($body['send_time'] ?? ''));
        $timezone = trim((string) ($body['timezone'] ?? 'UTC'));
        $payload = is_array($body['payload'] ?? null) ? $body['payload'] : [];

        if ($reportName === '' || $recipientEmail === '' || $sendTime === '' || $payload === []) {
            http_response_code(400);
            $this->json(false, null, 'Report name, recipient email, send time, and payload are required');
            return;
        }

        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            $this->json(false, null, 'Recipient email is invalid');
            return;
        }

        if (!preg_match('/^\d{2}:\d{2}$/', $sendTime)) {
            http_response_code(400);
            $this->json(false, null, 'Send time must be in HH:MM format');
            return;
        }

        if (!in_array($frequency, ['daily', 'weekly', 'monthly'], true)) {
            http_response_code(400);
            $this->json(false, null, 'Frequency must be daily, weekly, or monthly');
            return;
        }

        $dayOfWeek = $frequency === 'weekly' ? (int) ($body['day_of_week'] ?? -1) : null;
        $dayOfMonth = $frequency === 'monthly' ? (int) ($body['day_of_month'] ?? 0) : null;

        if ($frequency === 'weekly' && ($dayOfWeek < 0 || $dayOfWeek > 6)) {
            http_response_code(400);
            $this->json(false, null, 'Weekly schedules require day_of_week between 0 and 6');
            return;
        }

        if ($frequency === 'monthly' && ($dayOfMonth < 1 || $dayOfMonth > 28)) {
            http_response_code(400);
            $this->json(false, null, 'Monthly schedules require day_of_month between 1 and 28');
            return;
        }

        $id = $this->model->create([
            'user_id' => (int) ($_REQUEST['auth_user_id'] ?? 0),
            'report_name' => $reportName,
            'recipient_email' => $recipientEmail,
            'frequency' => $frequency,
            'send_time' => $sendTime,
            'day_of_week' => $dayOfWeek,
            'day_of_month' => $dayOfMonth,
            'timezone' => $timezone === '' ? 'UTC' : $timezone,
            'payload' => json_encode($payload),
            'is_active' => !empty($body['is_active']) ? 1 : 0,
        ]);

        http_response_code(201);
        $this->json(true, $this->model->findById($id));
    }

    public function destroy(int $id): void
    {
        if (!$this->canManageSchedules()) {
            http_response_code(403);
            $this->json(false, null, 'Only admins can manage scheduled reports');
            return;
        }

        $schedule = $this->model->findById($id);
        if ($schedule === null) {
            http_response_code(404);
            $this->json(false, null, 'Scheduled report not found');
            return;
        }

        $this->model->delete($id);
        $this->json(true, ['deleted' => true]);
    }

    private function canManageSchedules(): bool
    {
        return (string) ($_REQUEST['auth_user_role'] ?? 'viewer') === 'admin';
    }

    private function json(bool $success, mixed $data, ?string $error = null, array $meta = []): void
    {
        echo json_encode(['success' => $success, 'data' => $data, 'error' => $error, 'meta' => $meta]);
    }
}
