<?php
ob_start();
require_once '../conexion.php';
require_once '../helpers.php';

header('Content-Type: application/json');

$empleadoId = isset($_GET['empleadoId']) ? (int)$_GET['empleadoId'] : 0;
$rol = isset($_GET['rol']) ? urldecode($_GET['rol']) : '';
if (!$empleadoId || !$rol) {
    echo jsonError('Parámetros incompletos');
    exit;
}

$sql    = '{CALL sp_NotificacionesCreditos(?, ?)}';
$params = [
    [$empleadoId, SQLSRV_PARAM_IN],
    [$rol,        SQLSRV_PARAM_IN],
];
$stmt = sqlsrv_query($conn, $sql, $params);

if (!$stmt) {
    echo jsonError('Error al obtener notificaciones de créditos');
    exit;
}

$creditos = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    if ($row['FechaCredito'] instanceof DateTime) {
        $row['FechaCredito'] = $row['FechaCredito']->format('Y-m-d');
    }
    $creditos[] = $row;
}

echo json_encode(['success' => true, 'creditos' => $creditos]);
?>