<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../helpers.php';

$input      = getJsonInput();
$cajaId     = $input['CajaId']         ?? null;
$empleadoId = $input['EmpleadoId']     ?? null;
$tipo       = $input['TipoMovimiento'] ?? null;
$monto      = $input['Monto']          ?? null;
$motivo     = $input['Motivo']         ?? null;

if (!$cajaId || !$empleadoId || !$tipo || !$monto) {
    jsonError('Faltan parámetros: CajaId, EmpleadoId, TipoMovimiento, Monto');
}

$sql = "{ CALL sp_RegistrarMovimientoCaja(?, ?, ?, ?, ?) }";
$params = [
    [$cajaId,     SQLSRV_PARAM_IN],
    [$empleadoId, SQLSRV_PARAM_IN],
    [$tipo,       SQLSRV_PARAM_IN],
    [$monto,      SQLSRV_PARAM_IN],
    [$motivo,     SQLSRV_PARAM_IN],
];

$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) jsonError('Error al registrar movimiento', 500);

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($stmt);

jsonResponse(['success' => true, 'MovimientoId' => $row['MovimientoId'] ?? null]);