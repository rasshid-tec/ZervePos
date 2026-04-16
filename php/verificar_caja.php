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
    echo json_encode(["status" => "error", "message" => "Error de conexión"]);
    exit;
}

$body       = json_decode($rawBody, true);
$sucursalId = (int)($body['sucursalId'] ?? 0);

if (!$sucursalId) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "SucursalId no proporcionado"]);
    sqlsrv_close($conn);
    exit;
}

$sql    = "SELECT TOP 1 CajaId FROM Caja WHERE SucursalId = ? AND Estado = 1;
$stmt   = sqlsrv_query($conn, $sql, [$sucursalId]);

if ($stmt === false) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Error al verificar caja", "debug" => sqlsrv_errors()]);
    sqlsrv_close($conn);
    exit;
}

$row    = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$cajaId = $row['CajaId'] ?? null;

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

ob_clean();
echo json_encode([
    "status"      => "ok",
    "cajaAbierta" => $cajaId !== null,
    "CajaId"      => $cajaId
]);
?>