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
   (Trucky usa esto)
========================= */

if (!$payload || !isset($payload['event'])) {
    http_response_code(200);
    echo json_encode(['status' => 'acknowledged']);
    exit;
}

/* =========================
   🔐 Validar firma webhook
   (DESACTIVADO)
========================= */

// $headers   = getRequestHeaders();
// $signature = $headers['X-Trucky-Signature'] ?? '';

// $expectedSignature = 'sha256=' . hash_hmac(
//     'sha256',
//     $rawPayload,
//     env('TRUCKY_WEBHOOK_SECRET')
// );

// if (!hash_equals($expectedSignature, $signature)) {
//     http_response_code(401);
//     exit(json_encode(['error' => 'Invalid webhook signature']));
// }

/* =========================
   🔐 Validar API Key
   (DESACTIVADO)
========================= */

// if (
//     !isset($headers['X-Trucky-Api-Key']) ||
//     $headers['X-Trucky-Api-Key'] !== env('TRUCKY_API_KEY')
// ) {
//     http_response_code(401);
//     exit(json_encode(['error' => 'Invalid API key']));
// }

/* =========================
   📡 Validar evento
========================= */

if ($payload['event'] !== 'job.finished') {
    http_response_code(200);
    echo json_encode(['status' => 'ignored']);
    exit;
}

$data = $payload['data'];

/* =========================
   👤 Usuario
========================= */

$stmt = $conn->prepare("
    SELECT id FROM users WHERE trucky_driver_id = ?
");
$stmt->bind_param("i", $data['driver']['id']);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    http_response_code(404);
    exit(json_encode(['error' => 'Driver not registered']));
}

$userId = $user['id'];

/* =========================
   🚫 Duplicados
========================= */

$stmt = $conn->prepare("
    SELECT id FROM trucksbook_jobs WHERE job_id = ?
");
$stmt->bind_param("i", $data['job_id']);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['status' => 'duplicate']);
    exit;
}

/* =========================
   🧮 Puntos
========================= */

$points = (int) $data['distance_km'] * (int) env('POINTS_PER_KM', 1);

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
    "iiisiisssssi",
    $data['job_id'],
    $userId,
    $data['game'],
    $data['distance_km'],
    $data['income'],
    $data['from']['city'],
    $data['from']['country'],
    $data['to']['city'],
    $data['to']['country'],
    $data['cargo'],
    $data['truck'],
    $points
);

$stmt->execute();

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
        {$data['distance_km']},
        1,
        $points,
        $points,
        NOW()
    )
    ON DUPLICATE KEY UPDATE
        total_km = total_km + {$data['distance_km']},
        total_jobs = total_jobs + 1,
        total_points = total_points + $points,
        available_points = available_points + $points,
        last_job_at = NOW()
");

/* =========================
   ✅ OK
========================= */

echo json_encode([
    'status' => 'ok',
    'job_id' => $data['job_id'],
    'points' => $points
]);
