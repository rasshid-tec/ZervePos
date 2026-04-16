<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

$serverName = "zerveposs-rasshid-2026.database.windows.net";
$connectionOptions = [
    "Database" => "ZervePos", "Uid" => "adminZerve",
    "PWD" => "ContraZervePos1234", "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => false, "Encrypt" => true
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Error de conexión"]);
    exit;
}

$body = json_decode(file_get_contents("php://input"), true);
if (!$body) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "JSON inválido"]);
    exit;
}

$sucursalId      = (int)($body['sucursalId']        ?? 0);
$empleadoId      = (int)($body['empleadoId']        ?? 0);
$tipo            = $body['tipo']                    ?? '';
$motivo          = $body['motivo']                  ?? '';
$sucursalDestino = !empty($body['sucursalDestino']) ? (int)$body['sucursalDestino'] : null;
$detalles        = $body['detalles']                ?? [];

if (!$sucursalId || !$empleadoId || !$tipo || empty($detalles)) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Faltan datos requeridos"]);
    exit;
}

sqlsrv_begin_transaction($conn);

// ── 1. sp_RegistrarSalidaMovimiento ──────────────────────────
$statusSal = 0;
$paramsSal = [
    [$sucursalId,      SQLSRV_PARAM_IN],
    [$empleadoId,      SQLSRV_PARAM_IN],
    [$tipo,            SQLSRV_PARAM_IN],
    [$motivo,          SQLSRV_PARAM_IN],
    [$sucursalDestino, SQLSRV_PARAM_IN],
    [&$statusSal, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT]
];

$stmtSal = sqlsrv_query($conn, "{CALL sp_RegistrarSalidaMovimiento(?, ?, ?, ?, ?, ?)}", $paramsSal);
if ($stmtSal === false) {
    sqlsrv_rollback($conn);
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Error al registrar salida", "debug" => sqlsrv_errors()]);
    exit;
}

$rowSal   = sqlsrv_fetch_array($stmtSal, SQLSRV_FETCH_ASSOC);
$salidaId = $rowSal['SalidaId'];
while (sqlsrv_next_result($stmtSal) !== null);

if ($statusSal !== 1 || !$salidaId) {
    sqlsrv_rollback($conn);
    ob_clean();
    echo json_encode(["status" => "error", "message" => "No se pudo obtener el ID de la salida"]);
    exit;
}

// ── 2. sp_InsertarDetalleSalida (trigger actualiza inventario) ─
foreach ($detalles as $d) {
    $pid  = (int)($d['productoId'] ?? 0);
    $cant = (int)($d['cantidad']   ?? 0);

    if (!$pid || $cant <= 0) {
        sqlsrv_rollback($conn);
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Producto o cantidad inválido"]);
        exit;
    }

    $statusDet = 0;
    $paramsDet = [
        [$salidaId, SQLSRV_PARAM_IN],
        [$pid,       SQLSRV_PARAM_IN],
        [$cant,      SQLSRV_PARAM_IN],
        [&$statusDet, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT]
    ];

    $stmtDet = sqlsrv_query($conn, "{CALL sp_InsertarDetalleSalida(?, ?, ?, ?)}", $paramsDet);
    if ($stmtDet === false) {
        sqlsrv_rollback($conn);
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Error en DetallesSalida", "debug" => sqlsrv_errors()]);
        exit;
    }
    while (sqlsrv_next_result($stmtDet) !== null);

    if ($statusDet === -1) {
        sqlsrv_rollback($conn);
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Stock insuficiente para el producto ID $pid"]);
        exit;
    }

    if ($statusDet !== 1) {
        sqlsrv_rollback($conn);
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Error al insertar detalle de salida"]);
        exit;
    }
}

sqlsrv_commit($conn);
ob_clean();
echo json_encode([
    "status"  => "ok",
    "message" => "Salida registrada correctamente",
    "data"    => ["salidaId" => $salidaId]
]);
sqlsrv_close($conn);
?>