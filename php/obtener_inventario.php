<?php
// obtener_inventario.php
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

$sucursalId = isset($_GET['sucursalId']) ? intval($_GET['sucursalId']) : 0;

if ($sucursalId === 0) {
    echo json_encode(["status" => "error", "message" => "SucursalId no proporcionado"]);
    exit;
}

$sql = "SELECT i.ProductoId, p.NombreProducto, i.Inventario, p.UnidadesEnStock
        FROM Inventario i
        INNER JOIN Productos p ON (i.ProductoId = p.ProductoId)
        WHERE i.SucursalId = ?";

$stmt = sqlsrv_query($conn, $sql, [$sucursalId]);

if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()]);
    exit;
}

$inventario = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $inventario[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

echo json_encode(["status" => "ok", "data" => $inventario]);
?>
