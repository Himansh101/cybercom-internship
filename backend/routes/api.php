<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\IngestionController;
use App\Controllers\ReportController;
use App\Controllers\ScheduledReportController;
use App\Controllers\FilterController;
use App\Controllers\SavedViewController;
use App\Middleware\AuthMiddleware;

/**
 * Simple router: match HTTP method + URI pattern, dispatch to controller.
 * Supports {param} placeholders in routes.
 */

/**
 * Match a URI against a pattern and extract named params.
 *
 * @param string $pattern  e.g. '/saved-views/{id}'
 * @param string $uri      e.g. '/saved-views/5'
 * @param array  $params   out – extracted params
 */
function matchRoute(string $pattern, string $uri, array &$params = []): bool
{
    $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
    $regex = '#^' . $regex . '$#';
    if (preg_match($regex, $uri, $matches)) {
        foreach ($matches as $k => $v) {
            if (is_string($k)) {
                $params[$k] = $v;
            }
        }
        return true;
    }
    return false;
}

/**
 * Send a JSON 404 response.
 */
function notFound(): void
{
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'data' => null, 'error' => 'Route not found', 'meta' => []]);
    exit;
}

/**
 * Send a JSON 405 response.
 */
function methodNotAllowed(): void
{
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'data' => null, 'error' => 'Method not allowed', 'meta' => []]);
    exit;
}

$auth        = new AuthMiddleware();
$reports     = new ReportController();
$filters     = new FilterController();
$savedViews  = new SavedViewController();
$authCtrl    = new AuthController();
$ingestion   = new IngestionController();
$scheduledReports = new ScheduledReportController();

$params = [];

// ─── Public routes ───────────────────────────────────────────────────────────

// POST /auth/login
if ($method === 'POST' && matchRoute('/auth/login', $uri)) {
    $authCtrl->login();
    exit;
}

// GET /schema
if ($method === 'GET' && matchRoute('/schema', $uri)) {
    $reports->schema();
    exit;
}

// ─── Protected routes ────────────────────────────────────────────────────────

// POST /reports/query
if (matchRoute('/reports/query', $uri)) {
    if ($method !== 'POST') { methodNotAllowed(); }
    // $auth->handle();
    $reports->query();
    exit;
}

// POST /reports/chart
if (matchRoute('/reports/chart', $uri)) {
    if ($method !== 'POST') { methodNotAllowed(); }
    // $auth->handle();
    $reports->chart();
    exit;
}

// GET /reports/export
if (matchRoute('/reports/export', $uri)) {
    if ($method !== 'GET') { methodNotAllowed(); }
    // $auth->handle();
    $reports->export();
    exit;
}

// GET /facets/{field}
if (matchRoute('/facets/{field}', $uri, $params)) {
    if ($method !== 'GET') { methodNotAllowed(); }
    // $auth->handle();
    $filters->facets($params['field']);
    exit;
}

// POST /ingestion/upload
if (matchRoute('/ingestion/upload', $uri)) {
    if ($method !== 'POST') { methodNotAllowed(); }
    $auth->handle();
    $ingestion->uploadCsv();
    exit;
}

// GET /saved-views
if (matchRoute('/saved-views', $uri) && $method === 'GET') {
    $auth->handle();
    $savedViews->index();
    exit;
}

// POST /saved-views
if (matchRoute('/saved-views', $uri) && $method === 'POST') {
    $auth->handle();
    $savedViews->store();
    exit;
}

// PUT /saved-views/{id}
if (matchRoute('/saved-views/{id}', $uri, $params) && $method === 'PUT') {
    $auth->handle();
    $savedViews->update((int) $params['id']);
    exit;
}

// DELETE /saved-views/{id}
if (matchRoute('/saved-views/{id}', $uri, $params) && $method === 'DELETE') {
    $auth->handle();
    $savedViews->destroy((int) $params['id']);
    exit;
}

// GET /scheduled-reports
if (matchRoute('/scheduled-reports', $uri) && $method === 'GET') {
    $auth->handle();
    $scheduledReports->index();
    exit;
}

// POST /scheduled-reports
if (matchRoute('/scheduled-reports', $uri) && $method === 'POST') {
    $auth->handle();
    $scheduledReports->store();
    exit;
}

// DELETE /scheduled-reports/{id}
if (matchRoute('/scheduled-reports/{id}', $uri, $params) && $method === 'DELETE') {
    $auth->handle();
    $scheduledReports->destroy((int) $params['id']);
    exit;
}

notFound();
