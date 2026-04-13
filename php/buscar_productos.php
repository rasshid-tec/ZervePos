<?php
require_once 'conexion.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$data     = json_decode(file_get_contents("php://input"), true);
$busqueda = $data["busqueda"] ?? '';

$params = [[$busqueda, SQLSRV_PARAM_IN]];
$sql    = "{CALL sp_BuscarProductos(?)}";
$stmt   = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(["status" => 0, "debug" => sqlsrv_errors()]);
    exit;
}

$productos = [];
while ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $productos[] = $fila;
}

echo json_encode(["status" => 1, "productos" => $productos]);
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>