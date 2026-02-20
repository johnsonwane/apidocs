<?php

require __DIR__ . '/../_bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    jsonResponse(['message' => 'Method Not Allowed'], 405);
}

requireAdmin($pdo);
$body = parseJsonBody();
$id = (int)($body['id'] ?? 0);

if ($id <= 0) {
    jsonResponse(['message' => 'id 无效'], 422);
}

$stmt = $pdo->prepare('UPDATE categories SET name = :name, sort_order = :sort_order WHERE id = :id');
$stmt->execute([
    'id' => $id,
    'name' => trim((string)($body['name'] ?? '')),
    'sort_order' => (int)($body['sort_order'] ?? 99),
]);

jsonResponse(['message' => '更新成功']);
