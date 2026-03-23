<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use Firebase\JWT\JWT;

/**
 * Handles user authentication and JWT issuance.
 */
class AuthController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * POST /api/auth/login
     * Validates credentials and returns a signed JWT.
     */
    public function login(): void
    {
        $body  = json_decode(file_get_contents('php://input'), true) ?? [];
        $email = trim($body['email'] ?? '');
        $pass  = $body['password'] ?? '';

        if ($email === '' || $pass === '') {
            http_response_code(400);
            $this->json(false, null, 'Email and password are required');
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if ($user === null || !password_verify($pass, $user['password_hash'])) {
            http_response_code(401);
            $this->json(false, null, 'Invalid credentials');
            return;
        }

        $secret  = $_ENV['JWT_SECRET']  ?? 'changeme';
        $expiry  = (int) ($_ENV['JWT_EXPIRY'] ?? 3600);
        $issuedAt = time();

        $payload = [
            'iss'  => 'reporting-system',
            'iat'  => $issuedAt,
            'exp'  => $issuedAt + $expiry,
            'sub'  => $user['id'],
            'role' => $user['role'],
            'name' => $user['name'],
        ];

        $token = JWT::encode($payload, $secret, 'HS256');

        $this->json(true, [
            'token'      => $token,
            'expires_in' => $expiry,
            'user'       => [
                'id'   => $user['id'],
                'name' => $user['name'],
                'role' => $user['role'],
            ],
        ]);
    }

    /**
     * Send a JSON response.
     */
    private function json(bool $success, mixed $data, ?string $error = null, array $meta = []): void
    {
        echo json_encode(['success' => $success, 'data' => $data, 'error' => $error, 'meta' => $meta]);
    }
}
