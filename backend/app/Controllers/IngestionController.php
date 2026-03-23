<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CsvImportService;

/**
 * Handles admin CSV uploads and Solr indexing.
 */
class IngestionController
{
    private CsvImportService $importer;

    public function __construct()
    {
        $this->importer = new CsvImportService();
    }

    /**
     * POST /api/ingestion/upload
     */
    public function uploadCsv(): void
    {
        $role = (string) ($_REQUEST['auth_user_role'] ?? 'viewer');
        if ($role !== 'admin') {
            http_response_code(403);
            $this->json(false, null, 'Only admins can upload CSV files');
            return;
        }

        $file = $_FILES['file'] ?? null;
        if (!is_array($file)) {
            http_response_code(400);
            $this->json(false, null, 'CSV file is required');
            return;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            http_response_code(400);
            $this->json(false, null, 'CSV upload failed');
            return;
        }

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            http_response_code(400);
            $this->json(false, null, 'Only .csv files are supported');
            return;
        }

        try {
            $result = $this->importer->import($file);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            $this->json(false, null, $e->getMessage());
            return;
        } catch (\Throwable $e) {
            http_response_code(500);
            $this->json(false, null, 'Unexpected import failure: ' . $e->getMessage());
            return;
        }

        $this->json(true, $result, null, ['message' => 'CSV indexed successfully']);
    }

    private function json(bool $success, mixed $data, ?string $error = null, array $meta = []): void
    {
        echo json_encode(['success' => $success, 'data' => $data, 'error' => $error, 'meta' => $meta]);
    }
}
