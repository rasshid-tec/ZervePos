<?php
require_once 'conexion.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$data = json_decode(file_get_contents("php://input"), true);
$sku  = $data["sku"] ?? '';

$params = [[$sku, SQLSRV_PARAM_IN]];
$sql    = "{CALL sp_ObtenerProductoPorSKU(?)}";
$stmt   = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(["status" => 0, "debug" => sqlsrv_errors()]);
    exit;
}

$producto = null;
if ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $producto = $fila;
}

echo json_encode(["status" => $producto ? 1 : 0, "producto" => $producto]);
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>