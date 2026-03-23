<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\Solr;

/**
 * Service for all Solr interactions: querying, facets, aggregations.
 * Integrates Redis caching to avoid redundant Solr calls.
 */
class SolrService
{
    private Solr         $config;
    private RedisService $redis;

    public function __construct()
    {
        $this->config = new Solr();
        $this->redis  = new RedisService();
    }

    /**
     * Execute a Solr query. Checks Redis cache before hitting Solr.
     *
     * @param array $params  Solr query parameters.
     * @return array         ['docs' => [...], 'numFound' => int, 'facets' => [...]]
     */
    public function query(array $params): array
    {
        $cacheKey = 'solr:query:' . md5(json_encode($params));
        $cached   = $this->redis->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $url      = $this->config->getSelectUrl();
        $response = $this->httpGet($url, $params);

        if ($response === null) {
            return ['docs' => [], 'numFound' => 0, 'facets' => []];
        }

        $data = json_decode($response, true);

        $result = [
            'docs'     => $data['response']['docs']     ?? [],
            'numFound' => $data['response']['numFound']  ?? 0,
            'facets'   => $this->parseFacets($data['facet_counts'] ?? []),
        ];

        $this->redis->set($cacheKey, $result, 60);

        return $result;
    }

    /**
     * Retrieve facet value counts for a single Solr field.
     *
     * @param string $field  Solr field name.
     * @return array         [['value' => '...', 'count' => int], ...]
     */
    public function getFacets(string $field): array
    {
        $cacheKey = "solr:facets:{$field}";
        $cached   = $this->redis->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $params = [
            'q'             => '*:*',
            'rows'          => 0,
            'facet'         => 'true',
            'facet.field'   => $field,
            'facet.limit'   => 200,
            'facet.mincount'=> 1,
            'wt'            => 'json',
        ];

        $response = $this->httpGet($this->config->getSelectUrl(), $params);
        if ($response === null) return [];

        $data   = json_decode($response, true);
        $counts = $data['facet_counts']['facet_fields'][$field] ?? [];
        $result = [];

        // Solr returns alternating [value, count, value, count, ...]
        for ($i = 0; $i < count($counts) - 1; $i += 2) {
            $result[] = ['value' => $counts[$i], 'count' => $counts[$i + 1]];
        }

        $this->redis->set($cacheKey, $result, 120);

        return $result;
    }

    /**
     * Compute a numeric aggregation (sum, avg, count) for a Solr field.
     *
     * @param string $field     Solr field name.
     * @param string $function  Aggregation type: sum | avg | count.
     * @return float
     */
    public function getAggregations(string $field, string $function): float
    {
        $params = [
            'q'            => '*:*',
            'rows'         => 0,
            'stats'        => 'true',
            'stats.field'  => $field,
            'wt'           => 'json',
        ];

        $response = $this->httpGet($this->config->getSelectUrl(), $params);
        if ($response === null) return 0.0;

        $data  = json_decode($response, true);
        $stats = $data['stats']['stats_fields'][$field] ?? [];

        return match ($function) {
            'sum'   => (float) ($stats['sum']   ?? 0),
            'avg'   => (float) ($stats['mean']  ?? 0),
            'count' => (float) ($stats['count'] ?? 0),
            default => 0.0,
        };
    }

    /**
     * Execute a cursor-mark based pagination query for full dataset export.
     *
     * @param array  $params     Base Solr params (without cursorMark).
     * @param string $cursorMark Current cursor (start with '*').
     * @return array             ['docs' => [...], 'nextCursorMark' => '...']
     */
    public function queryCursor(array $params, string $cursorMark = '*'): array
    {
        $params['cursorMark'] = $cursorMark;
        $params['sort']       = $this->normalizeCursorSort($params['sort'] ?? 'id asc');
        unset($params['start']);

        $response = $this->httpGet($this->config->getSelectUrl(), $params);
        if ($response === null) return ['docs' => [], 'nextCursorMark' => $cursorMark];

        $data = json_decode($response, true);

        return [
            'docs'           => $data['response']['docs']  ?? [],
            'nextCursorMark' => $data['nextCursorMark']    ?? $cursorMark,
        ];
    }

