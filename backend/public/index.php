<?php

declare(strict_types=1);

// ─── Autoload & Bootstrap ───────────────────────────────────────────────────
require_once __DIR__ . '/../vendor/autoload.php';
// Fix Apache stripping Authorization header
if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $_SERVER['HTTP_AUTHORIZATION'] = $headers['Authorization'];
        }
    }
}

use Dotenv\Dotenv;
use App\Middleware\CorsMiddleware;

// Load .env from project root
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// ─── CORS ───────────────────────────────────────────────────────────────────
$cors = new CorsMiddleware();
$cors->handle();

// ─── Parse request ───────────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip /api prefix if present
$uri = preg_replace('#^/api#', '', $uri);
$uri = rtrim($uri, '/') ?: '/';

// ─── Dispatch ───────────────────────────────────────────────────────────────
require_once __DIR__ . '/../routes/api.php';
