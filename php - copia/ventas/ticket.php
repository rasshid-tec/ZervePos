<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../helpers.php';

$ventaId = $_GET['VentaId'] ?? null;
if (!$ventaId) jsonError('Falta parámetro VentaId');

$sql    = "{ CALL sp_ObtenerTicket(?) }";
$params = [[$ventaId, SQLSRV_PARAM_IN]];
$stmt   = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) jsonError('Error al obtener ticket', 500);

// Resultset 1: encabezado
$encabezado = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if ($encabezado) {
    $encabezado = fixDates($encabezado, ['FechaVenta']);
}

// Resultset 2: productos
sqlsrv_next_result($stmt);
$productos = [];
while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $productos[] = $r;
}

// Resultset 3: pagos
sqlsrv_next_result($stmt);
$pagos = [];
while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $pagos[] = fixDates($r, ['FechaPago']);
}

// Resultset 4: crédito
sqlsrv_next_result($stmt);
$credito = null;
if ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $credito = $r;
}

sqlsrv_free_stmt($stmt);

jsonResponse([
    'success'    => true,
    'encabezado' => $encabezado,
    'productos'  => $productos,
    'pagos'      => $pagos,
    'credito'    => $credito,
]);