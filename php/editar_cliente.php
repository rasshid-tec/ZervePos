<?php
require_once 'conexion.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$data             = json_decode(file_get_contents("php://input"), true);
$clienteId        = $data["clienteId"]        ?? null;
$negocio          = $data["negocio"]          ?? '';
$nombreCliente    = $data["nombreCliente"]    ?? '';
$tituloContacto   = $data["tituloContacto"]   ?? '';
$direccion        = $data["direccion"]        ?? '';
$ciudad           = $data["ciudad"]           ?? '';
$colonia          = $data["colonia"]          ?? '';
$codigoPostal     = $data["codigoPostal"]     ?? '';
$telefono         = $data["telefono"]         ?? '';
$correo           = $data["correo"]           ?? '';
$creditoPermitido = $data["creditoPermitido"] ?? 0;
$categoriaId      = $data["categoriaId"]      ?? null;
$status           = 0;

$params = [
    [$clienteId,        SQLSRV_PARAM_IN],
    [$negocio,          SQLSRV_PARAM_IN],
    [$nombreCliente,    SQLSRV_PARAM_IN],
    [$tituloContacto,   SQLSRV_PARAM_IN],
    [$direccion,        SQLSRV_PARAM_IN],
    [$ciudad,           SQLSRV_PARAM_IN],
    [$colonia,          SQLSRV_PARAM_IN],
    [$codigoPostal,     SQLSRV_PARAM_IN],
    [$telefono,         SQLSRV_PARAM_IN],
    [$correo,           SQLSRV_PARAM_IN],
    [$creditoPermitido, SQLSRV_PARAM_IN],
    [$categoriaId,      SQLSRV_PARAM_IN],
    [&$status, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT]
];

$sql  = "{CALL sp_EditarCliente(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(["status" => 0, "debug" => sqlsrv_errors()]);
    exit;
}

while (sqlsrv_next_result($stmt)) {}
echo json_encode(["status" => $status]);
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>