<?php
ob_start();
require_once '../conexion.php';
require_once '../helpers.php';

header('Content-Type: application/json');

$body      = getJsonInput();
$clienteId = isset($body['clienteId']) ? (int)$body['clienteId'] : 0;

if (!$clienteId) {
    echo jsonError('ClienteId requerido');
    exit;
}

$sql    = '{CALL sp_EstadoCuentaCliente(?)}';
$params = [[$clienteId, SQLSRV_PARAM_IN]];
$stmt   = sqlsrv_query($conn, $sql, $params);

if (!$stmt) {
    echo jsonError('Error al obtener estado de cuenta');
    exit;
}

$encabezado = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $encabezado = $row;
}

sqlsrv_next_result($stmt);

$creditos = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $creditos[] = $row;
}

echo json_encode([
    'success'    => true,
    'encabezado' => $encabezado,
    'creditos'   => $creditos,
]);
?>