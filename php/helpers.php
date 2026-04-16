<?php
/**
 * ZervePOS - Helpers para responder JSON
 * Ubicación: C:\xampp\htdocs\php\helpers.php
 *
 * Se incluye junto con conexion.php en cada endpoint.
 */

header('Content-Type: application/json; charset=utf-8');

/** Envía una respuesta JSON y termina la ejecución. */
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Envía un error JSON estándar. */
function jsonError($mensaje, $code = 400, $detalle = null) {
    jsonResponse([
        'success' => false,
        'error'   => $mensaje,
        'detail'  => $detalle ?? sqlsrv_errors()
    ], $code);
}

/** Lee y decodifica el body JSON de la request (POST). */
function getJsonInput() {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        jsonError('JSON inválido en el body', 400);
    }
    return $data;
}

/** Convierte objetos DateTime de sqlsrv a string para serializar. */
function fixDates(array $row, array $campos) {
    foreach ($campos as $c) {
        if (isset($row[$c]) && $row[$c] instanceof DateTime) {
            $row[$c] = $row[$c]->format('Y-m-d H:i:s');
        }
    }
    return $row;
}