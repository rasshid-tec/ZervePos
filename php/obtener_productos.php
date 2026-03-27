<?php
// obtener_productos.php
header('Content-Type: application/json; charset=utf-8');

$serverName = "zervepos-rasshid-2026.database.windows.net";

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
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()]);
    exit;
}

$sql = "SELECT ProductoId, NombreProducto, PrecioUnitario FROM Productos";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()]);
    exit;
}

$productos = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $productos[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

echo json_encode(["status" => "ok", "data" => $productos]);
?>
