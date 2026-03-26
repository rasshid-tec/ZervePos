<?php
// agregar_sucursal.php
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

$data      = json_decode(file_get_contents("php://input"), true);
$nombre    = $data["NombreSucursal"];
$direccion = $data["Direccion"];
$ciudad    = $data["Ciudad"];
$telefono  = $data["Telefono"];

$sql    = "INSERT INTO Sucursales (NombreSucursal, Direccion, Ciudad, Telefono) VALUES (?, ?, ?, ?)";
$params = array($nombre, $direccion, $ciudad, $telefono);

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt) {
    echo json_encode(["status" => "ok"]);
} else {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()[0]['message']]);
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
