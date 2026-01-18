<?php
header('Content-Type: application/json');

require __DIR__ . '../../../includes/db.php';

/* =========================
   🧩 Helpers
========================= */

function getRequestHeaders()
{
    if (function_exists('getallheaders')) {
        return getallheaders();
    }

    $headers = [];
    foreach ($_SERVER as $key => $value) {
        if (str_starts_with($key, 'HTTP_')) {
            $name = str_replace('_', '-', substr($key, 5));
            $headers[$name] = $value;
        }
    }
    return $headers;
}

function logWebhook($title, $data = [])
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

function env($key, $default = null)
{
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
}


/* =========================
   📥 RAW Request
========================= */

$rawPayload = file_get_contents('php://input');
$payload    = json_decode($rawPayload, true);

logWebhook('Incoming request', [
    'method' => $_SERVER['REQUEST_METHOD'],
    'raw'    => $rawPayload
]);

/* =========================
   🌐 GET → Webhook alive
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(['status' => 'webhook alive']);
    exit;
}

/* =========================
   📭 POST vacío / test
========================= */

if (!$payload || !isset($payload['event'])) {
    http_response_code(200);
    echo json_encode(['status' => 'acknowledged']);
    exit;
}

/* =========================
   🔐 Validar firma webhook (Trucky)
========================= */

$headers = getRequestHeaders();

/**
 * Trucky envía la firma en este header:
 * X-Signature-Sha256
 * (NO usa el prefijo "sha256=")
 */
$signature = $headers['X-Signature-Sha256'] ?? '';

$secret = env('TRUCKY_WEBHOOK_SECRET');

if (!$secret) {
    logWebhook('Webhook error', ['error' => 'Missing webhook secret']);
    http_response_code(500);
    exit(json_encode(['error' => 'Server misconfigured']));
}

if (!$signature) {
    logWebhook('Webhook error', ['error' => 'Missing signature header']);
    http_response_code(401);
    exit(json_encode(['error' => 'Missing signature']));
}

/**
 * Firma esperada (HMAC SHA256 del raw payload)
 */
$expectedSignature = hash_hmac(
    'sha256',
    $rawPayload,
    $secret
);

/**
 * Comparación segura
 */
if (!hash_equals($expectedSignature, $signature)) {
    logWebhook('Invalid signature', [
        'expected' => $expectedSignature,
        'received' => $signature
    ]);
    http_response_code(401);
    exit(json_encode(['error' => 'Invalid webhook signature']));
}


/* =========================
   📡 Validar evento
========================= */

if ($payload['event'] !== 'job_completed') {
    logWebhook('IGNORANDO EVENTO', [
        'event' => $payload['event']
    ]);
    http_response_code(200);
    echo json_encode(['status' => 'ignored']);
    exit;
}


$data = $payload['data'];

// =========================
// 🧠 Normalización payload Trucky
// =========================

$jobId      = (int) $data['id'];
$userTrucky = (int) $data['user_id'];
$distanceKm = (int) round($data['driven_distance_km']) ?? 0;
$income     = (int) $data['income'] ?? 0;
$game       = $data['game']['code'] ?? 'ETS2';

$fromCity = $data['source_city_id'] ?? '';
$toCity   = $data['destination_city_id'] ?? '';

$cargo = $data['cargo_definition']['name'] ?? 'sin carga';

$truck = trim(
    ($data['vehicle_brand_name'] ?? '') . ' ' . ($data['vehicle_model_name'] ?? '')
);
$truck = $truck ?: 'sin troca';



/* =========================
   👤 Usuario
========================= */

$stmt = $conn->prepare("SELECT id FROM users WHERE trucky_driver_id = ?");
$stmt->bind_param("i", $userTrucky);

$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    http_response_code(200);
    logWebhook('Driver not registered', [
        'driver_id' => $userTrucky
    ]);
    exit(json_encode(['error' => 'Driver not registered']));
}

$userId = (int) $user['id'];

/* =========================
   🚫 Duplicados
========================= */

$stmt = $conn->prepare("SELECT id FROM trucksbook_jobs WHERE job_id = ?");
$stmt->bind_param("i", $jobId);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    logWebhook('Duplicate job ignored', [
        'job_id' => $jobId,
        'user_id' => $userId
    ]);
    http_response_code(200);
    echo json_encode(['status' => 'duplicate']);
    exit;
}

$stmt->close();

/* =========================
   🧮 Puntos
========================= */

$points = $distanceKm * (int) env('POINTS_PER_KM', 1);

/* =========================
   🔒 Transacción
========================= */

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

    $fromCountry = null;
    $toCountry = null;

    $stmt->bind_param(
        "iiisiisssssi",
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
       🏆 Grant achievements
    ========================= */

    $stmt = $conn->prepare("CALL grant_distance_achievements(?)");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    /* =========================
       ✅ Commit
    ========================= */

    $conn->commit();
} catch (Throwable $e) {

    $conn->rollback();

    logWebhook('Transaction failed', [
        'error' => $e->getMessage(),
        'user_id' => $userId,
        'job_id' => $jobId

    ]);

    http_response_code(500);
    exit(json_encode(['error' => 'Database transaction failed']));
}

/* =========================
   ✅ OK
========================= */

echo json_encode([
    'status'  => 'ok',
    'job_id' => $jobId,
    'points' => $points
]);
