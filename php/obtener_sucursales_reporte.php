<?php
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

$sql  = "SELECT SucursalId, NombreSucursal FROM dbo.Sucursales WHERE Estado = 1 ORDER BY NombreSucursal";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    ob_clean();
    echo json_encode(["status" => 0, "debug" => sqlsrv_errors()]);
    sqlsrv_close($conn);
    exit;
}

$sucursales = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $sucursales[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

ob_clean();
echo json_encode(["status" => 1, "sucursales" => $sucursales]);
?>