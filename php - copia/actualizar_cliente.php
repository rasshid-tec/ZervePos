<?php
// actualizar_cliente.php
header('Content-Type: application/json; charset=utf-8');

$serverName = "zerveposs-rasshid-2026.database.windows.net";

$connectionOptions = array(
    "Database"               => "ZervePos",
    "Uid"                    => "adminZerve",
    "PWD"                    => "ContraZervePos1234",
    "CharacterSet"           => "UTF-8",
    "TrustServerCertificate" => false,
    "Encrypt"                => true
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()[0]['message']]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['ClienteId']) || empty($data['Negocio'])) {
    echo json_encode(["status" => "error", "message" => "El ID y el nombre del negocio son obligatorios."]);
    exit;
}

$sql = "UPDATE Clientes SET
            Negocio        = ?,
            NombreCliente  = ?,
            TituloContacto = ?,
            Direccion      = ?,
            Ciudad         = ?,
            Colonia        = ?,
            CodigoPostal   = ?,
            Telefono       = ?,
            Correo         = ?
        WHERE ClienteId = ?";

$params = array(
    trim($data['Negocio']),
    isset($data['NombreCliente'])  ? trim($data['NombreCliente'])  : null,
    isset($data['TituloContacto']) ? trim($data['TituloContacto']) : null,
    isset($data['Direccion'])      ? trim($data['Direccion'])      : null,
    isset($data['Ciudad'])         ? trim($data['Ciudad'])         : null,
    isset($data['Colonia'])        ? trim($data['Colonia'])        : null,
    isset($data['CodigoPostal'])   ? trim($data['CodigoPostal'])   : null,
    isset($data['Telefono'])       ? trim($data['Telefono'])       : null,
    isset($data['Correo'])         ? trim($data['Correo'])         : null,
    trim($data['ClienteId'])
);

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()[0]['message']]);
    exit;
}

echo json_encode(["status" => "ok", "message" => "Cliente actualizado correctamente."]);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
