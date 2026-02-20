<?php

require __DIR__ . '/../_bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    jsonResponse(['message' => 'Method Not Allowed'], 405);
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    jsonResponse(['message' => 'id 无效'], 422);
}

$stmt = $pdo->prepare('SELECT * FROM api_items WHERE id = :id');
$stmt->execute(['id' => $id]);
$row = $stmt->fetch();

if (!$row) {
    jsonResponse(['message' => 'API 不存在'], 404);
}

jsonResponse(['data' => $row]);
