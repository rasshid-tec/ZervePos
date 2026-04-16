<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../helpers.php';

$input        = getJsonInput();
$sucursalId   = $input['SucursalId']   ?? null;
$empleadoId   = $input['EmpleadoId']   ?? null;
$montoInicial = $input['MontoInicial'] ?? null;

if (!$sucursalId || !$empleadoId || $montoInicial === null) {
    jsonError('Faltan parámetros: SucursalId, EmpleadoId, MontoInicial');
}

$cajaId = 0;
$sql = "{ CALL sp_AbrirCaja(?, ?, ?, ?) }";
$params = [
    [$sucursalId,   SQLSRV_PARAM_IN],
    [$empleadoId,   SQLSRV_PARAM_IN],
    [$montoInicial, SQLSRV_PARAM_IN],
    [&$cajaId,      SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT],
];

$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) jsonError('Error al abrir caja', 500);

sqlsrv_free_stmt($stmt);
jsonResponse(['success' => true, 'CajaId' => $cajaId]);