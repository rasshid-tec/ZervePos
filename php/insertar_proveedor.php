<?php
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
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()[0]['message']]);
    exit;
}

$body         = json_decode(file_get_contents("php://input"), true);
$nombreEmpresa = $body['nombreEmpresa'];
$nombre        = !empty($body['nombre'])    ? $body['nombre']    : null;
$apellidos     = !empty($body['apellidos']) ? $body['apellidos'] : null;
$telefono      = !empty($body['telefono'])  ? $body['telefono']  : null;

$sql  = "INSERT INTO Proveedores (NombreEmpresa, Nombre, Apellidos, Telefono)
         OUTPUT INSERTED.ProveedorId
         VALUES (?, ?, ?, ?)";
$stmt = sqlsrv_query($conn, $sql, [$nombreEmpresa, $nombre, $apellidos, $telefono]);

if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()[0]['message']]);
    exit;
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
echo json_encode(["status" => "ok", "nuevoId" => $row['ProveedorId']]);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>