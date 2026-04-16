<?php
// obtener_clientes.php
header('Content-Type: application/json; charset=utf-8');

$serverName = "zerveposs-rasshid-2026.database.windows.net";

$connectionOptions = array(
    "Database"             => "ZervePos",
    "Uid"                  => "adminZerve",
    "PWD"                  => "ContraZervePos1234",
    "CharacterSet"         => "UTF-8",
    "TrustServerCertificate" => false,
    "Encrypt"              => true
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()[0]['message']]);
    exit;
}

$sql  = "SELECT ClienteId, Negocio, NombreCliente, TituloContacto, Direccion, Ciudad, Colonia, CodigoPostal, Telefono, Correo FROM Clientes ORDER BY Negocio";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()[0]['message']]);
    exit;
}

$clientes = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $clientes[] = $row;
}

echo json_encode(["status" => "ok", "data" => $clientes]);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
