<?php

/**
 * TEST LOCAL PARA WEBHOOK TRUCKY
 * Ejecuta una simulación real del evento job.finished
 */

function env($key, $default = null)
{
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
}

$webhookUrl = 'http://localhost/Driverhub/Api/trucky/job-finished.php';

/* =========================
   🔐 MISMA KEY QUE EN .env
========================= */

require __DIR__ . '/../includes/db.php';
$webhookSecret = env('TRUCKY_WEBHOOK_SECRET');

// $webhookSecret = 'TU_TRUCKY_WEBHOOK_SECRET_AQUI';

/* =========================
   📦 Payload simulado
========================= */

$payload = [
    'event' => 'job.finished',
    'data' => [
        'job_id' => 987654321,
        'game' => 'ETS2',
        'distance_km' => 1250,
        'income' => 48000,

        'from' => [
            'city' => 'Madrid',
            'country' => 'Spain'
        ],
        'to' => [
            'city' => 'Berlin',
            'country' => 'Germany'
        ],

        'cargo' => 'Electronics',
        'truck' => 'Scania S',

        'driver' => [
            'id' => 251971,
            'name' => 'xbyangelmx'
        ]
    ]
];

$jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);

/* =========================
   🔑 Firma HMAC (igual Trucky)
========================= */

$signature = 'sha256=' . hash_hmac(
    'sha256',
    $jsonPayload,
    $webhookSecret
);

/* =========================
   🚀 CURL
========================= */

$ch = curl_init($webhookUrl);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-Trucky-Signature: ' . $signature
    ],
    CURLOPT_POSTFIELDS => $jsonPayload
]);

$response = curl_exec($ch);
$error    = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

/* =========================
   📤 Resultado
========================= */

header('Content-Type: application/json');

echo json_encode([
    'http_code' => $httpCode,
    'response'  => json_decode($response, true),
    'raw'       => $response,
    'error'     => $error
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
