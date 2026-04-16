<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../helpers.php';

$termino    = $_GET['termino']    ?? '';
$sucursalId = $_GET['SucursalId'] ?? null;
$nivelId    = $_GET['NivelId']    ?? 4;

if (!$sucursalId || $termino === '') {
    jsonError('Faltan parámetros: termino, SucursalId');
}

$sql = "{ CALL sp_BuscarProducto(?, ?, ?) }";
$params = [
    [$termino,    SQLSRV_PARAM_IN],
    [$sucursalId, SQLSRV_PARAM_IN],
    [$nivelId,    SQLSRV_PARAM_IN],
];

$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) jsonError('Error en búsqueda', 500);

$productos = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $productos[] = $row;
}

sqlsrv_free_stmt($stmt);
jsonResponse(['success' => true, 'productos' => $productos]);