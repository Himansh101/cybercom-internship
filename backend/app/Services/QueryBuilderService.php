<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Converts a frontend filter payload into Solr query parameters.
 *
 * Supports filter types: dropdown, range, text, date, boolean.
 * Supports AND/OR nested filter groups recursively.
 */
class QueryBuilderService
{
    /**
     * Build the complete Solr parameter array from the frontend payload.
     *
     * @param array $payload  Decoded JSON payload from the frontend.
     * @return array          Solr-ready query parameters.
     */
    public function build(array $payload): array
    {
        $params = [];

        // ── Main query (full-text search) ──────────────────────────────────
        $params['q'] = !empty($payload['q'])
            ? $this->escapeSpecialChars((string) $payload['q'])
            : '*:*';

        // ── Filter queries ─────────────────────────────────────────────────
        $fqList = [];
        if (!empty($payload['filters']) && is_array($payload['filters'])) {
            foreach ($payload['filters'] as $filterGroup) {
                $fq = $this->buildGroup($filterGroup);
                if ($fq !== '') {
                    $fqList[] = $fq;
                }
            }
        }
        if (!empty($fqList)) {
            $params['fq'] = $fqList;
        }

        // ── Field list (fl) ────────────────────────────────────────────────
        if (!empty($payload['columns']) && is_array($payload['columns'])) {
            $safeColumns = [];
            foreach ($payload['columns'] as $column) {
                $field = $this->sanitizeField((string) $column);
                if ($field !== '') {
                    $safeColumns[] = $field;
                }
            }
            $params['fl'] = !empty($safeColumns) ? implode(',', array_unique($safeColumns)) : '*';
        } else {
            $params['fl'] = '*';
        }


        // ── Sorting ────────────────────────────────────────────────────────
        if (!empty($payload['sort']['field'])) {
            $field     = $this->sanitizeField($payload['sort']['field']);
            $direction = strtolower($payload['sort']['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
            $params['sort'] = "{$field} {$direction}";
        } else {
            $params['sort'] = 'id asc';
        }

        // ── Pagination ─────────────────────────────────────────────────────
        $perPage      = max(1, min((int) ($payload['per_page'] ?? 50), 500));
        $page         = max(1, (int) ($payload['page'] ?? 1));
        $params['rows']  = $perPage;
        $params['start'] = ($page - 1) * $perPage;

        // ── Facets ─────────────────────────────────────────────────────────
        $params['facet'] = 'true';
        $params['facet.limit'] = 100;
        $params['facet.mincount'] = 1;

        $facetFields = ['category', 'sub_category', 'region', 'is_active'];
        if (!empty($payload['facet_fields']) && is_array($payload['facet_fields'])) {
            $facetFields = [];
            foreach ($payload['facet_fields'] as $facetField) {
                $field = $this->sanitizeField((string) $facetField);
                if ($field !== '') {
                    $facetFields[] = $field;
                }
            }
            $facetFields = array_values(array_unique($facetFields));
        }
        $params['facet.field'] = $facetFields;

        // ── Response format ────────────────────────────────────────────────
        $params['wt'] = 'json';

        return $params;
    }

    /**
     * Recursively build a filter query string from a group or rule.
     *
     * @param array $node  A filter group or rule.
     * @return string      Solr fq string, e.g. "(price:[100 TO 500] AND category:(chair OR table))"
     */
    private function buildGroup(array $node): string
    {
        // If the node is a GROUP, recurse into its rules
        if (isset($node['type']) && $node['type'] === 'group') {
            $operator = strtoupper($node['operator'] ?? 'AND');
            $operator = in_array($operator, ['AND', 'OR']) ? $operator : 'AND';

            $parts = [];
            foreach ($node['rules'] ?? [] as $rule) {
                $part = $this->buildGroup($rule);
                if ($part !== '') {
                    $parts[] = $part;
                }
            }

            if (empty($parts)) return '';
            if (count($parts) === 1) return $parts[0];

            return '(' . implode(" {$operator} ", $parts) . ')';
        }

        // Otherwise it is a leaf rule
        return $this->buildRule($node);
    }

    /**
     * Build a single Solr filter query clause from a rule object.
     *
     * @param array $rule  A single filter rule with type, field, value, etc.
     * @return string
     */
    private function buildRule(array $rule): string
    {
        $type  = $rule['type']  ?? '';
        $field = $this->sanitizeField($rule['field'] ?? '');

        if ($field === '') return '';

        return match ($type) {
            'dropdown' => $this->buildDropdown($field, $rule['value'] ?? []),
            'range'    => $this->buildRange($field, $rule['from'] ?? null, $rule['to'] ?? null),
            'text'     => $this->buildText($field, $rule['value'] ?? ''),
            'date'     => $this->buildDate($field, $rule['from'] ?? null, $rule['to'] ?? null),
            'boolean'  => $this->buildBoolean($field, $rule['value'] ?? null),
            default    => '',
        };
    }

    /**
     * Build a multi-value dropdown filter: field:(val1 OR val2 OR val3)
     *
     * @param string $field
     * @param array  $values
     * @return string
     */
    private function buildDropdown(string $field, array $values): string
    {
        if (empty($values)) return '';

        $escaped = array_map(
            fn($v) => '"' . addslashes((string) $v) . '"',
            $values
        );

        return "{$field}:(" . implode(' OR ', $escaped) . ')';
    }

    /**
     * Build a numeric range filter: field:[from TO to]
     *
     * @param string     $field
     * @param mixed|null $from
     * @param mixed|null $to
     * @return string
     */
    private function buildRange(string $field, mixed $from, mixed $to): string
    {
        $lower = ($from !== null && $from !== '') ? (float) $from : '*';
        $upper = ($to   !== null && $to   !== '') ? (float) $to   : '*';

        if ($lower === '*' && $upper === '*') return '';

        return "{$field}:[{$lower} TO {$upper}]";
    }

    /**
     * Build a wildcard text search filter: field:*value*
     *
     * @param string $field
     * @param string $value
     * @return string
     */
    private function buildText(string $field, string $value): string
    {
        $value = trim($value);
        if ($value === '') return '';
        $escaped = $this->escapeSpecialChars($value);
        return "{$field}:*{$escaped}*";
    }

    /**
     * Build a date range filter: field:[2024-01-01T00:00:00Z TO 2024-12-31T23:59:59Z]
     *
     * @param string      $field
     * @param string|null $from  ISO date string (Y-m-d or full datetime)
     * @param string|null $to    ISO date string
     * @return string
     */
    private function buildDate(string $field, ?string $from, ?string $to): string
    {
        $lower = $this->formatSolrDate($from, '00:00:00');
        $upper = $this->formatSolrDate($to,   '23:59:59');

        if ($lower === null && $upper === null) return '';

        $lowerStr = $lower ?? '*';
        $upperStr = $upper ?? '*';

        return "{$field}:[{$lowerStr} TO {$upperStr}]";
    }

    /**
     * Build a boolean filter: field:true / field:false
     *
     * @param string $field
     * @param mixed  $value
     * @return string
     */
    private function buildBoolean(string $field, mixed $value): string
    {
        if ($value === null || $value === '') return '';
        $boolStr = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        return "{$field}:{$boolStr}";
    }

    /**
     * Formats a date string to Solr ISO 8601 format: 2024-01-01T00:00:00Z
     *
     * @param string|null $date       Input date string
     * @param string      $timeOfDay  Default time if only a date is given
     * @return string|null
     */
    private function formatSolrDate(?string $date, string $timeOfDay): ?string
    {
        if ($date === null || $date === '') return null;

        // If it already looks like a full datetime
        if (preg_match('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $date)) {
            return rtrim($date, 'Z') . 'Z';
        }

        // Plain date: append time
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return "{$date}T{$timeOfDay}Z";
        }

        return null;
    }

    /**
     * Strip/sanitize field names to prevent query injection.
     *
     * @param string $field
     * @return string
     */
    private function sanitizeField(string $field): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $field);
    }

    /**
     * Escape Solr special characters in user-provided string values.
     *
     * @param string $value
     * @return string
     */
    private function escapeSpecialChars(string $value): string
    {
        $chars = [
            '\\',
            '+',
            '-',
            '&&',
            '||',
            '!',
            '(',
            ')',
            '{',
            '}',
            '[',
            ']',
            '^',
            '"',
            '~',
            '?',
            ':',
            '/'
        ];
        foreach ($chars as $char) {
            $value = str_replace($char, '\\' . $char, $value);
        }
        return $value;
    }
}
