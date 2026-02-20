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

$pdo->prepare('DELETE FROM api_items WHERE category_id = :id')->execute(['id' => $id]);
$pdo->prepare('DELETE FROM categories WHERE id = :id')->execute(['id' => $id]);

jsonResponse(['message' => '删除成功']);
