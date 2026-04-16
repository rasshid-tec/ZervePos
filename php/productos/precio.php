<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../helpers.php';

$productoId = $_GET['ProductoId'] ?? null;
$nivelId    = $_GET['NivelId']    ?? null;

if (!$productoId || !$nivelId) {
    jsonError('Faltan parámetros: ProductoId, NivelId');
}

$sql = "{ CALL sp_ObtenerPrecioProducto(?, ?) }";
$params = [
    [$productoId, SQLSRV_PARAM_IN],
    [$nivelId,    SQLSRV_PARAM_IN],
];

$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) jsonError('Error al obtener precio', 500);

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($stmt);

jsonResponse(['success' => true, 'producto' => $row]);