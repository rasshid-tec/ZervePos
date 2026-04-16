<?php
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

$body = json_decode(file_get_contents("php://input"), true);
if (!$body) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "JSON inválido"]);
    exit;
}

$sucursalId     = (int)($body['SucursalId']     ?? 0);
$nombreSucursal = trim($body['NombreSucursal']  ?? '');
$direccion      = trim($body['Direccion']       ?? '');
$ciudad         = trim($body['Ciudad']          ?? '');
$telefono       = trim($body['Telefono']        ?? '');

if (!$sucursalId || !$nombreSucursal) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Faltan datos requeridos"]);
    exit;
}

$status = 0;
$params = [
    [$sucursalId,     SQLSRV_PARAM_IN],
    [$nombreSucursal, SQLSRV_PARAM_IN],
    [$direccion,      SQLSRV_PARAM_IN],
    [$ciudad,         SQLSRV_PARAM_IN],
    [$telefono,       SQLSRV_PARAM_IN],
    [&$status, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT]
];

$stmt = sqlsrv_query($conn, "{CALL sp_EditarSucursal(?, ?, ?, ?, ?, ?)}", $params);
if ($stmt === false) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Error al editar sucursal", "debug" => sqlsrv_errors()]);
    sqlsrv_close($conn);
    exit;
}
while (sqlsrv_next_result($stmt) !== null);

ob_clean();
echo json_encode(["status" => "ok", "message" => "Sucursal actualizada correctamente"]);
sqlsrv_close($conn);
?>