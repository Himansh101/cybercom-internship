<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\ScheduledReportRunnerService;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$runner = new ScheduledReportRunnerService();
$runOnce = in_array('--once', $argv, true);
$sleepSeconds = max(5, (int) ($_ENV['SCHEDULE_WORKER_INTERVAL_SECONDS'] ?? 60));

do {
    $result = $runner->runDueSchedules();
    $timestamp = gmdate('c');
    echo "[{$timestamp}] processed={$result['processed']} sent={$result['sent']} failed={$result['failed']}" . PHP_EOL;

    if ($runOnce) {
        break;
    }

    sleep($sleepSeconds);
} while (true);
