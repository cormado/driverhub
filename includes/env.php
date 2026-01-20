<?php

$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    return;
}



$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
    if (str_starts_with(trim($line), '#')) {
        continue;
    }

    if (!str_contains($line, '=')) {
        continue;
    }

    [$key, $value] = explode('=', $line, 2);

    $key = trim($key);
    $value = trim($value, " \t\n\r\0\x0B\"'");

    putenv("$key=$value");
    $_ENV[$key] = $value;
}
// echo json_encode([
//     'status' => getenv('TRUCKY_WEBHOOK_SECRET')
// ]);
