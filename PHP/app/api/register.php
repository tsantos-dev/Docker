<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Service\AuthService;
use App\Repository\UserRepository;
use App\Utils\ApiResponse;

header("Access-Control-Allow-Origin: *"); // Permite todas as origens (ajuste para produção)
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::json(405, ['message' => 'Method Not Allowed']);
}

$inputData = json_decode(file_get_contents("php://input"), true);

$username = $inputData['username'] ?? '';
$email = $inputData['email'] ?? '';
$password = $inputData['password'] ?? '';
// A validação de campos vazios e outras regras agora é tratada pelo AuthService
 
$userRepository = new UserRepository();
$authService = new AuthService($userRepository);

$result = $authService->register($username, $email, $password);

$statusCode = $result['success'] ? 201 : 400;
ApiResponse::json($statusCode, $result);