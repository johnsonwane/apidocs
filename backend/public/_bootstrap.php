<?php

declare(strict_types=1);

require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/helpers.php';

$config = require __DIR__ . '/../config.php';
$cors = $config['cors'] ?? [];
$allowedOrigins = $cors['allowed_origins'] ?? ['*'];
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';

$allowOrigin = '*';
if ($requestOrigin !== '' && $allowedOrigins !== ['*']) {
    $allowOrigin = in_array($requestOrigin, $allowedOrigins, true) ? $requestOrigin : '';
}

if ($allowOrigin !== '') {
    header('Access-Control-Allow-Origin: ' . $allowOrigin);
    header('Vary: Origin');
}
header('Access-Control-Allow-Headers: ' . ($cors['allowed_headers'] ?? 'Content-Type, Authorization, X-Requested-With'));
header('Access-Control-Allow-Methods: ' . ($cors['allowed_methods'] ?? 'GET, POST, OPTIONS'));
header('Access-Control-Max-Age: ' . (string)($cors['max_age'] ?? 86400));
header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    $pdo = Database::connection();
} catch (Throwable $e) {
    jsonResponse(['message' => '数据库连接失败', 'error' => $e->getMessage()], 500);
}
