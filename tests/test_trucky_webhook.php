<?php

/**
 * TEST LOCAL PARA WEBHOOK TRUCKY
 * Simulación real compatible con el endpoint
 */

$webhookUrl = 'https://vtc.vintara.xyz/driverhub/Api/trucky/job-finished.php';
// $webhookUrl = 'http://localhost/driverhub/Api/trucky/job-finished.php';

require __DIR__ . '/../includes/db.php';

$webhookSecret = getenv('TRUCKY_WEBHOOK_SECRET');

if (!$webhookSecret) {
    die('❌ TRUCKY_WEBHOOK_SECRET no cargado');
}

/* =========================
   📦 Payload SIMULADO REAL
========================= */

$payload = [
    'event' => 'job_completed',
    'data' => [
        'id' => 987654322,
        'user_id' => 251971,
        'driven_distance_km' => 1250,
        'income' => 48000,
        'game' => [
            'code' => 'ETS2'
        ],
        'source_city_id' => 'Madrid',
        'destination_city_id' => 'Berlin',
        'cargo_definition' => [
            'name' => 'Electronics'
        ],
        'vehicle_brand_name' => 'Scania',
        'vehicle_model_name' => 'S'
    ]
];

$jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);

/* =========================
   🔑 Firma HMAC (IGUAL TRUCKY)
========================= */

$signature = hash_hmac(
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
        'X-Signature-Sha256: ' . $signature
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
