<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../helpers.php';

$cajaId = $_GET['CajaId'] ?? null;
if (!$cajaId) jsonError('Falta parámetro CajaId');

$sql = "{ CALL sp_ObtenerResumenCaja(?) }";
$params = [[$cajaId, SQLSRV_PARAM_IN]];

$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) jsonError('Error al obtener resumen', 500);

// Resultset 1: Encabezado
$encabezado = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if ($encabezado) {
    $encabezado = fixDates($encabezado, ['FechaApertura', 'FechaCierre']);
}

// Resultset 2: Pagos por método
sqlsrv_next_result($stmt);
$pagos = [];
while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) $pagos[] = $r;

// Resultset 3: Movimientos
sqlsrv_next_result($stmt);
$movimientos = [];
while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) $movimientos[] = $r;

// Resultset 4: Totales
sqlsrv_next_result($stmt);
$totales = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

sqlsrv_free_stmt($stmt);

jsonResponse([
    'success'     => true,
    'encabezado'  => $encabezado,
    'pagos'       => $pagos,
    'movimientos' => $movimientos,
    'totales'     => $totales,
]);