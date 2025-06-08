<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Entity\User;
use Exception;

/**
 * Class AuthService
 * Handles user authentication, registration, and JWT management.
 */
class AuthService
{
    private UserRepository $userRepository;
    private array $jwtConfig;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $config = require __DIR__ . '/../../config/config.php';
        $this->jwtConfig = $config['jwt'];
    }

    /**
     * Attempts to register a new user.
     *
     * @param string $username The desired username.
     * @param string $email The user's email address.
     * @param string $password The user's plain text password.
     * @return array ['success' => bool, 'message' => string, 'userId' => ?int]
     */
    public function register(string $username, string $email, string $password): array
    {
        // Validação de campos obrigatórios
        if (empty($username) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Username, email, and password are required.'];
        }

        // Validação do formato do e-mail
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format.'];
        }

        // Validação do nome de usuário
        if (strlen($username) < 3) {
            return ['success' => false, 'message' => 'Username must be at least 3 characters long.'];
        }
        if (strlen($username) > 50) {
            return ['success' => false, 'message' => 'Username cannot exceed 50 characters.'];
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return ['success' => false, 'message' => 'Username can only contain letters, numbers, and underscores.'];
        }

        // Validação da senha
        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
        }
        // Melhoria futura: Adicionar regras de complexidade de senha aqui (ex: maiúsculas, minúsculas, números, especiais)

        // Validação de unicidade
        if ($this->userRepository->findByEmail($email)) {
            return ['success' => false, 'message' => 'Email already in use.'];
        }
        if ($this->userRepository->findByUsername($username)) {
            return ['success' => false, 'message' => 'Username already taken.'];
        }
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        $userId = $this->userRepository->create($username, $email, $passwordHash);

        if ($userId) {
            return ['success' => true, 'message' => 'User registered successfully.', 'userId' => $userId];
        }
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }

    /**
     * Attempts to log in a user.
     *
     * @param string $email The user's email.
     * @param string $password The user's plain text password.
     * @return array ['success' => bool, 'message' => string, 'token' => ?string]
     */
    public function login(string $email, string $password): array
    {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required.'];
        }

        $user = $this->userRepository->findByEmail($email);

        if ($user && password_verify($password, $user->getPasswordHash())) {
            $token = $this->generateToken($user);
            return ['success' => true, 'message' => 'Login successful.', 'token' => $token];
        }

        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    /**
     * Generates a JWT for a given user.
     *
     * @param User $user The user object.
     * @return string The generated JWT.
     */
    private function generateToken(User $user): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + $this->jwtConfig['expiry_seconds'];
        $payload = [
            'iss' => $this->jwtConfig['issuer'],       // Issuer of the token
            'aud' => $this->jwtConfig['audience'],     // Audience of the token
            'iat' => $issuedAt,                        // Time the JWT was issued.
            'exp' => $expirationTime,                  // Expiration time
            'data' => [                                // Data related to the signer
                'userId' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail()
            ]
        ];

        return JWT::encode($payload, $this->jwtConfig['secret_key'], $this->jwtConfig['algorithm']);
    }

    /**
     * Validates a JWT and returns the decoded payload.
     *
     * @param string|null $token The JWT from the Authorization header.
     * @return object|null The decoded payload as an object, or null if invalid.
     */
    public function validateToken(?string $token): ?object
    {
        if (!$token) {
            return null;
        }

        // Remove "Bearer " prefix if present
        if (preg_match('/Bearer\s(\S+)/', $token, $matches)) {
            $token = $matches[1];
        }

        try {
            $decoded = JWT::decode($token, new Key($this->jwtConfig['secret_key'], $this->jwtConfig['algorithm']));
            // Check if token is expired (Firebase JWT library handles this, but an extra check can be added if needed)
            // if ($decoded->exp < time()) {
            //     return null; // Token expired
            // }
            return $decoded;
        } catch (Exception $e) {
            // Log error: $e->getMessage()
            return null;
        }
    }
}