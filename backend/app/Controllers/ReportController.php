<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\QueryBuilderService;
use App\Services\ReportExportService;
use App\Services\SolrService;

/**
 * Handles report schema lookup, querying, and CSV export.
 */
class ReportController
{
    private SolrService $solr;
    private QueryBuilderService $queryBuilder;
    private ReportExportService $reportExport;

    public function __construct()
    {
        $this->solr = new SolrService();
        $this->queryBuilder = new QueryBuilderService();
        $this->reportExport = new ReportExportService();
    }

    /**
     * Returns schema fields used by frontend filter/table builders.
     */
    public function schema(): void
    {
        $schema = $this->fetchSchemaFields();
        $this->json(true, $schema, null, ['count' => count($schema)]);
    }

    /**
     * Executes a Solr report query from request payload and returns paginated data.
     */
    public function query(): void
    {
        $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        if (!is_array($payload)) {
            http_response_code(400);
            $this->json(false, null, 'Invalid JSON payload');
            return;
        }

        $solrParams = $this->queryBuilder->build($payload);
        $result = $this->solr->query($solrParams);

        $perPage = max(1, (int) ($payload['per_page'] ?? 50));
        $page = max(1, (int) ($payload['page'] ?? 1));

        $compareData = null;
        if (!empty($payload['compare_mode'])) {
            $comparePayload = $this->buildComparePayload($payload);
            if ($comparePayload !== null) {
                $compareData = $this->solr->query($this->queryBuilder->build($comparePayload))['docs'];
            }
        }

        $this->json(true, [
            'data' => $result['docs'],
            'total' => $result['numFound'],
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil(($result['numFound'] ?: 0) / $perPage),
            'facets' => $result['facets'],
            'compare_data' => $compareData,
        ]);
    }

    /**
     * Executes a full-data chart aggregation query from request payload.
     */
    public function chart(): void
    {
        $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        if (!is_array($payload)) {
            http_response_code(400);
            $this->json(false, null, 'Invalid JSON payload');
            return;
        }

        $xField = $this->sanitizeField((string) ($payload['x_field'] ?? ''));
        $yField = $this->sanitizeField((string) ($payload['y_field'] ?? ''));
        if ($xField === '' || $yField === '') {
            http_response_code(400);
            $this->json(false, null, 'Chart fields are required');
            return;
        }

        $limit = max(1, min((int) ($payload['limit'] ?? 15), 100));
        $chartPayload = $payload;
        $chartPayload['columns'] = [$xField, $yField];
        unset($chartPayload['page'], $chartPayload['per_page'], $chartPayload['x_field'], $chartPayload['y_field'], $chartPayload['limit']);

        $solrParams = $this->queryBuilder->build($chartPayload);
        $aggregated = $this->solr->aggregateTerms($solrParams, $xField, $yField, $limit);

        $data = array_map(function (array $item): array {
            return [
                'name' => $this->normalizeChartCategory($item['name'] ?? ''),
                'value' => round((float) ($item['value'] ?? 0), 2),
            ];
        }, $aggregated['data']);

        $this->json(true, [
            'data' => $data,
            'matched_rows' => $aggregated['matched'],
            'group_count' => $aggregated['groups'],
        ]);
    }

    /**
     * Streams all matching records as CSV using cursorMark pagination.
     */
    public function export(): void
    {
        $payload = json_decode($_GET['payload'] ?? '{}', true) ?? [];
        if (!is_array($payload)) {
            http_response_code(400);
            header('Content-Type: application/json');
            $this->json(false, null, 'Invalid export payload');
            return;
        }
        $export = $this->reportExport->buildCsv($payload);
        $filename = $export['filename'];
        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        echo $export['csv'];
    }

    /**
     * Builds comparison period payload for previous period or same period last year.
     */
    private function buildComparePayload(array $payload): ?array
    {
        $mode = $payload['compare_mode'] ?? null;
        $start = $payload['date_range']['start'] ?? null;
        $end = $payload['date_range']['end'] ?? null;
        if (!$mode || !$start || !$end) {
            return null;
        }

        $startTs = strtotime((string) $start);
        $endTs = strtotime((string) $end);
        if ($startTs === false || $endTs === false || $endTs < $startTs) {
            return null;
        }

        $span = $endTs - $startTs;
        $compareStart = null;
        $compareEnd = null;

        if ($mode === 'previous_period') {
            $compareStart = date('Y-m-d', $startTs - $span - 86400);
            $compareEnd = date('Y-m-d', $startTs - 86400);
        } elseif ($mode === 'same_last_year') {
            $compareStart = date('Y-m-d', strtotime('-1 year', $startTs));
            $compareEnd = date('Y-m-d', strtotime('-1 year', $endTs));
        }

        if ($compareStart === null || $compareEnd === null) {
            return null;
        }

        $comparePayload = $payload;
        $comparePayload['date_range'] = ['start' => $compareStart, 'end' => $compareEnd];
        unset($comparePayload['compare_mode']);

        return $comparePayload;
    }

