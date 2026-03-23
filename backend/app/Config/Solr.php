<?php

declare(strict_types=1);

namespace App\Config;

/**
 * Solr connection configuration.
 * Reads host, port, and collection from environment variables.
 */
class Solr
{
    private string $host;
    private int    $port;
    private string $collection;

    public function __construct()
    {
        $this->host       = $_ENV['SOLR_HOST']       ?? 'solr';
        $this->port       = (int) ($_ENV['SOLR_PORT'] ?? 8983);
        $this->collection = $_ENV['SOLR_COLLECTION'] ?? 'report_data';
    }

    /**
     * Returns the base URL for all Solr API calls.
     */
    public function getBaseUrl(): string
    {
        return "http://{$this->host}:{$this->port}/solr/{$this->collection}";
    }

    /**
     * Returns the Solr select endpoint URL.
     */
    public function getSelectUrl(): string
    {
        return $this->getBaseUrl() . '/select';
    }

    /**
     * Returns the Solr update endpoint URL.
     */
    public function getUpdateUrl(): string
    {
        return $this->getBaseUrl() . '/update';
    }

    public function getHost(): string       { return $this->host; }
    public function getPort(): int          { return $this->port; }
    public function getCollection(): string { return $this->collection; }
}
