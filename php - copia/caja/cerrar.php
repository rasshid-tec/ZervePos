<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../helpers.php';

$input      = getJsonInput();
$cajaId     = $input['CajaId']     ?? null;
$montoFinal = $input['MontoFinal'] ?? null;

if (!$cajaId || $montoFinal === null) {
    jsonError('Faltan parámetros: CajaId, MontoFinal');
}

$sql = "{ CALL sp_CerrarCaja(?, ?) }";
$params = [
    [$cajaId,     SQLSRV_PARAM_IN],
    [$montoFinal, SQLSRV_PARAM_IN],
];

$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) jsonError('Error al cerrar caja', 500);

$resumen = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($stmt);

jsonResponse(['success' => true, 'resumen' => $resumen]);