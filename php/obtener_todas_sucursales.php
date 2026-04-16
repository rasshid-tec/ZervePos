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

$sql  = "SELECT SucursalId, NombreSucursal, Direccion, Ciudad, Telefono, Estado
         FROM Sucursales
         WHERE SucursalId != 0
         ORDER BY NombreSucursal";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => "Error al obtener sucursales", "debug" => sqlsrv_errors()]);
    sqlsrv_close($conn);
    exit;
}

$sucursales = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $sucursales[] = $row;
}

sqlsrv_close($conn);
echo json_encode(["status" => "ok", "data" => $sucursales]);
?>