<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Utils/ApiResponse.php';

use Dotenv\Dotenv; 
use App\Utils\ApiResponse; // Adicionado para uso nos handlers
use ErrorException; // Adicionado para o error handler

try {
    // Carrega variáveis de ambiente do arquivo .env localizado na pasta PHP/ (um nível acima de app/)
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../'); // Aponta para a raiz
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    error_log("Warning: .env file not found or not readable at " . __DIR__ . '/../' . ". Ensure it exists and is readable.");
}

// Configurações de exibição e log de erros
// Em produção, display_errors deve ser '0'. Em desenvolvimento, pode ser '1'.
ini_set('display_errors', ($_ENV['APP_DEBUG'] ?? 'false') === 'true' ? '1' : '0'); // Adicionado ?? 'false' para segurança
ini_set('log_errors', '1');
// error_log($_ENV['LOG_PATH'] ?? '/var/log/php_errors.log'); // Defina um caminho de log se necessário, ou deixe o padrão do servidor

/**
 * Manipulador de exceções global.
 * Captura todas as exceções não tratadas.
 */
set_exception_handler(function (Throwable $exception) {
    // Log do erro detalhado no servidor
    error_log(
        "Uncaught Exception: " . $exception->getMessage() .
        " in " . $exception->getFile() . ":" . $exception->getLine() .
        "\nStack trace:\n" . $exception->getTraceAsString()
    );

    // Envia uma resposta JSON genérica para o cliente
    // Não exponha detalhes da exceção ao cliente em produção
    $statusCode = ($exception instanceof \App\Exception\ApiException) ? $exception->getCode() : 500;
    $message = ($statusCode >= 500 && ($_ENV['APP_ENV'] ?? 'production') !== 'development') // Adicionado ?? 'production'
        ? 'An unexpected server error occurred. Please try again later.'
        : $exception->getMessage();

    ApiResponse::json($statusCode ?: 500, ['success' => false, 'message' => $message]);
});

/**
 * Manipulador de erros global.
 * Converte erros PHP (warnings, notices) em ErrorException.
 */
set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        // Este código de erro não está incluído em error_reporting
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

/**
 * Manipulador de desligamento.
 * Captura erros fatais que não são pegos pelos outros manipuladores.
 */
register_shutdown_function(function () {
    $lastError = error_get_last();
    if ($lastError && in_array($lastError['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        error_log(
            "Fatal Error: " . $lastError['message'] .
            " in " . $lastError['file'] . ":" . $lastError['line']
        );
        // Tenta enviar uma resposta JSON, mas pode não funcionar se os headers já foram enviados.
        if (!headers_sent()) {
            $message = (($_ENV['APP_ENV'] ?? 'production') !== 'development') // Adicionado ?? 'production'
                ? 'A critical server error occurred.'
                : $lastError['message'];
            ApiResponse::json(500, ['success' => false, 'message' => $message]);
        }
    }
});