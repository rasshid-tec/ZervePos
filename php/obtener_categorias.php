<?php
require_once 'conexion.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$sql  = "{CALL sp_ObtenerCategorias()}";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo json_encode(["status" => 0, "debug" => sqlsrv_errors()]);
    exit;
}

$categorias = [];
while ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $categorias[] = $fila;
}

echo json_encode(["status" => 1, "categorias" => $categorias]);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>