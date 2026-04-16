<?php
header('Content-Type: application/json; charset=utf-8');

$serverName = "zerveposs-rasshid-2026.database.windows.net";
$connectionOptions = array(
    "Database" => "ZervePos",
    "Uid" => "adminZerve",
    "PWD" => "ContraZervePos1234",
    "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => false,
    "Encrypt" => true
);

$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()[0]['message']]);
    exit;
}

$sql  = "SELECT ProveedorId, NombreEmpresa, Nombre, Apellidos, Telefono FROM Proveedores ORDER BY NombreEmpresa";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()[0]['message']]);
    exit;
}

$proveedores = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $proveedores[] = $row;
}

echo json_encode(["status" => "ok", "data" => $proveedores]);
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>