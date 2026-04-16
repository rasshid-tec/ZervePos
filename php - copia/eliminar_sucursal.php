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
    echo json_encode(["status" => "error", "message" => "JSON inválido"]);
    exit;
}

$sucursalId = (int)($body['SucursalId'] ?? 0);
if (!$sucursalId) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "SucursalId no proporcionado"]);
    sqlsrv_close($conn);
    exit;
}

$status = 0;
$params = [
    [$sucursalId, SQLSRV_PARAM_IN],
    [&$status, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT]
];

$stmt = sqlsrv_query($conn, "{CALL sp_EliminarSucursal(?, ?)}", $params);
if ($stmt === false) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Error al ejecutar SP", "debug" => sqlsrv_errors()]);
    sqlsrv_close($conn);
    exit;
}
while (sqlsrv_next_result($stmt) !== null);

if ($status === -1) {
    ob_clean();
    echo json_encode([
        "status"  => "error",
        "message" => "No se puede eliminar la sucursal porque aún tiene stock en inventario"
    ]);
    sqlsrv_close($conn);
    exit;
}

if ($status !== 1) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "No se pudo eliminar la sucursal"]);
    sqlsrv_close($conn);
    exit;
}

ob_clean();
echo json_encode(["status" => "ok", "message" => "Sucursal eliminada correctamente"]);
sqlsrv_close($conn);
?>