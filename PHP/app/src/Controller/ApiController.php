<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Repository\UserRepository;
use App\Utils\ApiResponse;

/**
 * Class ApiController
 * Handles API endpoints related to authentication and user data.
 */
class ApiController
{
    private AuthService $authService;

    public function __construct()
    {
        // Instancia o UserRepository e o AuthService
        $userRepository = new UserRepository();
        $this->authService = new AuthService($userRepository);
    }

    /**
     * Handles user registration requests.
     * Expected Method: POST
     * Expected Body: JSON with 'username', 'email', 'password'
     */
    public function register(): void
    {
        $inputData = json_decode(file_get_contents("php://input"), true);

        $username = $inputData['username'] ?? '';
        $email = $inputData['email'] ?? '';
        $password = $inputData['password'] ?? '';

        $result = $this->authService->register($username, $email, $password);

        $statusCode = $result['success'] ? 201 : 400;
        ApiResponse::json($statusCode, $result);
    }

    /**
     * Handles user login requests.
     * Expected Method: POST
     * Expected Body: JSON with 'email', 'password'
     */
    public function login(): void
    {
        $inputData = json_decode(file_get_contents("php://input"), true);

        $email = $inputData['email'] ?? '';
        $password = $inputData['password'] ?? '';

        $result = $this->authService->login($email, $password);

        $statusCode = $result['success'] ? 200 : 401;
        ApiResponse::json($statusCode, $result);
    }

    /**
     * Handles user profile requests (protected endpoint).
     * Expected Method: GET
     * Expected Header: Authorization: Bearer <token>
     */
    public function profile(): void
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        $decodedToken = $this->authService->validateToken($authHeader);

        if ($decodedToken) {
            ApiResponse::json(200, ['message' => 'Access granted.', 'user_data' => $decodedToken->data]);
        } else {
            ApiResponse::json(401, ['message' => 'Access denied. Invalid or expired token.']);
        }
    }
}