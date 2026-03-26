<?php
// insertar_cliente.php
header('Content-Type: application/json; charset=utf-8');

$serverName = "zervepos-rasshid-2026.database.windows.net";

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

$data = json_decode(file_get_contents("php://input"), true);

// Validaciones básicas
if (empty($data['ClienteId']) || empty($data['Negocio'])) {
    echo json_encode(["status" => "error", "message" => "El ID y el nombre del negocio son obligatorios."]);
    exit;
}

$sql = "INSERT INTO Clientes 
            (ClienteId, Negocio, NombreCliente, TituloContacto, Direccion, Ciudad, Colonia, CodigoPostal, Telefono, Correo)
        VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$params = array(
    trim($data['ClienteId']),
    trim($data['Negocio']),
    isset($data['NombreCliente'])  ? trim($data['NombreCliente'])  : null,
    isset($data['TituloContacto']) ? trim($data['TituloContacto']) : null,
    isset($data['Direccion'])      ? trim($data['Direccion'])      : null,
    isset($data['Ciudad'])         ? trim($data['Ciudad'])         : null,
    isset($data['Colonia'])        ? trim($data['Colonia'])        : null,
    isset($data['CodigoPostal'])   ? trim($data['CodigoPostal'])   : null,
    isset($data['Telefono'])       ? trim($data['Telefono'])       : null,
    isset($data['Correo'])         ? trim($data['Correo'])         : null
);

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    $error = sqlsrv_errors()[0];
    // Error de clave duplicada (ID ya existe)
    if ($error['code'] == 2627) {
        echo json_encode(["status" => "error", "message" => "Ya existe un cliente con ese ID."]);
    } else {
        echo json_encode(["status" => "error", "message" => $error['message']]);
    }
    exit;
}

echo json_encode(["status" => "ok", "message" => "Cliente registrado correctamente."]);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
