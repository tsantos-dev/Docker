<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * Class ApiResponse
 * Utility for sending standardized JSON API responses.
 */
class ApiResponse
{
    /**
     * Sends a JSON response.
     *
     * @param int $statusCode HTTP status code.
     * @param array<string, mixed> $data Data to be encoded as JSON.
     * @param array<string, string> $headers Additional headers.
     */
    public static function json(int $statusCode, array $data, array $headers = []): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        foreach ($headers as $key => $value) {
            header("{$key}: {$value}");
        }
        echo json_encode($data);
        exit;
    }
}