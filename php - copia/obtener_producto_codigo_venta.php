<?php
require_once 'conexion.php';
ob_start();
header("Content-Type: application/json");

$data       = json_decode(file_get_contents("php://input"), true);
$codigo     = trim($data["codigo"]     ?? '');
$sucursalId = intval($data["sucursalId"] ?? 0);
$nivelId    = intval($data["nivelId"]   ?? 4);

if ($codigo === '' || $sucursalId === 0) {
    ob_end_clean();
    echo json_encode(["status" => 0, "mensaje" => "Parámetros inválidos"]);
    exit;
}

$sql    = "{CALL sp_ObtenerProductoCodigoVenta(?, ?, ?)}";
$params = [
    [$codigo,     SQLSRV_PARAM_IN],
    [$sucursalId, SQLSRV_PARAM_IN],
    [$nivelId,    SQLSRV_PARAM_IN],
];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    ob_end_clean();
    echo json_encode(["status" => 0, "debug" => sqlsrv_errors()]);
    exit;
}

$producto = null;
if ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $producto = $fila;
}

ob_end_clean();
echo json_encode([
    "status"   => $producto ? 1 : 0,
    "producto" => $producto
]);
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>