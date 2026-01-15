<?php
header('Content-Type: application/json');

require __DIR__ . '/../../config/database.php';

/* =========================
   📥 Leer JSON
========================= */

$payload = json_decode(file_get_contents('php://input'), true);

if (!$payload || $payload['event'] !== 'job.finished') {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid event']));
}

$data = $payload['data'];

/* =========================
   🔐 Validar API Key
========================= */

$headers = getallheaders();
if (
    !isset($headers['X-Trucky-Api-Key']) ||
    $headers['X-Trucky-Api-Key'] !== getenv('TRUCKY_API_KEY')
) {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

/* =========================
   👤 Buscar usuario
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
   🚫 Evitar duplicados
========================= */

$stmt = $conn->prepare("
    SELECT id FROM trucksbook_jobs WHERE job_id = ?
");
$stmt->bind_param("i", $data['job_id']);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    exit(json_encode(['status' => 'ignored', 'reason' => 'duplicate']));
}

/* =========================
   🧮 Calcular puntos
========================= */

$points = $data['distance_km'] * (int) getenv('POINTS_PER_KM');

/* =========================
   💾 Insertar job
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
    INSERT INTO user_stats (user_id, total_km, total_jobs, total_points, available_points, last_job_at)
    VALUES ($userId, {$data['distance_km']}, 1, $points, $points, NOW())
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
