<?php

require __DIR__ . '/../_bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    jsonResponse(['message' => 'Method Not Allowed'], 405);
}

$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
if ($categoryId > 0) {
    $stmt = $pdo->prepare('SELECT id, category_id, name, method, path FROM api_items WHERE category_id = :category_id ORDER BY id DESC');
    $stmt->execute(['category_id' => $categoryId]);
    $rows = $stmt->fetchAll();
} else {
    $rows = $pdo->query('SELECT id, category_id, name, method, path FROM api_items ORDER BY id DESC')->fetchAll();
}

jsonResponse(['data' => $rows]);