    /**
     * Reads schema fields from Solr and maps them to frontend-friendly field metadata.
     */
    private function fetchSchemaFields(): array
    {
        $host = $_ENV['SOLR_HOST'] ?? 'solr';
        $port = $_ENV['SOLR_PORT'] ?? '8983';
        $collection = $_ENV['SOLR_COLLECTION'] ?? 'report_data';
        $skip = ['_version_', '_root_', '_text_'];
        $blocked = [
            'name',
            'category',
            'sub_category',
            'subcategory',
            'created_at',
            'region',
            'is_active',
            'price',
            'quantity',
            'margin',
            'default_sku_s',
            'store_price_i',
            'store_sku_f',
            'store_sku_i',
        ];

        $lukeUrl = "http://{$host}:{$port}/solr/{$collection}/admin/luke?numTerms=0&wt=json";
        $ch = curl_init($lukeUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $lukeResponse = curl_exec($ch);
        $lukeErrno = curl_errno($ch);
        curl_close($ch);

        $schema = [];
        if ($lukeErrno === 0 && $lukeResponse !== false) {
            $lukeDecoded = json_decode($lukeResponse, true);
            $lukeFields = $lukeDecoded['fields'] ?? [];
            if (is_array($lukeFields)) {
                foreach ($lukeFields as $fieldName => $fieldMeta) {
                    if (!is_string($fieldName) || $fieldName === '' || in_array($fieldName, $skip, true) || in_array($fieldName, $blocked, true) || str_starts_with($fieldName, '_')) {
                        continue;
                    }

                    $type = is_array($fieldMeta) ? (string) ($fieldMeta['type'] ?? 'string') : 'string';
                    $type = $this->mapSolrType($type);
                    if ($type === 'string') {
                        $type = $this->inferTypeFromFieldName($fieldName);
                    }
                    $schema[] = [
                        'name' => $fieldName,
                        'type' => $type,
                        'label' => $this->buildLabel($fieldName),
                        'faceted' => $this->isFacetedField($fieldName),
                    ];
                }
            }
        }

        if ($schema === []) {
            return $this->fallbackSchema();
        }

        usort($schema, function (array $a, array $b): int {
            $priority = [
                'id',
                'product_id_i',
                'product_name_s',
                'brand_name_s',
                'type_s',
                'price_f',
                'map_price_f',
                'quantity_i',
                'store_price_f',
                'sku_s',
                'store_sku_s',
                'store_url_s',
                'source_file_s',
                'date_dt',
            ];

            $aIndex = array_search($a['name'], $priority, true);
            $bIndex = array_search($b['name'], $priority, true);
            $aRank = $aIndex === false ? 999 : $aIndex;
            $bRank = $bIndex === false ? 999 : $bIndex;

            if ($aRank !== $bRank) {
                return $aRank <=> $bRank;
            }

            return strcmp($a['name'], $b['name']);
        });

        return $schema;
    }

    /**
     * Infers frontend field type from Solr dynamic suffix.
     */
    private function inferTypeFromFieldName(string $name): string
    {
        if (str_ends_with($name, '_f')) {
            return 'pfloat';
        }
        if (str_ends_with($name, '_i') || str_ends_with($name, '_l')) {
            return 'pint';
        }
        if (str_ends_with($name, '_dt')) {
            return 'pdate';
        }
        if (str_ends_with($name, '_b')) {
            return 'boolean';
        }

        return 'string';
    }

    /**
     * Builds a cleaner label by removing Solr suffixes.
     */
    private function buildLabel(string $name): string
    {
        if ($name === 'source_file_s') {
            return 'Source File Name';
        }

        $label = preg_replace('/(_dt|_s|_i|_f|_b|_l)$/', '', $name);
        $label = str_replace('_', ' ', (string) $label);
        return ucwords(trim($label));
    }

    /**
     * Maps Solr field types into frontend filter control types.
     */
    private function mapSolrType(string $type): string
    {
        return match ($type) {
            'pint', 'int', 'plong', 'long' => 'pint',
            'pfloat', 'pdouble', 'float', 'double' => 'pfloat',
            'pdate', 'date' => 'pdate',
            'boolean', 'bool' => 'boolean',
            default => 'string',
        };
    }

    /**
     * Marks common low-cardinality fields as facetable.
     */
    private function isFacetedField(string $name): bool
    {
        return in_array($name, ['source_file_s', 'type_s', 'brand_name_s', 'stock_s'], true);
    }

    /**
     * Returns fallback schema when Solr schema API is unavailable.
     */
    private function fallbackSchema(): array
    {
        return [
            ['name' => 'id', 'type' => 'string', 'label' => 'Id', 'faceted' => false],
            ['name' => 'name', 'type' => 'string', 'label' => 'Name', 'faceted' => false],
            ['name' => 'category', 'type' => 'string', 'label' => 'Category', 'faceted' => true],
            ['name' => 'sub_category', 'type' => 'string', 'label' => 'Sub Category', 'faceted' => true],
            ['name' => 'price', 'type' => 'pfloat', 'label' => 'Price', 'faceted' => false],
            ['name' => 'quantity', 'type' => 'pint', 'label' => 'Quantity', 'faceted' => false],
            ['name' => 'margin', 'type' => 'pfloat', 'label' => 'Margin', 'faceted' => false],
            ['name' => 'region', 'type' => 'string', 'label' => 'Region', 'faceted' => true],
            ['name' => 'is_active', 'type' => 'boolean', 'label' => 'Is Active', 'faceted' => true],
            ['name' => 'source_file_s', 'type' => 'string', 'label' => 'Source File Name', 'faceted' => true],
            ['name' => 'created_at', 'type' => 'pdate', 'label' => 'Created At', 'faceted' => false],
        ];
    }

    private function sanitizeField(string $field): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $field);
    }

    private function normalizeChartCategory(mixed $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim((string) $value));
        return $normalized === null ? '' : $normalized;
    }

    /**
     * Sends standard API JSON response.
     */
    private function json(bool $success, mixed $data, ?string $error = null, array $meta = []): void
    {
        echo json_encode(['success' => $success, 'data' => $data, 'error' => $error, 'meta' => $meta]);
    }
}
