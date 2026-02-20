<?php

require __DIR__ . '/_bootstrap.php';

jsonResponse([
    'message' => 'API 服务运行中',
    'tip' => '请访问具体接口文件，例如 /api/health.php 或 /api/auth/login.php',
]);
