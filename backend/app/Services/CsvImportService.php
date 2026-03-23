<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Parses uploaded CSV files and indexes them into Solr.
 */
class CsvImportService
{
    private const BATCH_SIZE = 200;

    private SolrService $solr;

    public function __construct()
    {
        $this->solr = new SolrService();
    }

    /**
     * Import a CSV upload into Solr.
     *
     * @param array $file
     * @return array
     */
    public function import(array $file): array
    {
        $tmpPath = $file['tmp_name'] ?? '';
        $originalName = (string) ($file['name'] ?? 'uploaded.csv');

        if (!is_string($tmpPath) || $tmpPath === '' || !is_uploaded_file($tmpPath)) {
            throw new \RuntimeException('A valid CSV upload is required');
        }

        $handle = fopen($tmpPath, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Unable to read uploaded CSV file');
        }

        try {
            $headers = fgetcsv($handle);
            if (!is_array($headers) || $headers === []) {
                throw new \RuntimeException('CSV file is empty or missing a header row');
            }

            $sanitizedHeaders = $this->sanitizeHeaders($headers);
            $rows = [];

            while (($row = fgetcsv($handle)) !== false) {
                if ($this->rowIsEmpty($row)) {
                    continue;
                }

                $rows[] = $this->padRow($row, count($sanitizedHeaders));
            }
        } finally {
            fclose($handle);
        }

        if ($rows === []) {
            throw new \RuntimeException('CSV file contains no data rows');
        }

        $fieldMap = $this->detectFieldMap($sanitizedHeaders, $rows);
        $imported = 0;

        foreach (array_chunk($rows, self::BATCH_SIZE) as $batchIndex => $chunk) {
            $documents = [];
            foreach ($chunk as $rowOffset => $row) {
                $documents[] = $this->buildDocument(
                    $row,
                    $fieldMap,
                    $originalName,
                    $batchIndex * self::BATCH_SIZE + $rowOffset + 1
                );
            }

            if (!$this->solr->addDocuments($documents)) {
                throw new \RuntimeException('Failed to index CSV data into Solr');
            }

            $imported += count($documents);
        }

        return [
            'imported' => $imported,
            'columns' => array_values($fieldMap),
            'source_file' => $originalName,
        ];
    }

    /**
     * @param array<int, string> $headers
     * @return array<int, string>
     */
    private function sanitizeHeaders(array $headers): array
    {
        $result = [];
        $seen = [];

        foreach ($headers as $index => $header) {
            $base = strtolower(trim((string) $header));
            $base = preg_replace('/[^a-z0-9]+/', '_', $base) ?? '';
            $base = trim($base, '_');
            if ($base === '') {
                $base = 'column_' . ($index + 1);
            }

            $candidate = $base;
            $suffix = 2;
            while (isset($seen[$candidate])) {
                $candidate = $base . '_' . $suffix;
                $suffix++;
            }

            $seen[$candidate] = true;
            $result[] = $candidate;
        }

        return $result;
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, array<int, string|null>> $rows
     * @return array<int, string>
     */
    private function detectFieldMap(array $headers, array $rows): array
    {
        $fieldMap = [];

        foreach ($headers as $index => $header) {
            if ($header === 'id') {
                $fieldMap[$index] = 'id';
                continue;
            }

            if ($header === 'source_file' || $header === 'source_file_name') {
                $fieldMap[$index] = 'source_file_s';
                continue;
            }

            if (preg_match('/_(s|i|f|b|dt|l)$/', $header) === 1) {
                $fieldMap[$index] = $header;
                continue;
            }

            $values = array_column($rows, $index);
            $suffix = $this->detectSuffix($header, $values);
            $fieldMap[$index] = $header . $suffix;
        }

        return $fieldMap;
    }

    /**
     * @param array<int, string|null> $values
     */
    private function detectSuffix(string $header, array $values): string
    {
        $nonEmpty = array_values(array_filter($values, static fn($value) => trim((string) $value) !== ''));
        if ($nonEmpty === []) {
            return '_s';
        }

        $headerLooksDate = preg_match('/(^|_)(date|time|timestamp|created|updated|indexed)(_|$)/', $header) === 1;
        if ($headerLooksDate && $this->allMatch($nonEmpty, fn(string $value): bool => $this->looksLikeDate($value))) {
            return '_dt';
        }

        if ($this->allMatch($nonEmpty, fn(string $value): bool => $this->looksLikeBoolean($value))) {
            return '_b';
        }

        if ($this->allMatch($nonEmpty, fn(string $value): bool => preg_match('/^-?\d+$/', $value) === 1)) {
            return '_i';
        }

        if ($this->allMatch($nonEmpty, fn(string $value): bool => is_numeric($value))) {
            return '_f';
        }

        if ($this->allMatch($nonEmpty, fn(string $value): bool => $this->looksLikeDate($value))) {
            return '_dt';
        }

        return '_s';
    }

    /**
     * @param array<int, string|null> $row
     * @param array<int, string> $fieldMap
     * @return array<string, mixed>
     */
    private function buildDocument(array $row, array $fieldMap, string $sourceFile, int $rowNumber): array
    {
        $doc = [
            'id' => $this->buildDocumentId($sourceFile, $rowNumber),
            'source_file_s' => $sourceFile,
        ];

        foreach ($fieldMap as $index => $fieldName) {
            $value = trim((string) ($row[$index] ?? ''));
            if ($value === '') {
                continue;
            }

            if ($fieldName === 'source_file_s') {
                continue;
            }

            $doc[$fieldName] = $this->castValue($fieldName, $value);
        }

        return $doc;
    }

    private function buildDocumentId(string $sourceFile, int $rowNumber): string
    {
        $prefix = preg_replace('/[^a-z0-9]+/i', '-', pathinfo($sourceFile, PATHINFO_FILENAME)) ?? 'csv';
        $prefix = trim($prefix, '-');
        if ($prefix === '') {
            $prefix = 'csv';
        }

        return strtolower($prefix) . '-' . time() . '-' . $rowNumber;
    }

    private function castValue(string $fieldName, string $value): mixed
    {
        if (str_ends_with($fieldName, '_b')) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        if (str_ends_with($fieldName, '_i') || str_ends_with($fieldName, '_l')) {
            return (int) $value;
        }

        if (str_ends_with($fieldName, '_f')) {
            return (float) $value;
        }

        if (str_ends_with($fieldName, '_dt')) {
            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return gmdate('Y-m-d\TH:i:s\Z', $timestamp);
            }
        }

        return $value;
    }

    /**
     * @param array<int, string|null> $row
     * @return array<int, string|null>
     */
    private function padRow(array $row, int $length): array
    {
        return array_pad(array_slice($row, 0, $length), $length, null);
    }

    /**
     * @param array<int, string|null> $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function looksLikeBoolean(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['true', 'false', 'yes', 'no', '1', '0'], true);
    }

    private function looksLikeDate(string $value): bool
    {
        return strtotime($value) !== false;
    }

    /**
     * @param array<int, string> $values
     * @param callable $predicate
     */
    private function allMatch(array $values, callable $predicate): bool
    {
        foreach ($values as $value) {
            if (!$predicate((string) $value)) {
                return false;
            }
        }

        return true;
    }
}
