<?php

declare(strict_types=1);

/**
 * CsvParser reads CSV files in chunks and converts each row to
 * Solr-ready dynamic fields (e.g. product_id_i, product_name_s).
 */
class CsvParser
{
    private const CHUNK_SIZE = 500;

    private string $filePath;
    private string $sourceFile;
    private array $headers = [];

    public function __construct(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("CSV file not found: {$filePath}");
        }
        $this->filePath = $filePath;
        $this->sourceFile = pathinfo($filePath, PATHINFO_FILENAME);
    }

    /**
     * Yields valid chunks.
     *
     * @param callable $onInvalid fn(array $row, string $reason): void
     */
    public function chunks(callable $onInvalid): \Generator
    {
        $file = new SplFileObject($this->filePath, 'r');
        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::SKIP_EMPTY |
            SplFileObject::READ_AHEAD
        );

        $lineNumber = 0;
        $chunk = [];

        foreach ($file as $row) {
            if ($row === [null] || $row === ['']) {
                continue;
            }

            if ($lineNumber === 0) {
                $this->headers = array_map(static fn ($h) => trim((string) $h), $row);
                $lineNumber++;
                continue;
            }

            if (count($row) !== count($this->headers)) {
                $onInvalid($row, "Malformed row at line {$lineNumber}");
                $lineNumber++;
                continue;
            }

            $raw = array_combine($this->headers, $row);
            if (!is_array($raw)) {
                $onInvalid($row, "Invalid row mapping at line {$lineNumber}");
                $lineNumber++;
                continue;
            }

            $doc = $this->toSolrDoc($raw, $lineNumber);
            $chunk[] = $doc;

            if (count($chunk) >= self::CHUNK_SIZE) {
                yield $chunk;
                $chunk = [];
            }

            $lineNumber++;
        }

        if ($chunk !== []) {
            yield $chunk;
        }
    }

    /**
     * Builds one Solr doc with dynamic suffix fields.
     */
    private function toSolrDoc(array $raw, int $lineNumber): array
    {
        $doc = [];
        $normalized = [];

        foreach ($raw as $header => $value) {
            $key = $this->normalizeKey((string) $header);
            if ($key === '') {
                continue;
            }
            $normalized[$key] = is_string($value) ? trim($value) : $value;
        }

        // Unique Solr id (required).
        // Keep one row per (source_file, product_id) so all retailer files are retained.
        $productId = (string) ($normalized['product_id'] ?? $normalized['id'] ?? '');
        if ($productId !== '') {
            $doc['id'] = $this->sourceFile . '-' . $productId;
        } else {
            $doc['id'] = 'row-' . $this->sourceFile . '-' . $lineNumber;
        }

        // Keep source filename in each row for traceability.
        $doc['source_file_s'] = $this->sourceFile;

        foreach ($normalized as $key => $value) {
            if ($key === 'id') {
                continue; // already set as unique id
            }

            if ($value === null || $value === '') {
                continue;
            }

            [$suffix, $castValue] = $this->detectTypeAndCast($key, $value);
            $doc[$key . $suffix] = $castValue;
        }

        // Provide generic aliases for common cross-file reporting fields.
        if (!empty($normalized['default_sku'])) {
            $doc['sku_s'] = trim((string) $normalized['default_sku']);
        } elseif (!empty($doc['store_sku_s'])) {
            $doc['sku_s'] = (string) $doc['store_sku_s'];
        }

        if (
            isset($doc['price_f'], $doc['map_price_f']) &&
            is_numeric($doc['price_f']) &&
            is_numeric($doc['map_price_f']) &&
            (float) $doc['map_price_f'] > 0
        ) {
            $price = (float) $doc['price_f'];
            $mapPrice = (float) $doc['map_price_f'];
            $doc['margin_f'] = round((($mapPrice - $price) / $mapPrice) * 100, 2);
        }

        return $doc;
    }

    /**
     * Normalizes CSV headers to snake_case.
     */
    private function normalizeKey(string $header): string
    {
        $raw = trim($header);

        // Normalize retailer-prefixed columns across files:
        // AF Name / AFA Name / AMZ Name   -> store_name
        // AF URL  / AFA URL  / AMZ URL    -> store_url
        // AF PRICE / AFA PRICE / ...      -> store_price
        // AF SKU / AFA SKU / ...          -> store_sku
        if (preg_match('/^[A-Z0-9]{2,6}\s+(Name|URL|PRICE|SKU)$/', $raw, $m) === 1) {
            return 'store_' . strtolower($m[1]);
        }

        $raw = strtolower($raw);
        $raw = (string) preg_replace('/[^a-z0-9]+/', '_', $raw);
        return trim($raw, '_');
    }

    /**
     * Returns [suffix, castValue] for Solr dynamic fields.
     */
    private function detectTypeAndCast(string $key, mixed $value): array
    {
        $str = trim((string) $value);

        // These fields should always remain strings even if they look numeric.
        if (
            str_contains($key, 'sku') ||
            str_contains($key, 'url') ||
            str_contains($key, 'name') ||
            str_contains($key, 'brand') ||
            str_contains($key, 'type') ||
            str_contains($key, 'stock')
        ) {
            return ['_s', $str];
        }

        if (in_array($key, ['date', 'created_at', 'updated_at'], true) || str_contains($key, 'date') || str_contains($key, 'time')) {
            $ts = strtotime($str);
            if ($ts !== false) {
                return ['_dt', gmdate('Y-m-d\TH:i:s\Z', $ts)];
            }
        }

        $num = str_replace([',', '$'], '', $str);
        if ($num !== '' && is_numeric($num)) {
            if (str_contains($num, '.')) {
                return ['_f', (float) $num];
            }
            return ['_i', (int) $num];
        }

        $lower = strtolower($str);
        if (in_array($lower, ['true', 'false', 'yes', 'no', 'y', 'n'], true)) {
            return ['_b', in_array($lower, ['true', 'yes', 'y'], true)];
        }

        return ['_s', $str];
    }
}
