<?php
require_once 'conexion.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$data      = json_decode(file_get_contents("php://input"), true);
$usuario   = $data["NombreUsuario"] ?? '';
$contrasena = $data["Contrasena"] ?? '';

$status = 0;

$params = [
    [$usuario,    SQLSRV_PARAM_IN],
    [$contrasena, SQLSRV_PARAM_IN],
    [&$status,    SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT]
];

$sql  = "{CALL IniciarSesion(?, ?, ?)}";
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(["status" => 0, "debug" => sqlsrv_errors()]);
    exit;
}

$row = null;

if ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $row = $fila;
}

while (sqlsrv_next_result($stmt)) {}

if ($status === 1 && $row !== null) {
    echo json_encode([
        "status"         => 1,
        "UsuarioId"      => $row['UsuariosId'],
        "EmpleadoId"     => $row['EmpleadoId'],
        "NombreCompleto" => $row['NombreCompleto'],
        "Rol"            => $row['Rol'],
        "SucursalId"     => $row['SucursalId']
    ]);
} else {
    echo json_encode([
        "status"  => 0,
        "mensaje" => "Credenciales incorrectas o usuario inactivo"
    ]);
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>