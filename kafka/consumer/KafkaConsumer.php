<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/SolrIndexer.php';

use Dotenv\Dotenv;

/**
 * Kafka consumer that reads report_data_topic and indexes records into Solr.
 * Supports both single-record and chunked-array message payloads.
 */
final class KafkaConsumerRunner
{
    private \RdKafka\KafkaConsumer $consumer;
    private \RdKafka\ProducerTopic $dlqTopic;
    private \RdKafka\Producer $producer;
    private SolrIndexer $indexer;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->safeLoad();

        $broker = $_ENV['KAFKA_BROKER'] ?? 'kafka:9092';
        $topic = $_ENV['KAFKA_TOPIC'] ?? 'report_data_topic';
        $group = $_ENV['KAFKA_CONSUMER_GROUP'] ?? 'report-consumer-group';
        $dlq = $_ENV['KAFKA_DLQ_TOPIC'] ?? 'report_data_dlq';

        $consumerConf = new \RdKafka\Conf();
        $consumerConf->set('group.id', $group);
        $consumerConf->set('metadata.broker.list', $broker);
        $consumerConf->set('auto.offset.reset', 'earliest');
        $consumerConf->set('enable.auto.commit', 'false');

        $this->consumer = new \RdKafka\KafkaConsumer($consumerConf);
        $this->consumer->subscribe([$topic]);

        $producerConf = new \RdKafka\Conf();
        $producerConf->set('metadata.broker.list', $broker);
        $this->producer = new \RdKafka\Producer($producerConf);
        $this->dlqTopic = $this->producer->newTopic($dlq);

        $this->indexer = new SolrIndexer();
    }

    /**
     * Runs infinite consume/index/commit loop.
     */
    public function run(): void
    {
        echo "KafkaConsumer started\n";

        while (true) {
            $message = $this->consumer->consume(1000);

            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $this->handleMessage($message);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    break;
                default:
                    fwrite(STDERR, "Kafka error: {$message->errstr()}\n");
                    usleep(200000);
                    break;
            }
        }
    }

    /**
     * Handles one Kafka message, indexes records, commits offset on success.
     */
    private function handleMessage(\RdKafka\Message $message): void
    {
        $decoded = json_decode((string) $message->payload, true);
        if ($decoded === null) {
            $this->sendToDlq(['payload' => $message->payload], 'Invalid JSON payload');
            $this->consumer->commit($message);
            return;
        }

        $records = $this->normalizeRecords($decoded);
        if ($records === []) {
            $this->consumer->commit($message);
            return;
        }

        try {
            $result = $this->indexer->index($records);
            if (($result['errors'] ?? 0) > 0) {
                $this->sendToDlq($records, 'Partial/failed Solr indexing');
            }
            $this->consumer->commit($message);
        } catch (\Throwable $e) {
            $this->sendToDlq($records, 'Indexing exception: ' . $e->getMessage());
        }
    }

    /**
     * Normalizes payload into list of Solr documents.
     */
    private function normalizeRecords(array $decoded): array
    {
        $records = [];
        if (array_is_list($decoded)) {
            foreach ($decoded as $item) {
                if (is_array($item)) {
                    $records[] = $this->normalizeRecord($item);
                }
            }
            return array_values(array_filter($records, static fn (array $r): bool => isset($r['id'])));
        }

        $record = $this->normalizeRecord($decoded);
        return isset($record['id']) ? [$record] : [];
    }

    /**
     * Normalizes field values to match the Solr schema.
     */
    private function normalizeRecord(array $record): array
    {
        $normalized = $record;

        if (!empty($normalized['id'])) {
            $normalized['id'] = (string) $normalized['id'];
        }
        if (isset($normalized['price'])) {
            $normalized['price'] = (float) $normalized['price'];
        }
        if (isset($normalized['quantity'])) {
            $normalized['quantity'] = (int) $normalized['quantity'];
        }
        if (isset($normalized['margin'])) {
            $normalized['margin'] = (float) $normalized['margin'];
        }
        if (isset($normalized['is_active'])) {
            $normalized['is_active'] = filter_var($normalized['is_active'], FILTER_VALIDATE_BOOLEAN);
        }
        if (!empty($normalized['created_at'])) {
            $ts = strtotime((string) $normalized['created_at']);
            if ($ts !== false) {
                $normalized['created_at'] = gmdate('Y-m-d\TH:i:s\Z', $ts);
            }
        }

        return $normalized;
    }

    /**
     * Sends message payload to DLQ topic.
     */
    private function sendToDlq(array $payload, string $reason): void
    {
        $body = json_encode([
            'reason' => $reason,
            'payload' => $payload,
            'timestamp' => time(),
        ]);

        $this->dlqTopic->produce(RD_KAFKA_PARTITION_UA, 0, (string) $body);
        $this->producer->poll(0);
        $this->producer->flush(1000);
    }
}

(new KafkaConsumerRunner())->run();

