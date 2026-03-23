<?php

declare(strict_types=1);

namespace App\Config;

/**
 * Kafka configuration loaded from environment variables.
 */
class Kafka
{
    public string $broker;
    public string $topic;
    public string $dlqTopic;
    public string $consumerGroup;

    public function __construct()
    {
        $this->broker        = $_ENV['KAFKA_BROKER']         ?? 'kafka:9092';
        $this->topic         = $_ENV['KAFKA_TOPIC']          ?? 'report_data_topic';
        $this->dlqTopic      = $_ENV['KAFKA_DLQ_TOPIC']      ?? 'report_data_dlq';
        $this->consumerGroup = $_ENV['KAFKA_CONSUMER_GROUP'] ?? 'report-consumer-group';
    }
}
