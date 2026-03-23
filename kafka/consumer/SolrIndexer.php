<?php

declare(strict_types=1);

/**
 * SolrIndexer merges and indexes records into Solr.
 * Records with the same id are merged so fields from multiple CSV files
 * are joined into one final Solr document.
 */
class SolrIndexer
{
    private string $solrUpdateUrl;
    private string $solrSelectUrl;

    public function __construct()
    {
        $host = $_ENV['SOLR_HOST'] ?? 'solr';
        $port = $_ENV['SOLR_PORT'] ?? '8983';
        $collection = $_ENV['SOLR_COLLECTION'] ?? 'report_data';
        $base = "http://{$host}:{$port}/solr/{$collection}";

        $this->solrUpdateUrl = $base . '/update?commitWithin=5000&wt=json';
        $this->solrSelectUrl = $base . '/select';
    }

    /**
     * Indexes records into Solr with merge-on-id behavior.
     *
     * @param array $records
     * @return array{indexed:int, merged:int, errors:int}
     */
    public function index(array $records): array
    {
        $stats = ['indexed' => 0, 'merged' => 0, 'errors' => 0];
        if ($records === []) {
            return $stats;
        }

        $ids = array_values(array_unique(array_map(
            static fn (array $r): string => (string) ($r['id'] ?? ''),
            $records
        )));
        $ids = array_values(array_filter($ids, static fn (string $id): bool => $id !== ''));
        if ($ids === []) {
            return $stats;
        }

        $existingDocs = $this->fetchExistingDocsById($ids);
        $toIndex = [];

        foreach ($records as $record) {
            $id = (string) ($record['id'] ?? '');
            if ($id === '') {
                continue;
            }

            if (isset($existingDocs[$id])) {
                $toIndex[] = $this->mergeDocs($existingDocs[$id], $record);
                $stats['merged']++;
            } else {
                $toIndex[] = $record;
            }
        }

        if ($toIndex === []) {
            return $stats;
        }

        $payload = json_encode($toIndex);
        $response = $this->httpPost($this->solrUpdateUrl, (string) $payload, 'application/json');

        if ($response === null) {
            $stats['errors'] += count($toIndex);
            return $stats;
        }

        $decoded = json_decode($response, true);
        if (($decoded['responseHeader']['status'] ?? -1) === 0) {
            $stats['indexed'] += count($toIndex);
        } else {
            $stats['errors'] += count($toIndex);
            fwrite(STDERR, 'Solr error: ' . json_encode($decoded) . PHP_EOL);
        }

        return $stats;
    }

    /**
     * Fetches existing docs for ids and maps them by id.
     *
     * @param string[] $ids
     * @return array<string,array>
     */
    private function fetchExistingDocsById(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $escaped = array_map(static fn (string $id): string => '"' . addslashes($id) . '"', $ids);
        $query = 'id:(' . implode(' OR ', $escaped) . ')';

        $params = http_build_query([
            'q' => $query,
            'fl' => '*',
            'rows' => count($ids),
            'wt' => 'json',
        ]);

        $response = $this->httpGet($this->solrSelectUrl . '?' . $params);
        if ($response === null) {
            return [];
        }

        $decoded = json_decode($response, true);
        $docs = $decoded['response']['docs'] ?? [];
        $byId = [];

        foreach ($docs as $doc) {
            if (is_array($doc) && !empty($doc['id'])) {
                $byId[(string) $doc['id']] = $doc;
            }
        }

        return $byId;
    }

    /**
     * Merges incoming doc fields into an existing doc.
     * Empty incoming values do not overwrite populated existing values.
     */
    private function mergeDocs(array $existing, array $incoming): array
    {
        $merged = $existing;

        foreach ($incoming as $field => $value) {
            if ($field === 'id') {
                $merged['id'] = (string) $value;
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $merged[$field] = $value;
        }

        return $merged;
    }

    /**
     * Performs an HTTP POST request.
     */
    private function httpPost(string $url, string $body, string $contentType): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => ["Content-Type: {$contentType}"],
        ]);
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        return $errno === 0 ? $response : null;
    }

    /**
     * Performs an HTTP GET request.
     */
    private function httpGet(string $url): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        return $errno === 0 ? $response : null;
    }
}

