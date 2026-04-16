<?php
require_once 'conexion.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$sql  = "{CALL sp_ObtenerNivelesCliente()}";
$stmt = sqlsrv_query($conn, $sql);

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