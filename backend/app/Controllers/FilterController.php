<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\SolrService;

/**
 * Handles facet/autocomplete data for dropdown filters.
 */
class FilterController
{
    private SolrService $solr;

    public function __construct()
    {
        $this->solr = new SolrService();
    }

    /**
     * GET /api/facets/{field}
     * Returns distinct values and counts for a Solr field (for dropdown filters).
     *
     * @param string $field  Solr field name.
     */
    public function facets(string $field): void
    {
        // Whitelist allowed facet fields to prevent abuse
        $allowed = ['category', 'sub_category', 'region', 'is_active', 'source_file_s', 'type_s', 'brand_name_s', 'stock_s'];

        if (!in_array($field, $allowed, true)) {
            http_response_code(400);
            $this->json(false, null, "Faceting not supported for field: {$field}");
            return;
        }

        $facets = $this->solr->getFacets($field);

        $this->json(true, $facets, null, ['field' => $field, 'count' => count($facets)]);
    }

    private function json(bool $success, mixed $data, ?string $error = null, array $meta = []): void
    {
        echo json_encode(['success' => $success, 'data' => $data, 'error' => $error, 'meta' => $meta]);
    }
}
