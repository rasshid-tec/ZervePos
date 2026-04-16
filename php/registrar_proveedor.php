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

$nombreEmpresa = trim($body['NombreEmpresa'] ?? '');
$nombre        = trim($body['Nombre']        ?? '');
$apellidos     = trim($body['Apellidos']     ?? '');
$telefono      = trim($body['Telefono']      ?? '');

if (!$nombreEmpresa) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "NombreEmpresa es requerido"]);
    exit;
}

$status = 0;
$params = [
    [$nombreEmpresa, SQLSRV_PARAM_IN],
    [$nombre,        SQLSRV_PARAM_IN],
    [$apellidos,     SQLSRV_PARAM_IN],
    [$telefono,      SQLSRV_PARAM_IN],
    [&$status, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT]
];

$stmt = sqlsrv_query($conn, "{CALL sp_RegistrarProveedor(?, ?, ?, ?, ?)}", $params);
if ($stmt === false) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Error al registrar proveedor", "debug" => sqlsrv_errors()]);
    sqlsrv_close($conn);
    exit;
}

$row         = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$proveedorId = $row['ProveedorId'];
while (sqlsrv_next_result($stmt) !== null);

ob_clean();
echo json_encode(["status" => "ok", "data" => ["proveedorId" => $proveedorId]]);
sqlsrv_close($conn);
?>