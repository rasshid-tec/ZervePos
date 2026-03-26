<?php
// actualizar_producto.php
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

$data   = json_decode(file_get_contents("php://input"), true);
$id     = $data["ProductoId"];
$nombre = $data["NombreProducto"];
$precio = $data["PrecioUnitario"];

// Validar que el precio no sea negativo
if ($precio <= 0) {
    echo json_encode(["status" => "error", "message" => "El precio debe ser mayor a cero."]);
    exit;
}

$sql    = "UPDATE Productos SET NombreProducto = ?, PrecioUnitario = ? WHERE ProductoId = ?";
$params = array($nombre, $precio, $id);

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt) {
    echo json_encode(["status" => "ok"]);
} else {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()[0]['message']]);
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
