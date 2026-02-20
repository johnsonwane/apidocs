<?php

require __DIR__ . '/_bootstrap.php';

jsonResponse([
    'message' => 'ok',
    'uri' => $_SERVER['REQUEST_URI'] ?? '',
]);
