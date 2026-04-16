<?php
require_once 'conexion.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$data            = json_decode(file_get_contents("php://input"), true);
$nombreCategoria = $data["nombreCategoria"] ?? '';
$status          = 0;

$params = [
    [$nombreCategoria, SQLSRV_PARAM_IN],
    [&$status, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT]
];

$sql  = "{CALL sp_InsertarCategoria(?, ?)}";
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