    /**
     * Index documents into Solr using the JSON update API.
     *
     * @param array $documents
     * @return bool
     */
    public function addDocuments(array $documents): bool
    {
        if ($documents === []) {
            return true;
        }

        $response = $this->httpPostJson(
            $this->config->getUpdateUrl() . '/json/docs?commit=true',
            $documents
        );

        if ($response === null) {
            return false;
        }

        $this->redis->flushAll();

        return true;
    }

    /**
     * Aggregate a numeric field grouped by a term field using Solr JSON Facet API.
     *
     * @param array $params   Base Solr params including q/fq filters.
     * @param string $xField  Grouping field.
     * @param string $yField  Numeric field to sum.
     * @param int $limit      Max number of buckets.
     * @return array          ['data' => [...], 'matched' => int, 'groups' => int]
     */
    public function aggregateTerms(array $params, string $xField, string $yField, int $limit = 15): array
    {
        unset(
            $params['start'],
            $params['rows'],
            $params['fl'],
            $params['facet'],
            $params['facet.field'],
            $params['facet.limit'],
            $params['facet.mincount'],
            $params['sort']
        );

        $params['rows'] = 0;
        $params['json.facet'] = json_encode([
            'groups' => [
                'type' => 'terms',
                'field' => $xField,
                'limit' => $limit,
                'mincount' => 1,
                'sort' => 'metric desc',
                'facet' => [
                    'metric' => "sum({$yField})",
                ],
            ],
        ], JSON_UNESCAPED_SLASHES);

        $response = $this->httpGet($this->config->getSelectUrl(), $params);
        if ($response === null) {
            return ['data' => [], 'matched' => 0, 'groups' => 0];
        }

        $data = json_decode($response, true);
        $facets = $data['facets'] ?? [];
        $buckets = $facets['groups']['buckets'] ?? [];

        $result = array_map(static function (array $bucket): array {
            return [
                'name' => (string) ($bucket['val'] ?? ''),
                'value' => round((float) ($bucket['metric'] ?? 0), 2),
            ];
        }, $buckets);

        return [
            'data' => $result,
            'matched' => (int) ($facets['count'] ?? 0),
            'groups' => count($buckets),
        ];
    }

    /**
     * Perform an HTTP GET request to Solr with query params.
     *
     * @param string $url
     * @param array  $params
     * @return string|null  Raw JSON response or null on failure.
     */
    private function httpGet(string $url, array $params): ?string
    {
        // Handle arrays in params (e.g., fq, facet.field)
        $queryParts = [];
        foreach ($params as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $item) {
                    $queryParts[] = urlencode($k) . '=' . urlencode((string) $item);
                }
            } else {
                $queryParts[] = urlencode($k) . '=' . urlencode((string) $v);
            }
        }
        $queryString = implode('&', $queryParts);
        $fullUrl     = $url . '?' . $queryString;

        $ch = curl_init($fullUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);

        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        curl_close($ch);

        return $errno === 0 ? $response : null;
    }

    /**
     * Perform an HTTP POST request with a JSON body.
     *
     * @param string $url
     * @param mixed $payload
     * @return string|null
     */
    private function httpPostJson(string $url, mixed $payload): ?string
    {
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($body === false) {
            return null;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        return $errno === 0 ? $response : null;
    }

    private function normalizeCursorSort(string $sort): string
    {
        $sort = trim($sort);
        if ($sort === '') {
            return 'id asc';
        }

        if (preg_match('/(^|,)\s*id\s+(asc|desc)\s*$/i', $sort) === 1 || str_contains(strtolower($sort), ',id ')) {
            return $sort;
        }

        return $sort . ',id asc';
    }

    /**
     * Parse Solr's facet_counts structure into a cleaner format.
     *
     * @param array $facetCounts
     * @return array
     */
    private function parseFacets(array $facetCounts): array
    {
        $result = [];
        $fields = $facetCounts['facet_fields'] ?? [];

        foreach ($fields as $field => $counts) {
            $result[$field] = [];
            for ($i = 0; $i < count($counts) - 1; $i += 2) {
                $result[$field][] = ['value' => $counts[$i], 'count' => $counts[$i + 1]];
            }
        }

        return $result;
    }
}
