<?php
// includes/api.php

// 1. CONFIGURACIÓN BASE
$api_vtc_imagen = "assets/img/logo.png";
$api_vtc_nombre = "VINTARA";
$api_vtc_miembros = "--";
$api_vtc_reclutamiento = "Abierto"; 
$vtc_id = 81636; 

// 2. INICIALIZAR VARIABLES
$evento_destacado = null; 
$eventos_asistencia = []; 
$eventos_manuales_ids = [];

// 3. LEER EVENTOS DE ASISTENCIA DESDE LA BASE DE DATOS (BLINDADO)
// Verificamos que $conn exista y sea un objeto MySQLi válido
if (isset($conn) && $conn instanceof mysqli) {
    // Verificamos si la tabla existe primero para evitar crash
    $check_table = $conn->query("SHOW TABLES LIKE 'event_ids'");
    
    if ($check_table && $check_table->num_rows > 0) {
        $sql_events = "SELECT event_id FROM event_ids ORDER BY id DESC";
        $res_events = $conn->query($sql_events);
        
        if ($res_events) {
            while ($row = $res_events->fetch_assoc()) {
                $eventos_manuales_ids[] = $row['event_id'];
            }
        }
    }
}

// 4. FUNCIÓN DE CONEXIÓN A TRUCKERSMP
function conectarTruckersMP($endpoint) {
    if (!function_exists('curl_init')) return null; // Si no hay cURL, no hacemos nada

    $url = "https://api.truckersmp.com/v2" . $endpoint;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Bajamos timeout a 3s para que sea rápido
    curl_setopt($ch, CURLOPT_USERAGENT, "VintaraHub/1.0 (Dev)");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $respuesta = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code === 200 && $respuesta) ? json_decode($respuesta, true) : null;
}

// 5. FUNCIÓN VERIFICAR USUARIO
function verificar_usuario_en_vtc($user_tmp_id) {
    global $vtc_id;
    if (empty($user_tmp_id) || $user_tmp_id == 0) return false;
    $datos = conectarTruckersMP("/player/" . $user_tmp_id);
    if (isset($datos['response']['vtc']['id'])) {
        if ($datos['response']['vtc']['id'] == $vtc_id) return true;
    }
    return false;
}

// 6. OBTENER DATOS DE LA VTC
$json_vtc = conectarTruckersMP("/vtc/$vtc_id");
if (isset($json_vtc['response'])) {
    $r = $json_vtc['response'];
    $api_vtc_nombre = $r['name'];
    $api_vtc_miembros = $r['members_count'];
    $api_vtc_reclutamiento = $r['recruitment'];
    $api_vtc_imagen = $r['logo'];
}

$ahora = time();

// ---------------------------------------------------------
// LÓGICA A: EVENTO DESTACADO
// ---------------------------------------------------------
$json_eventos_propios = conectarTruckersMP("/vtc/$vtc_id/events");

if (isset($json_eventos_propios['response']) && is_array($json_eventos_propios['response'])) {
    foreach ($json_eventos_propios['response'] as $evt) {
        $fecha = strtotime($evt['start_at']);
        if ($fecha > $ahora) {
            $salida = is_array($evt['departure']) ? $evt['departure']['city'] : $evt['departure'];
            $llegada = is_array($evt['arrive']) ? $evt['arrive']['city'] : $evt['arrive'];
            
            $evento_destacado = [
                'nombre' => $evt['name'],
                'fecha' => $fecha,
                'banner' => $evt['banner'] ?? null,
                'server' => $evt['server']['name'] ?? 'N/A',
                'salida' => $salida,
                'llegada' => $llegada,
                'url' => "https://truckersmp.com/events/" . $evt['id']
            ];
            break; 
        }
    }
}

// ---------------------------------------------------------
// LÓGICA B: AGENDA DE ASISTENCIA
// ---------------------------------------------------------
if (!empty($eventos_manuales_ids)) {
    foreach ($eventos_manuales_ids as $id_evt) {
        $json_evt = conectarTruckersMP("/events/$id_evt");
        if (isset($json_evt['response'])) {
            $evt = $json_evt['response'];
            $fecha = strtotime($evt['start_at']);
            
            if ($fecha > $ahora) {
                $salida = is_array($evt['departure']) ? $evt['departure']['city'] : $evt['departure'];
                $llegada = is_array($evt['arrive']) ? $evt['arrive']['city'] : $evt['arrive'];

                $eventos_asistencia[] = [
                    'nombre' => $evt['name'],
                    'fecha' => $fecha,
                    'organiza' => $evt['vtc']['name'],
                    'server' => $evt['server']['name'],
                    'banner' => $evt['banner'] ?? null,
                    'salida' => $salida,
                    'llegada' => $llegada,
                    'url' => "https://truckersmp.com/events/" . $evt['id']
                ];
            }
        }
    }
    // Ordenar agenda
    usort($eventos_asistencia, function($a, $b) { return $a['fecha'] - $b['fecha']; });
}
?>