<?php

declare(strict_types=1);

// O bootstrap.php já deve ter sido carregado pelo script de entrada (ex: api/login.php)
// Se este arquivo for incluído em um contexto onde o bootstrap não foi carregado,
// as variáveis de $_ENV podem não estar disponíveis.

return [
    'db' => [
        'host' => $_ENV['MYSQL_HOST'] ?? 'db',
        'port' => (int) ($_ENV['MYSQL_PORT'] ?? 3306),
        'dbname' => $_ENV['MYSQL_DATABASE'] ?? 'php_app_db',
        'user' => $_ENV['MYSQL_USER'] ?? 'php_app_user',
        'pass' => $_ENV['MYSQL_PASSWORD'] ?? 'supersecretpassword',
        'charset' => 'utf8mb4'
    ],
    'jwt' => [
        'secret_key' => $_ENV['JWT_SECRET'] ?? 'fallback_secret_key_change_this',
        'algorithm' => 'HS256',
        'issuer' => $_ENV['JWT_ISSUER'] ?? 'http://localhost',
        'audience' => $_ENV['JWT_AUDIENCE'] ?? 'http://localhost',
        'expiry_seconds' => (int) ($_ENV['JWT_EXPIRY_SECONDS'] ?? 3600),
    ]
];