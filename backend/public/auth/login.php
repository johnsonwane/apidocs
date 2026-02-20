<?php

require __DIR__ . '/../_bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    jsonResponse(['message' => 'Method Not Allowed'], 405);
}

$body = parseJsonBody();
$stmt = $pdo->prepare('SELECT id, password_hash FROM admins WHERE username = :username LIMIT 1');
$stmt->execute(['username' => $body['username'] ?? '']);
$admin = $stmt->fetch();

if (!$admin || !password_verify($body['password'] ?? '', $admin['password_hash'])) {
    jsonResponse(['message' => '账号或密码错误'], 401);
}

$token = bin2hex(random_bytes(24));
$insert = $pdo->prepare('INSERT INTO admin_sessions(admin_id, token, expired_at) VALUES(:admin_id, :token, DATE_ADD(NOW(), INTERVAL 12 HOUR))');
$insert->execute(['admin_id' => $admin['id'], 'token' => $token]);

jsonResponse(['data' => ['token' => $token]]);
