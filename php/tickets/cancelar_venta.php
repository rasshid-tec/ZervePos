<?php
ob_start();
require_once '../conexion.php';
require_once '../helpers.php';

header('Content-Type: application/json');

$body       = getJsonInput();
$ventaId    = isset($body['ventaId'])    ? (int)$body['ventaId']    : 0;
$empleadoId = isset($body['empleadoId']) ? (int)$body['empleadoId'] : 0;

if (!$ventaId || !$empleadoId) {
    echo jsonError('Parámetros incompletos');
    exit;
}

$status = 0;
$sql    = "{CALL sp_CancelarVenta(?, ?, ?)}";
$params = [
    [$ventaId,    SQLSRV_PARAM_IN],
    [$empleadoId, SQLSRV_PARAM_IN],
    [&$status,    SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT],
];

$stmt = sqlsrv_query($conn, $sql, $params);

if (!$stmt) {
    echo jsonError('Error al ejecutar cancelación');
    exit;
}

while (sqlsrv_next_result($stmt)) {}

$mensajes = [
    1  => 'Venta cancelada correctamente',
    -1 => 'La venta no existe o ya fue cancelada',
    -2 => 'No se puede cancelar: tiene un crédito pendiente',
];

echo json_encode([
    'success' => $status === 1,
    'status'  => $status,
    'mensaje' => $mensajes[$status] ?? 'Error desconocido',
]);
?>