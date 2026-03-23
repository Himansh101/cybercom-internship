<?php

declare(strict_types=1);

namespace App\Middleware;

/**
 * CORS Middleware — adds appropriate headers and handles preflight OPTIONS requests.
 * Allows all origins in development; restrict in production.
 */
class CorsMiddleware
{
    /**
     * Apply CORS headers and short-circuit OPTIONS preflight requests.
     */
    public function handle(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
