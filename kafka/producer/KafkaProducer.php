<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CsvParser.php';

use Dotenv\Dotenv;

// ── Load .env ────────────────────────────────────────────────────────────────
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

$broker    = $_ENV['KAFKA_BROKER']    ?? 'kafka:9092';
$topic     = $_ENV['KAFKA_TOPIC']     ?? 'report_data_topic';
$dlqTopic  = $_ENV['KAFKA_DLQ_TOPIC'] ?? 'report_data_dlq';

// ── CLI arg validation ───────────────────────────────────────────────────────
$opts = getopt('', ['file:']);
$file = $opts['file'] ?? $argv[1] ?? null;

if (!$file) {
    fwrite(STDERR, "Usage: php KafkaProducer.php --file=path/to/file.csv\n");
    exit(1);
}

// ── Kafka config ─────────────────────────────────────────────────────────────
$conf = new RdKafka\Conf();
$conf->set('metadata.broker.list', $broker);
$conf->set('socket.timeout.ms', '10000');
$conf->set('queue.buffering.max.ms', '1000');

$producer    = new RdKafka\Producer($conf);
$mainTopic   = $producer->newTopic($topic);
$dlq         = $producer->newTopic($dlqTopic);

// ── Stats ─────────────────────────────────────────────────────────────────────
$stats = ['sent' => 0, 'failed' => 0, 'invalid' => 0, 'chunks' => 0];

/**
 * Produce a message to Kafka with retry logic.
 * Retries up to 3 times with exponential backoff (1s, 2s, 4s).
 *
 * @param RdKafka\ProducerTopic $kafkaTopic
 * @param string                $message
 * @param RdKafka\Producer      $prod
 * @return bool
 */
function produceWithRetry(
    RdKafka\ProducerTopic $kafkaTopic,
    string $message,
    RdKafka\Producer $prod
): bool {
    $maxRetries = 3;
    $delay      = 1;

    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            $kafkaTopic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
            $prod->poll(0);
            return true;
        } catch (Exception $e) {
            fwrite(STDERR, "  Attempt {$attempt} failed: {$e->getMessage()}\n");
            if ($attempt < $maxRetries) {
                sleep($delay);
                $delay *= 2;
            }
        }
    }
    return false;
}

// ── Parse & produce ───────────────────────────────────────────────────────────
echo "Starting Kafka producer...\n";
echo "  Broker : {$broker}\n";
echo "  Topic  : {$topic}\n";
echo "  File   : {$file}\n\n";

$parser = new CsvParser($file);

// DLQ callback for invalid rows
$onInvalid = function (array $row, string $reason) use ($dlq, $producer, &$stats): void {
    $payload = json_encode(['row' => $row, 'error' => $reason, 'ts' => time()]);
    produceWithRetry($dlq, $payload, $producer);
    $stats['invalid']++;
    echo "  [DLQ] {$reason}\n";
};

foreach ($parser->chunks($onInvalid) as $chunk) {
    $stats['chunks']++;
    $payload = json_encode($chunk);
    echo "  [Chunk {$stats['chunks']}] Sending " . count($chunk) . " records...\n";

    if (produceWithRetry($mainTopic, $payload, $producer)) {
        $stats['sent'] += count($chunk);
    } else {
        $stats['failed'] += count($chunk);
        fwrite(STDERR, "  [ERROR] Failed to produce chunk {$stats['chunks']} after retries.\n");
    }
}

// Flush remaining messages from the queue
$producer->flush(30000);

// ── Summary ───────────────────────────────────────────────────────────────────
echo "\n=== Producer Summary ===\n";
echo "  Chunks sent : {$stats['chunks']}\n";
echo "  Records sent: {$stats['sent']}\n";
echo "  Invalid rows: {$stats['invalid']} (sent to DLQ)\n";
echo "  Failed      : {$stats['failed']}\n";
