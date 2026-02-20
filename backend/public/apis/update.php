<?php

require __DIR__ . '/../_bootstrap.php';

function validateRequestParams(string $raw): array
{
    $rows = json_decode($raw, true);
    if (!is_array($rows)) {
        return [false, 'request_params 必须是 JSON 数组'];
    }

    foreach ($rows as $idx => $row) {
        if (!is_array($row)) {
            return [false, 'request_params 第 ' . ($idx + 1) . ' 行必须是对象'];
        }
        foreach (['name', 'type', 'required', 'description'] as $key) {
            if (!array_key_exists($key, $row)) {
                return [false, 'request_params 第 ' . ($idx + 1) . ' 行缺少字段：' . $key];
            }
        }
        if (!is_string($row['name']) || trim($row['name']) === '') {
            return [false, 'request_params 第 ' . ($idx + 1) . ' 行 name 必须是非空字符串'];
        }
        if (!is_string($row['type']) || trim($row['type']) === '') {
            return [false, 'request_params 第 ' . ($idx + 1) . ' 行 type 必须是非空字符串'];
        }
        if (!is_bool($row['required'])) {
            return [false, 'request_params 第 ' . ($idx + 1) . ' 行 required 必须是布尔值'];
        }
        if (!is_string($row['description'])) {
            return [false, 'request_params 第 ' . ($idx + 1) . ' 行 description 必须是字符串'];
        }
    }

    return [true, ''];
}

function validateResponseFields(string $raw): array
{
    $rows = json_decode($raw, true);
    if (!is_array($rows)) {
        return [false, 'response_fields 必须是 JSON 数组'];
    }

    foreach ($rows as $idx => $row) {
        if (!is_array($row)) {
            return [false, 'response_fields 第 ' . ($idx + 1) . ' 行必须是对象'];
        }
        foreach (['name', 'type', 'description'] as $key) {
            if (!array_key_exists($key, $row)) {
                return [false, 'response_fields 第 ' . ($idx + 1) . ' 行缺少字段：' . $key];
            }
        }
        if (!is_string($row['name']) || trim($row['name']) === '') {
            return [false, 'response_fields 第 ' . ($idx + 1) . ' 行 name 必须是非空字符串'];
        }
        if (!is_string($row['type']) || trim($row['type']) === '') {
            return [false, 'response_fields 第 ' . ($idx + 1) . ' 行 type 必须是非空字符串'];
        }
        if (!is_string($row['description'])) {
            return [false, 'response_fields 第 ' . ($idx + 1) . ' 行 description 必须是字符串'];
        }
    }

    return [true, ''];
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    jsonResponse(['message' => 'Method Not Allowed'], 405);
}

requireAdmin($pdo);
$body = parseJsonBody();
$id = (int)($body['id'] ?? 0);

if ($id <= 0) {
    jsonResponse(['message' => 'id 无效'], 422);
}

$requestParams = (string)($body['request_params'] ?? '[]');
$responseFields = (string)($body['response_fields'] ?? '[]');
[$okReq, $msgReq] = validateRequestParams($requestParams);
if (!$okReq) {
    jsonResponse(['message' => $msgReq], 422);
}
[$okResp, $msgResp] = validateResponseFields($responseFields);
if (!$okResp) {
    jsonResponse(['message' => $msgResp], 422);
}

$method = strtoupper((string)($body['method'] ?? 'GET'));
if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE'], true)) {
    jsonResponse(['message' => 'method 仅支持 GET/POST/PUT/DELETE'], 422);
}

$stmt = $pdo->prepare('UPDATE api_items SET category_id=:category_id, name=:name, method=:method, path=:path, description=:description, request_params=:request_params, response_fields=:response_fields, request_example=:request_example, response_example=:response_example, test_url=:test_url WHERE id=:id');
$stmt->execute([
    'id' => $id,
    'category_id' => (int)($body['category_id'] ?? 0),
    'name' => trim((string)($body['name'] ?? '')),
    'method' => $method,
    'path' => trim((string)($body['path'] ?? '')),
    'description' => (string)($body['description'] ?? ''),
    'request_params' => $requestParams,
    'response_fields' => $responseFields,
    'request_example' => (string)($body['request_example'] ?? '{}'),
    'response_example' => (string)($body['response_example'] ?? '{}'),
    'test_url' => (string)($body['test_url'] ?? ''),
]);

jsonResponse(['message' => '更新成功']);
