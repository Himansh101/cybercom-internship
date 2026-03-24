<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Builds full CSV exports from report payloads.
 */
class ReportExportService
{
    private QueryBuilderService $queryBuilder;
    private SolrService $solr;

    public function __construct()
    {
        $this->queryBuilder = new QueryBuilderService();
        $this->solr = new SolrService();
    }

    /**
     * @param array $payload
     * @return array{filename:string,csv:string,row_count:int}
     */
    public function buildCsv(array $payload): array
    {
        $tempPath = $this->buildCsvFile($payload);
        $csv = file_get_contents($tempPath['path']);
        @unlink($tempPath['path']);

        return [
            'filename' => $tempPath['filename'],
            'csv' => $csv === false ? '' : $csv,
            'row_count' => $tempPath['row_count'],
        ];
    }

    /**
     * @param array $payload
     * @return array{filename:string,path:string,row_count:int}
     */
    public function buildCsvFile(array $payload): array
    {
        $solrParams = $this->queryBuilder->build($payload);
        $solrParams['rows'] = 500;

        $requestedColumns = array_values(array_filter(
            array_map(
                fn(mixed $column): string => $this->sanitizeField((string) $column),
                is_array($payload['columns'] ?? null) ? $payload['columns'] : []
            ),
            fn(string $column): bool => $column !== ''
        ));

        $path = tempnam(sys_get_temp_dir(), 'scheduled-report-');
        if ($path === false) {
            throw new \RuntimeException('Unable to create temporary CSV file');
        }

        $stream = fopen($path, 'w+');
        if ($stream === false) {
            throw new \RuntimeException('Unable to open temporary CSV file');
        }
        fwrite($stream, "\xEF\xBB\xBF");

        $wroteHeader = false;
        $rowCount = 0;
        $cursor = '*';
        $columns = [];

        do {
            $result = $this->solr->queryCursor($solrParams, $cursor);
            $docs = $result['docs'];
            $nextCursor = $result['nextCursorMark'];

            if (!empty($docs)) {
                if (!$wroteHeader) {
                    $columns = array_keys($docs[0]);
                    fputcsv($stream, $columns);
                    $wroteHeader = true;
                }

                foreach ($docs as $doc) {
                    $row = [];
                    foreach ($columns as $column) {
                        $value = $doc[$column] ?? '';
                        $row[] = is_array($value) ? json_encode($value, JSON_UNESCAPED_SLASHES) : $value;
                    }
                    fputcsv($stream, $row);
                    $rowCount++;
                }
            }

            if ($nextCursor === $cursor || empty($docs)) {
                break;
            }
            $cursor = $nextCursor;
        } while (true);

        if (!$wroteHeader && $requestedColumns !== []) {
            fputcsv($stream, $requestedColumns);
        }

        fclose($stream);

        return [
            'filename' => 'report_export_' . date('Y-m-d_His') . '.csv',
            'path' => $path,
            'row_count' => $rowCount,
        ];
    }

    private function sanitizeField(string $field): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $field);
    }
}
