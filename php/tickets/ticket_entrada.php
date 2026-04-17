<?php
ob_start();
require_once '../conexion.php';
require_once '../helpers.php';

header('Content-Type: application/json');

$body = getJsonInput();
$entradaId = isset($body['entradaId']) ? (int)$body['entradaId'] : 0;

if (!$entradaId) {
    echo jsonError('EntradaId requerido');
    exit;
}

$sql = '{CALL sp_ObtenerTicketEntrada(?)}';
$params = [[$entradaId, SQLSRV_PARAM_IN]];
$stmt = sqlsrv_query($conn, $sql, $params);

if (!$stmt) {
    echo jsonError('Error al obtener ticket de entrada');
    exit;
}

$encabezado = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    if ($row['Fecha'] instanceof DateTime) $row['Fecha'] = $row['Fecha']->format('Y-m-d H:i:s');
    $encabezado = $row;
}

sqlsrv_next_result($stmt);

$detalle = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $detalle[] = $row;
}

echo json_encode(['success' => true, 'encabezado' => $encabezado, 'detalle' => $detalle]);