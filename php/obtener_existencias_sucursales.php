<?php
// obtener_existencias_sucursales.php
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

// Traer todas las combinaciones producto-sucursal ordenadas por producto y sucursal
$sql = "SELECT p.NombreProducto, s.NombreSucursal, i.Inventario
        FROM Inventario i
        INNER JOIN Productos  p ON i.ProductoId  = p.ProductoId
        INNER JOIN Sucursales s ON i.SucursalId  = s.SucursalId
        ORDER BY p.NombreProducto, s.NombreSucursal";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()]);
    exit;
}

$existencias = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $existencias[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

echo json_encode(["status" => "ok", "data" => $existencias]);
?>
