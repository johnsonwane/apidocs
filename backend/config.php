<?php

return [
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int)(getenv('DB_PORT') ?: 3306),
        'database' => getenv('DB_NAME') ?: 'api_docs',
        'username' => getenv('DB_USER') ?: 'api_docs_user',
        'password' => getenv('DB_PASS') ?: '',
        'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
    ],
    'cors' => [
        // 支持多个来源，生产可改为仅你的前端域名
        'allowed_origins' => [
            'http://localhost:5173',
            'http://localhost:5174',
            'https://apidocs.hahahaxinli.com',
        ],
        'allowed_methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'allowed_headers' => 'Content-Type, Authorization, X-Requested-With',
        'max_age' => 86400,
    ],
];
