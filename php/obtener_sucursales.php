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
$usuarioId = $data["UsuariosId"];

$status = 0;

$params = [
    [$usuarioId, SQLSRV_PARAM_IN],
    [&$status,   SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT]
];

$sql = "{CALL ObtenerSucursalesUsuario(?, ?)}";
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(["status" => 0, "debug" => sqlsrv_errors()]);
    exit;
}

// 1. Leer el SELECT primero
$sucursales = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $sucursales[] = $row;
}

// 2. Avanzar todos los result sets para que se popule el OUTPUT
while (sqlsrv_next_result($stmt)) {}

// 3. Ahora sí evaluar el status
if ($status === 1) {
    echo json_encode(["status" => 1, "sucursales" => $sucursales]);
} else {
    echo json_encode(["status" => 0]);
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>