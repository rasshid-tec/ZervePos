<?php
$rawBody = file_get_contents("php://input");
ob_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);

$serverName        = "zerveposs-rasshid-2026.database.windows.net";
$connectionOptions = [
    "Database" => "ZervePos", "Uid" => "adminZerve",
    "PWD" => "ContraZervePos1234", "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => false, "Encrypt" => true
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    ob_clean();
    echo json_encode(["status" => 0, "mensaje" => "Error de conexión"]);
    exit;
}

$body       = json_decode($rawBody, true);
$fechaDesde = $body['fechaDesde'] ?? null;
$fechaHasta = $body['fechaHasta'] ?? null;
$clienteId  = isset($body['clienteId']) && $body['clienteId'] !== '' ? intval($body['clienteId']) : null;

$sql    = "{CALL sp_ReporteClientes(?, ?, ?)}";
$params = [
    [$fechaDesde, SQLSRV_PARAM_IN],
    [$fechaHasta, SQLSRV_PARAM_IN],
    [$clienteId,  SQLSRV_PARAM_IN],
];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    ob_clean();
    echo json_encode(["status" => 0, "debug" => sqlsrv_errors()]);
    sqlsrv_close($conn);
    exit;
}

// Resultset 1: resumen clientes
$clientes = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $clientes[] = $row;
}

// Resultset 2: detalle ventas
sqlsrv_next_result($stmt);
$ventas = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $ventas[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

ob_clean();
echo json_encode([
    "status"   => 1,
    "clientes" => $clientes,
    "ventas"   => $ventas,
]);
?>