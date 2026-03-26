<?php
// eliminar_producto.php
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

$data = json_decode(file_get_contents("php://input"), true);
$id   = $data["ProductoId"];

// Verificar que el inventario del producto sea 0 en TODAS las sucursales
$sqlCheck = "SELECT SUM(Inventario) AS TotalInventario FROM Inventario WHERE ProductoId = ?";
$stmtCheck = sqlsrv_query($conn, $sqlCheck, array($id));

if ($stmtCheck === false) {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()]);
    exit;
}

$row = sqlsrv_fetch_array($stmtCheck, SQLSRV_FETCH_ASSOC);
$totalInventario = intval($row["TotalInventario"]);

if ($totalInventario > 0) {
    echo json_encode(["status" => "error", "message" => "No se puede eliminar. El producto aún tiene $totalInventario unidad(es) en inventario."]);
    exit;
}

// Si el inventario es 0 en todas las sucursales, proceder a eliminar
$sql    = "DELETE FROM Productos WHERE ProductoId = ?";
$stmt   = sqlsrv_query($conn, $sql, array($id));

if ($stmt) {
    echo json_encode(["status" => "ok"]);
} else {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()[0]['message']]);
}

sqlsrv_free_stmt($stmtCheck);
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
