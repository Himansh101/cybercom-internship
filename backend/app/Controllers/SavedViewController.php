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
        $views  = $this->model->findAllVisible();
        $this->json(true, $views, null, ['count' => count($views)]);
    }

    /**
     * POST /api/saved-views
     * Saves a new view configuration for the authenticated user.
     */
    public function store(): void
    {
        if (!$this->canManageViews()) {
            http_response_code(403);
            $this->json(false, null, 'Viewers can only apply saved views');
            return;
        }

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
            'column_order'  => $body['column_order']  ?? ($body['columns'] ?? []),
            'search_query'  => $body['search_query']  ?? '',
            'filters'       => $body['filters']       ?? [],
            'filter_groups_operator' => $body['filter_groups_operator'] ?? 'AND',
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
        if (!$this->canManageViews()) {
            http_response_code(403);
            $this->json(false, null, 'Viewers can only apply saved views');
            return;
        }

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

        $existingConfig = json_decode($view['config'], true) ?? [];
        $config = [
            'columns'       => $body['columns']       ?? ($existingConfig['columns'] ?? []),
            'column_order'  => $body['column_order']  ?? ($existingConfig['column_order'] ?? ($body['columns'] ?? ($existingConfig['columns'] ?? []))),
            'search_query'  => $body['search_query']  ?? ($existingConfig['search_query'] ?? ''),
            'filters'       => $body['filters']       ?? ($existingConfig['filters'] ?? []),
            'filter_groups_operator' => $body['filter_groups_operator'] ?? ($existingConfig['filter_groups_operator'] ?? 'AND'),
            'sorting'       => $body['sorting']       ?? ($existingConfig['sorting'] ?? []),
            'column_widths' => $body['column_widths'] ?? ($existingConfig['column_widths'] ?? []),
            'date_range'    => $body['date_range']    ?? ($existingConfig['date_range'] ?? []),
            'compare_mode'  => $body['compare_mode']  ?? ($existingConfig['compare_mode'] ?? null),
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
        if (!$this->canManageViews()) {
            http_response_code(403);
            $this->json(false, null, 'Viewers can only apply saved views');
            return;
        }

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

    private function canManageViews(): bool
    {
        return (string) ($_REQUEST['auth_user_role'] ?? 'viewer') === 'admin';
    }
}
