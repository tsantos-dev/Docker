<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

try {
    // Carrega variáveis de ambiente do arquivo .env localizado na pasta PHP/ (um nível acima de app/)
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../'); // Aponta para a pasta PHP/
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    // Lidar com o erro se o .env não for encontrado, talvez logar ou sair.
    // Para um ambiente de desenvolvimento, pode ser aceitável continuar se as variáveis forem definidas de outra forma.
    error_log("Warning: .env file not found or not readable at " . __DIR__ . '/../' . ". Ensure it exists and is readable.");
}