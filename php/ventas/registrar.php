<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../helpers.php';

$input = getJsonInput();

$clienteId  = $input['ClienteId']  ?? null;
$empleadoId = $input['EmpleadoId'] ?? null;
$cajaId     = $input['CajaId']     ?? null;
$detalles   = $input['Detalles']   ?? [];
$pagos      = $input['Pagos']      ?? [];

if (!$empleadoId || !$cajaId) {
    jsonError('Faltan parámetros: EmpleadoId, CajaId');
}
if (!is_array($detalles) || count($detalles) === 0) {
    jsonError('Detalles vacío');
}
if (!is_array($pagos) || count($pagos) === 0) {
    jsonError('Pagos vacío');
}

$detallesJson = json_encode($detalles, JSON_UNESCAPED_UNICODE);
$pagosJson    = json_encode($pagos,    JSON_UNESCAPED_UNICODE);
$ventaId      = 0;

$sql    = "{ CALL sp_RegistrarVenta(?, ?, ?, ?, ?, ?) }";
$params = [
    [$clienteId,    SQLSRV_PARAM_IN],
    [$empleadoId,   SQLSRV_PARAM_IN],
    [$cajaId,       SQLSRV_PARAM_IN],
    [$detallesJson, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_NVARCHAR('max')],
    [$pagosJson,    SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_NVARCHAR('max')],
    [&$ventaId,     SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT],
];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    $errors  = sqlsrv_errors();
    $mensaje = !empty($errors[0]['message']) ? $errors[0]['message'] : 'Error al registrar venta';
    jsonResponse(['success' => false, 'error' => $mensaje, 'detail' => $errors], 400);
}

$row = null;
do {
    $r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if ($r) { $row = $r; break; }
} while (sqlsrv_next_result($stmt));

sqlsrv_free_stmt($stmt);

jsonResponse([
    'success' => true,
    'VentaId' => $ventaId,
    'Total'   => $row['Total'] ?? null,
]);