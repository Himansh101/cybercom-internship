<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ScheduledReportModel;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Finds due schedules and emails report CSVs.
 */
class ScheduledReportRunnerService
{
    private ScheduledReportModel $model;
    private ReportExportService $reportExport;
    private SmtpMailerService $mailer;

    public function __construct()
    {
        $this->model = new ScheduledReportModel();
        $this->reportExport = new ReportExportService();
        $this->mailer = new SmtpMailerService();
    }

    /**
     * @return array{processed:int,sent:int,failed:int}
     */
    public function runDueSchedules(): array
    {
        $processed = 0;
        $sent = 0;
        $failed = 0;

        foreach ($this->model->findActive() as $schedule) {
            if (!$this->isDue($schedule)) {
                continue;
            }

            $processed++;

            $export = null;
            try {
                $export = $this->reportExport->buildCsvFile($schedule['payload'] ?? []);
                $subject = $schedule['report_name'] . ' - Scheduled Report';
                $body = "Attached is your scheduled report export.\n\nGenerated at: " . gmdate('c');

                $maxBytes = (int) ($_ENV['SCHEDULE_ATTACHMENT_MAX_BYTES'] ?? 10485760);
                $fileSize = filesize($export['path']);
                if ($fileSize === false) {
                    throw new \RuntimeException('Unable to determine CSV attachment size');
                }
                if ($maxBytes > 0 && $fileSize > $maxBytes) {
                    throw new \RuntimeException('Scheduled report attachment exceeds size limit');
                }

                $this->mailer->sendCsvReportFromFile(
                    (string) $schedule['recipient_email'],
                    $subject,
                    $body,
                    $export['filename'],
                    $export['path']
                );

                @unlink($export['path']);

                $this->model->markRun((int) $schedule['id'], gmdate('Y-m-d H:i:s'));
                $this->model->logRun((int) $schedule['id'], 'success', 'Report emailed successfully', (string) $schedule['recipient_email']);
                $sent++;
            } catch (\Throwable $e) {
                if (!empty($export['path']) && is_string($export['path'])) {
                    @unlink($export['path']);
                }
                $this->model->markRun((int) $schedule['id'], gmdate('Y-m-d H:i:s'));
                $this->model->logRun((int) $schedule['id'], 'failed', $e->getMessage(), (string) $schedule['recipient_email']);
                $failed++;
            }
        }

        return ['processed' => $processed, 'sent' => $sent, 'failed' => $failed];
    }

    private function isDue(array $schedule): bool
    {
        $timezone = new DateTimeZone((string) ($schedule['timezone'] ?? 'UTC'));
        $now = new DateTimeImmutable('now', $timezone);
        $slot = $now->format('Y-m-d H:i');

        if (($schedule['send_time'] ?? '') !== $now->format('H:i')) {
            return false;
        }

        $frequency = (string) ($schedule['frequency'] ?? 'daily');
        if ($frequency === 'weekly' && (int) ($schedule['day_of_week'] ?? -1) !== (int) $now->format('w')) {
            return false;
        }

        if ($frequency === 'monthly' && (int) ($schedule['day_of_month'] ?? 0) !== (int) $now->format('j')) {
            return false;
        }

        $lastRunAt = $schedule['last_run_at'] ?? null;
        if (is_string($lastRunAt) && $lastRunAt !== '') {
            $last = new DateTimeImmutable($lastRunAt, new DateTimeZone('UTC'));
            $lastInZone = $last->setTimezone($timezone);
            if ($lastInZone->format('Y-m-d H:i') === $slot) {
                return false;
            }
        }

        return true;
    }
}
