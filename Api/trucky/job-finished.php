<?php
declare(strict_types=1);

header('Content-Type: application/json');

require __DIR__ . '/../../includes/db.php';

/* ======================================================
   🧩 Helpers
====================================================== */

function getRequestHeaders(): array
{
    if (function_exists('getallheaders')) {
        return array_change_key_case(getallheaders(), CASE_LOWER);
    }

    $headers = [];
    foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'HTTP_') === 0) {
            $name = strtolower(str_replace('_', '-', substr($key, 5)));
            $headers[$name] = $value;
        }
    }
    return $headers;
}

function logWebhook(string $title, array $data = []): void
{
    $logDir  = __DIR__ . '/../../logs';
    $logFile = $logDir . '/trucky-webhook.log';

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $entry = [
        'time'    => date('Y-m-d H:i:s'),
        'ip'      => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'title'   => $title,
        'headers' => getRequestHeaders(),
        'data'    => $data
    ];

    file_put_contents(
        $logFile,
        json_encode($entry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        . PHP_EOL . str_repeat('-', 80) . PHP_EOL,
        FILE_APPEND
    );
}

function respond(int $code, array $body): never
{
    http_response_code($code);
    exit(json_encode($body, JSON_UNESCAPED_UNICODE));
}

/* ======================================================
   🌐 Validar método
====================================================== */

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    respond(405, ['error' => 'Method not allowed']);
}

/* ======================================================
   🌐 GET → Webhook alive
====================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    respond(200, ['status' => 'webhook alive']);
}

/* ======================================================
   📥 RAW Request
====================================================== */

$rawPayload = file_get_contents('php://input') ?: '';

if (strlen($rawPayload) > 200_000) {
    respond(413, ['error' => 'Payload too large']);
}

$payload = json_decode($rawPayload, true);

logWebhook('Incoming request', [
    'method' => $_SERVER['REQUEST_METHOD'],
    'raw'    => substr($rawPayload, 0, 5000)
]);

if (!$payload || !isset($payload['event'])) {
    respond(200, ['status' => 'acknowledged']);
}

/* ======================================================
   🔐 Validar firma webhook (Trucky)
====================================================== */

$headers = getRequestHeaders();

$signature = trim(
    $headers['x-signature-sha256']
        ?? $headers['x-trucky-signature']
        ?? ''
);

$secret = getenv('TRUCKY_WEBHOOK_SECRET');

if (!$secret) {
    logWebhook('Webhook error', ['error' => 'Missing webhook secret']);
    respond(500, ['error' => 'Server misconfigured']);
}

if ($signature === '') {
    logWebhook('Webhook error', ['error' => 'Missing signature header']);
    respond(401, ['error' => 'Missing signature']);
}

$expectedSignature = hash_hmac('sha256', $rawPayload, $secret);

if (!hash_equals($expectedSignature, $signature)) {
    logWebhook('Invalid signature', [
        'expected' => $expectedSignature,
        'received' => $signature
    ]);
    respond(401, ['error' => 'Invalid webhook signature']);
}

/* ======================================================
   📡 Validar evento
====================================================== */

if ($payload['event'] !== 'job_completed') {
    logWebhook('Ignored event', ['event' => $payload['event']]);
    respond(200, ['status' => 'ignored']);
}

$data = $payload['data'];

/* ======================================================
   🧠 Normalización payload Trucky
====================================================== */

$jobId      = (int) ($data['id'] ?? 0);
$userTrucky = (int) ($data['user_id'] ?? 0);
$distanceKm = isset($data['driven_distance_km'])
    ? (int) round($data['driven_distance_km'])
    : 0;
$income     = (int) ($data['income'] ?? 0);
$game       = $data['game']['code'] ?? 'ETS2';

$fromCity = $data['source_city_id'] ?? '';
$toCity   = $data['destination_city_id'] ?? '';

$fromCountry = null;
$toCountry   = null;

$cargo = $data['cargo_definition']['name'] ?? 'sin carga';

$truck = trim(
    ($data['vehicle_brand_name'] ?? '') . ' ' .
    ($data['vehicle_model_name'] ?? '')
);
$truck = $truck ?: 'sin troca';

/* ======================================================
   👤 Usuario
====================================================== */

$stmt = $conn->prepare(
    "SELECT id FROM users WHERE trucky_driver_id = ?"
);
$stmt->bind_param("i", $userTrucky);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    logWebhook('Driver not registered', ['driver_id' => $userTrucky]);
    respond(200, ['error' => 'Driver not registered']);
}

$userId = (int) $user['id'];

/* ======================================================
   🚫 Duplicados
====================================================== */

$stmt = $conn->prepare(
    "SELECT id FROM trucksbook_jobs WHERE job_id = ?"
);
$stmt->bind_param("i", $jobId);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    logWebhook('Duplicate job ignored', [
        'job_id' => $jobId,
        'user_id' => $userId
    ]);
    respond(200, ['status' => 'duplicate']);
}
$stmt->close();

/* ======================================================
   🧮 Puntos
====================================================== */

$pointsPerKm = getenv('POINTS_PER_KM') !== false
    ? (int) getenv('POINTS_PER_KM')
    : 1;

$points = $distanceKm * $pointsPerKm;

/* ======================================================
   🔒 Transacción
====================================================== */

$conn->begin_transaction();

try {

    /* =========================
       💾 Insert job
    ========================= */

    $stmt = $conn->prepare("
        INSERT INTO trucksbook_jobs (
            job_id, user_id, game, distance_km, profit,
            from_city, from_country, to_city, to_country,
            cargo, truck, points_earned
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
        "iisiissssssi",
        $jobId,
        $userId,
        $game,
        $distanceKm,
        $income,
        $fromCity,
        $fromCountry,
        $toCity,
        $toCountry,
        $cargo,
        $truck,
        $points
    );

    $stmt->execute();
    $stmt->close();

    /* =========================
       📊 Stats
    ========================= */

    $conn->query("
        INSERT INTO user_stats (
            user_id, total_km, total_jobs,
            total_points, available_points, last_job_at
        )
        VALUES (
            $userId,
            $distanceKm,
            1,
            $points,
            $points,
            NOW()
        )
        ON DUPLICATE KEY UPDATE
            total_km = total_km + $distanceKm,
            total_jobs = total_jobs + 1,
            total_points = total_points + $points,
            available_points = available_points + $points,
            last_job_at = NOW()
    ");

    /* =========================
       🏆 Achievements
    ========================= */

    $stmt = $conn->prepare("CALL grant_distance_achievements(?)");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

} catch (Throwable $e) {

    $conn->rollback();

    logWebhook('Transaction failed', [
        'error'   => $e->getMessage(),
        'user_id'=> $userId,
        'job_id' => $jobId
    ]);

    respond(500, ['error' => 'Database transaction failed']);
}

/* ======================================================
   ✅ OK
====================================================== */

respond(200, [
    'status'  => 'ok',
    'job_id' => $jobId,
    'points' => $points
]);
