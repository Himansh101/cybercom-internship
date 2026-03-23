<?php

declare(strict_types=1);

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Exception;

/**
 * JWT Authentication Middleware.
 * Validates Bearer tokens and attaches decoded user context to $_REQUEST.
 */
class AuthMiddleware
{
    /**
     * Validate the Authorization header and decode the JWT.
     * Terminates with 401 on failure.
     */
    // public function handle(): void
    // {
    //     $authHeader = $_SERVER['HTTP_AUTHORIZATION']
    //         ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
    //         ?? '';

    //     if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
    //         $this->unauthorized('Missing or malformed Authorization header');
    //     }

    //     $token  = substr($authHeader, 7);
    //     $secret = $_ENV['JWT_SECRET'] ?? 'changeme';

    //     try {
    //         $decoded = JWT::decode($token, new Key($secret, 'HS256'));

    //         // Attach user info to the request superglobal for controllers
    //         $_REQUEST['auth_user_id']   = $decoded->sub  ?? null;
    //         $_REQUEST['auth_user_role'] = $decoded->role ?? 'viewer';
    //     } catch (ExpiredException) {
    //         $this->unauthorized('Token has expired');
    //     } catch (Exception $e) {
    //         $this->unauthorized('Invalid token: ' . $e->getMessage());
    //     }
    // }

    public function handle(): void
    {
        // Try multiple ways Apache might pass the Authorization header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? $_SERVER['HTTP_X_AUTHORIZATION']
            ?? '';

        // Apache with CGI/FastCGI sometimes puts it here
        if (empty($authHeader) && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }

        // Last resort: check all server vars
        if (empty($authHeader)) {
            foreach ($_SERVER as $key => $value) {
                if (str_ends_with(strtolower($key), 'authorization')) {
                    $authHeader = $value;
                    break;
                }
            }
        }

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            $this->unauthorized('Missing or malformed Authorization header');
        }

        $token  = substr($authHeader, 7);
        $secret = $_ENV['JWT_SECRET'] ?? 'changeme';

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            $_REQUEST['auth_user_id']   = $decoded->sub  ?? null;
            $_REQUEST['auth_user_role'] = $decoded->role ?? 'viewer';
        } catch (ExpiredException) {
            $this->unauthorized('Token has expired');
        } catch (Exception $e) {
            $this->unauthorized('Invalid token: ' . $e->getMessage());
        }
    }
    /**
     * Send a 401 JSON response and terminate.
     *
     * @param string $message
     */
    private function unauthorized(string $message): void
    {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'data'    => null,
            'error'   => $message,
            'meta'    => [],
        ]);
        exit;
    }
}
