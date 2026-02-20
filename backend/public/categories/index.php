<?php

require __DIR__ . '/../_bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $rows = $pdo->query('SELECT id, name, sort_order FROM categories ORDER BY sort_order ASC, id DESC')->fetchAll();
    jsonResponse(['data' => $rows]);
}

if ($method === 'POST') {
    requireAdmin($pdo);
    $body = parseJsonBody();
    $stmt = $pdo->prepare('INSERT INTO categories(name, sort_order) VALUES(:name, :sort_order)');
    $stmt->execute([
        'name' => trim((string)($body['name'] ?? '')),
        'sort_order' => (int)($body['sort_order'] ?? 99),
    ]);
    jsonResponse(['message' => '创建成功']);
}

jsonResponse(['message' => 'Method Not Allowed'], 405);
