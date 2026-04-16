<?php

require_once 'conexion.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$data       = json_decode(file_get_contents("php://input"), true);
$productoId = $data["productoId"] ?? null;

$params = [[$productoId, SQLSRV_PARAM_IN]];
$sql    = "{CALL sp_ObtenerNivelesProducto(?)}";
$stmt   = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(["status" => 0, "debug" => sqlsrv_errors()]);
    exit;
}

$niveles = [];
while ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $niveles[] = $fila;
}

echo json_encode(["status" => 1, "niveles" => $niveles]);
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>