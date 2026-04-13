<?php
require_once 'conexion.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$data = json_decode(file_get_contents("php://input"), true);

$productoId        = $data["productoId"]        ?? null;
$categoriaId       = $data["categoriaId"]       ?? null;
$nombreProducto    = $data["nombreProducto"]    ?? '';
$marca             = $data["marca"]             ?? '';
$tamanio           = $data["tamanio"]           ?? '';
$presentacion      = $data["presentacion"]      ?? '';
$unidadesPorVenta  = $data["unidadesPorVenta"]  ?? null;
$codigoBarras      = $data["codigoBarras"]      ?? '';
$sku               = $data["sku"]               ?? null;
$precioCompra      = $data["precioCompra"]      ?? 0;
$stockMinimo       = $data["stockMinimo"]       ?? 0;
$pctMayoreo        = $data["pctMayoreo"]        ?? 0;
$pctMedioMayoreo   = $data["pctMedioMayoreo"]   ?? 0;
$pctMenudeo        = $data["pctMenudeo"]        ?? 0;
$pctPublicoGeneral = $data["pctPublicoGeneral"] ?? 0;
$status            = 0;

$params = [
    [$productoId,        SQLSRV_PARAM_IN],
    [$categoriaId,       SQLSRV_PARAM_IN],
    [$nombreProducto,    SQLSRV_PARAM_IN],
    [$marca,             SQLSRV_PARAM_IN],
    [$tamanio,           SQLSRV_PARAM_IN],
    [$presentacion,      SQLSRV_PARAM_IN],
    [$unidadesPorVenta,  SQLSRV_PARAM_IN],
    [$codigoBarras,      SQLSRV_PARAM_IN],
    [$sku,               SQLSRV_PARAM_IN],
    [$precioCompra,      SQLSRV_PARAM_IN],
    [$stockMinimo,       SQLSRV_PARAM_IN],
    [$pctMayoreo,        SQLSRV_PARAM_IN],
    [$pctMedioMayoreo,   SQLSRV_PARAM_IN],
    [$pctMenudeo,        SQLSRV_PARAM_IN],
    [$pctPublicoGeneral, SQLSRV_PARAM_IN],
    [&$status, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT]
];

$sql  = "{CALL sp_EditarProducto(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
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