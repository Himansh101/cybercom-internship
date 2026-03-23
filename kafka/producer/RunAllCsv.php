<?php

declare(strict_types=1);

ini_set('memory_limit', '512M');

require __DIR__ . '/vendor/autoload.php';

use longlang\phpkafka\Producer\Producer;
use longlang\phpkafka\Producer\ProducerConfig;

// ── Config ────────────────────────────────────────────────────────────────────
$folder     = __DIR__ . '/data/';
$topic      = $_ENV['KAFKA_TOPIC']  ?? 'report_data_topic';
$broker     = $_ENV['KAFKA_BROKER'] ?? 'kafka:9092';
$batchSize  = 1000;
$partitions = 4;

echo "========================================\n";
echo "  KAFKA PRODUCER - CSV INDEXER\n";
echo "========================================\n\n";

// ── Kafka producer ────────────────────────────────────────────────────────────
$config = new ProducerConfig();
$config->setBootstrapServer($broker);
$config->setAcks(-1);
$producer = new Producer($config);

// ── Find CSV files ────────────────────────────────────────────────────────────
$files = glob($folder . '*.csv');
if (!$files) {
    echo "No CSV files found in $folder\n";
    exit(0);
}

$fileCount = count($files);
echo "Broker     : $broker\n";
echo "Topic      : $topic\n";
echo "Folder     : $folder\n";
echo "Files found: $fileCount\n\n";

$total     = 0;
$fileIndex = 0;

foreach ($files as $file) {
    $fileIndex++;
    $fileName = basename($file);
    echo "[$fileIndex/$fileCount] Processing: $fileName\n";

    $handle = fopen($file, 'r');
    if (!$handle) {
        echo "  Cannot open — skipping\n";
        continue;
    }

    $headers = fgetcsv($handle);
    if (!$headers) {
        echo "  Empty header — skipping\n";
        fclose($handle);
        continue;
    }

    $headers   = array_map('trim', $headers);
    echo "  Headers: " . implode(', ', $headers) . "\n";

    $rowNumber = 0;
    $batch     = [];

    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) !== count($headers)) continue;

        $raw  = array_combine($headers, $row);
        $data = [];

        foreach ($raw as $key => $value) {
            $data[trim($key)] = autocast((string) $value);
        }

        // Tag with source file for traceability
        $data['_source_file'] = $fileName;

        $batch[] = ['data' => $data, 'row' => $rowNumber];
        $rowNumber++;
        $total++;

        if (count($batch) >= $batchSize) {
            sendBatch($producer, $topic, $batch, $fileName);
            $batch = [];
        }
    }

    if (!empty($batch)) {
        sendBatch($producer, $topic, $batch, $fileName);
    }

    fclose($handle);
    echo "  ✓ Finished — $rowNumber rows\n\n";
}

// ── Send sentinel signals (one per partition) so consumer knows we're done ───
echo "Sending sentinel signals to $partitions partitions...\n";
for ($p = 0; $p < $partitions; $p++) {
    $producer->send(
        $topic,
        json_encode(['__sentinel__' => true]),
        '__sentinel_' . $p . '__'
    );
}

$producer->close();

echo "\n========================================\n";
echo "  DONE — TOTAL ROWS SENT: $total\n";
echo "========================================\n";


// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Auto-cast a string value to int, float, bool, or string.
 */
function autocast(string $value): mixed
{
    $value = trim($value);
    if ($value === '') return null;
    $clean = str_replace(',', '', $value);
    if (ctype_digit($clean)) return (int) $clean;
    if (preg_match('/^-\d+$/', $clean)) return (int) $clean;
    if (is_numeric($clean) && str_contains($clean, '.')) return (float) $clean;
    if (strtolower($value) === 'true')  return true;
    if (strtolower($value) === 'false') return false;
    return $value;
}

/**
 * Send a batch of rows to Kafka.
 */
function sendBatch(Producer $producer, string $topic, array $batch, string $fileName): void
{
    foreach ($batch as $item) {
        // Sanitise values — convert booleans and nulls to strings to avoid TypeError
        $data = array_map(function($v) {
            if (is_bool($v)) return $v ? 'true' : 'false';
            if (is_null($v)) return '';
            return $v;
        }, $item['data']);

        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            echo "  [SKIP] json_encode failed for row {$item['row']}\n";
            continue;
        }

        $key = md5($fileName . ':' . $item['row'] . ':' . uniqid('', true));
        $producer->send($topic, $payload, $key);
    }
    echo "  Sent batch of " . count($batch) . "\n";
}