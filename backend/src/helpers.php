<?php

function jsonResponse($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function parseJsonBody(): array
{
    $raw = file_get_contents('php://input') ?: '{}';
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function getAuthorizationHeader(): string
{
    // 1) 通用 CGI/FPM
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if ($header !== '') {
        return $header;
    }

    // 2) Apache + rewrite 透传场景
    $header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if ($header !== '') {
        return $header;
    }

    // 3) 部分服务器通过 apache_request_headers 提供
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        foreach ($headers as $k => $v) {
            if (strtolower($k) === 'authorization') {
                return (string)$v;
            }
        }
    }

    return '';
}

function requireAdmin(PDO $pdo): int
{
    $header = getAuthorizationHeader();
    if (!preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
        jsonResponse(['message' => '未授权'], 401);
    }

    $token = trim((string)$matches[1]);
    if ($token === '') {
        jsonResponse(['message' => '未授权'], 401);
    }

    $stmt = $pdo->prepare('SELECT admin_id FROM admin_sessions WHERE token = :token AND expired_at > NOW()');
    $stmt->execute(['token' => $token]);
    $session = $stmt->fetch();

    if (!$session) {
        jsonResponse(['message' => '登录已过期，请重新登录'], 401);
    }

    return (int)$session['admin_id'];
}
