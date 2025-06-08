<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Service\AuthService;
use App\Repository\UserRepository;
use App\Utils\ApiResponse;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    ApiResponse::json(405, ['message' => 'Method Not Allowed']);
}

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

$userRepository = new UserRepository(); // Não é estritamente necessário aqui se apenas validando token
$authService = new AuthService($userRepository);

$decodedToken = $authService->validateToken($authHeader);

if ($decodedToken) {
    // Opcional: buscar dados atualizados do usuário se necessário
    // $user = $userRepository->findById($decodedToken->data->userId);
    ApiResponse::json(200, ['message' => 'Access granted.', 'user_data' => $decodedToken->data]);
} else {
    ApiResponse::json(401, ['message' => 'Access denied. Invalid or expired token.']);
}