<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SavedViewModel;

/**
 * CRUD controller for user-saved report views.
 */
class SavedViewController
{
    private SavedViewModel $model;

    public function __construct()
    {
        $this->model = new SavedViewModel();
    }

    /**
     * GET /api/saved-views
     * Returns all saved views for the authenticated user.
     */
    public function index(): void
    {
        $userId = (int) ($_REQUEST['auth_user_id'] ?? 0);
        $views  = $this->model->findByUser($userId);
        $this->json(true, $views, null, ['count' => count($views)]);
    }

    /**
     * POST /api/saved-views
     * Saves a new view configuration for the authenticated user.
     */
    public function store(): void
    {
        $userId = (int) ($_REQUEST['auth_user_id'] ?? 0);
        $body   = json_decode(file_get_contents('php://input'), true) ?? [];

        $name = trim($body['name'] ?? '');
        if ($name === '') {
            http_response_code(400);
            $this->json(false, null, 'View name is required');
            return;
        }

        $config = [
            'columns'       => $body['columns']       ?? [],
            'search_query'  => $body['search_query']  ?? '',
            'filters'       => $body['filters']       ?? [],
            'sorting'       => $body['sorting']       ?? [],
            'column_widths' => $body['column_widths'] ?? [],
            'date_range'    => $body['date_range']    ?? [],
            'compare_mode'  => $body['compare_mode']  ?? null,
        ];

        $isDefault = (bool) ($body['is_default'] ?? false);

        // If this is being set as default, unset all other defaults first
        if ($isDefault) {
            $this->model->clearDefaults($userId);
        }

        $id   = $this->model->create([
            'user_id'    => $userId,
            'report_id'  => $body['report_id'] ?? 'sales_report',
            'name'       => $name,
            'config'     => json_encode($config),
            'is_default' => $isDefault ? 1 : 0,
        ]);

        $view = $this->model->findById($id);
        http_response_code(201);
        $this->json(true, $view);
    }

    /**
     * PUT /api/saved-views/{id}
     * Updates an existing saved view.
     *
     * @param int $id  View ID.
     */
    public function update(int $id): void
    {
        $userId = (int) ($_REQUEST['auth_user_id'] ?? 0);
        $view   = $this->model->findById($id);

        if (!$view || (int) $view['user_id'] !== $userId) {
            http_response_code(404);
            $this->json(false, null, 'View not found');
            return;
        }

        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $isDefault = (bool) ($body['is_default'] ?? false);

        if ($isDefault) {
            $this->model->clearDefaults($userId);
        }

        $config = [
            'columns'       => $body['columns']       ?? json_decode($view['config'], true)['columns'] ?? [],
            'search_query'  => $body['search_query']  ?? json_decode($view['config'], true)['search_query'] ?? '',
            'filters'       => $body['filters']       ?? json_decode($view['config'], true)['filters'] ?? [],
            'sorting'       => $body['sorting']       ?? json_decode($view['config'], true)['sorting'] ?? [],
            'column_widths' => $body['column_widths'] ?? json_decode($view['config'], true)['column_widths'] ?? [],
            'date_range'    => $body['date_range']    ?? json_decode($view['config'], true)['date_range'] ?? [],
            'compare_mode'  => $body['compare_mode']  ?? json_decode($view['config'], true)['compare_mode'] ?? null,
        ];

        $this->model->update($id, [
            'name'       => trim($body['name'] ?? $view['name']),
            'config'     => json_encode($config),
            'is_default' => $isDefault ? 1 : 0,
        ]);

        $this->json(true, $this->model->findById($id));
    }

    /**
     * DELETE /api/saved-views/{id}
     * Deletes a saved view belonging to the authenticated user.
     *
     * @param int $id  View ID.
     */
    public function destroy(int $id): void
    {
        $userId = (int) ($_REQUEST['auth_user_id'] ?? 0);
        $view   = $this->model->findById($id);

        if (!$view || (int) $view['user_id'] !== $userId) {
            http_response_code(404);
            $this->json(false, null, 'View not found');
            return;
        }

        $this->model->delete($id);
        $this->json(true, ['deleted' => true]);
    }

    private function json(bool $success, mixed $data, ?string $error = null, array $meta = []): void
    {
        echo json_encode(['success' => $success, 'data' => $data, 'error' => $error, 'meta' => $meta]);
    }
}
