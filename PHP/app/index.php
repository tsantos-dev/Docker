<?php
declare(strict_types=1);

// Linhas de debug:
var_dump($_SERVER['REQUEST_URI']); // Descomente para ver a saída no Postman/navegador
error_log("ROUTER DEBUG: app/index.php reached. REQUEST_URI is: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET'));
// exit("Debug: Reached app/index.php. URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET')); // Descomente para parar a execução aqui e ver a saída



// Carrega o bootstrap da aplicação (autoloader, .env, handlers de erro)
require_once __DIR__ . '/bootstrap.php';

use App\Controller\ApiController;
use App\Utils\ApiResponse;

// Configura cabeçalhos CORS (ajuste para produção)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Permite métodos comuns
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Lida com requisições OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ApiResponse::json(200, ['message' => 'CORS preflight successful']);
}

// Obtém a URI da requisição e o método HTTP
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove o prefixo '/app' se estiver presente (depende da sua configuração do Apache/DocumentRoot)
// Se o DocumentRoot do Apache aponta diretamente para /var/www/html/app, a URI já começa com /api/...
// Se o DocumentRoot aponta para /var/www/html e você acessa via /app/..., pode precisar desta linha:
// $requestUri = str_replace('/app', '', $requestUri);

// Roteamento simples
$controller = new ApiController();

switch ($requestUri) {
    case '/api/register':
        ($requestMethod === 'POST') ? $controller->register() : ApiResponse::json(405, ['message' => 'Method Not Allowed']);
        break;
    case '/api/login':
        ($requestMethod === 'POST') ? $controller->login() : ApiResponse::json(405, ['message' => 'Method Not Allowed']);
        break;
    case '/api/profile':
        ($requestMethod === 'GET') ? $controller->profile() : ApiResponse::json(405, ['message' => 'Method Not Allowed']);
        break;
    default:
        ApiResponse::json(404, ['message' => 'Not Found']);
}