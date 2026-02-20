<?php

require __DIR__ . '/../_bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    jsonResponse(['message' => 'Method Not Allowed'], 405);
}

$body = parseJsonBody();
$id = (int)($body['id'] ?? 0);
$payload = $body['payload'] ?? [];
if (!is_array($payload)) {
    $payload = [];
}

if ($id <= 0) {
    jsonResponse(['message' => 'id 无效'], 422);
}

$stmt = $pdo->prepare('SELECT method, test_url, response_example FROM api_items WHERE id = :id');
$stmt->execute(['id' => $id]);
$api = $stmt->fetch();

if (!$api) {
    jsonResponse(['message' => 'API 不存在'], 404);
}

if (empty($api['test_url'])) {
    jsonResponse([
        'mode' => 'mock',
        'message' => '未配置 test_url，返回预设响应示例',
        'data' => json_decode((string)$api['response_example'], true),
    ]);
}

$ch = curl_init((string)$api['test_url']);
$methodType = strtoupper((string)$api['method']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $methodType);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
if (in_array($methodType, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
}
$result = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    jsonResponse(['message' => '测试请求失败', 'error' => $error], 500);
}

$decoded = json_decode((string)$result, true);
jsonResponse([
    'mode' => 'proxy',
    'status' => $status,
    'data' => $decoded ?? $result,
]);
