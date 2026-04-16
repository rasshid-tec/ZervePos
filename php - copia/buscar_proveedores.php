<?php
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
    echo json_encode(["status" => "error", "message" => "Error de conexión"]);
    exit;
}

$busqueda = trim($_GET['busqueda'] ?? '');
if (!$busqueda) {
    echo json_encode(["status" => "ok", "data" => []]);
    sqlsrv_close($conn);
    exit;
}

$stmt = sqlsrv_query($conn, "{CALL sp_BuscarProveedores(?)}", [[$busqueda, SQLSRV_PARAM_IN]]);
if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => "Error al buscar", "debug" => sqlsrv_errors()]);
    sqlsrv_close($conn);
    exit;
}

$proveedores = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $proveedores[] = $row;
}

sqlsrv_close($conn);
echo json_encode(["status" => "ok", "data" => $proveedores]);
?>