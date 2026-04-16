<?php
$rawBody = file_get_contents("php://input");
ob_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

$serverName = "zerveposs-rasshid-2026.database.windows.net";
$connectionOptions = [
    "Database" => "ZervePos", "Uid" => "adminZerve",
    "PWD" => "ContraZervePos1234", "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => false, "Encrypt" => true
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Error de conexión"]);
    exit;
}

$body = json_decode($rawBody, true);
if (!$body) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "JSON inválido", "raw" => $rawBody]);
    exit;
}

$sucursalId   = (int)($body['sucursalId']    ?? 0);
$empleadoId   = (int)($body['empleadoId']    ?? 0);
$montoInicial = (float)($body['montoInicial'] ?? 0);

$statusSP = 0;
$params   = [
    [$sucursalId,   SQLSRV_PARAM_IN],
    [$empleadoId,   SQLSRV_PARAM_IN],
    [$montoInicial, SQLSRV_PARAM_IN],
    [&$statusSP, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT]
];

$stmt = sqlsrv_query($conn, "{CALL sp_AbrirCaja(?, ?, ?, ?)}", $params);
if ($stmt === false) {
    ob_clean();
    echo json_encode([
        "status"      => "error",
        "message"     => "Error al abrir caja",
        "debug"       => sqlsrv_errors(),
        "sucursalId"  => $sucursalId,
        "empleadoId"  => $empleadoId,
        "monto"       => $montoInicial
    ]);
    sqlsrv_close($conn);
    exit;
}

$row    = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$cajaId = $row['CajaId'] ?? null;
while (sqlsrv_next_result($stmt) !== null);

ob_clean();
echo json_encode([
    "status"   => "debug",
    "statusSP" => $statusSP,
    "cajaId"   => $cajaId,
    "datos"    => ["sucursalId" => $sucursalId, "empleadoId" => $empleadoId, "monto" => $montoInicial]
]);
sqlsrv_close($conn);
?>