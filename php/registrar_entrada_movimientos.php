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

$sucursalId = (int)($body['sucursalId'] ?? 0);
$empleadoId = (int)($body['empleadoId'] ?? 0);
$tipo       = $body['tipo']      ?? '';
$motivo     = $body['motivo']    ?? '';
$detalles   = $body['detalles']  ?? [];
$facturado  = (bool)($body['facturado'] ?? false);

if (!$sucursalId || !$empleadoId || !$tipo || empty($detalles)) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Faltan datos requeridos"]);
    exit;
}

sqlsrv_begin_transaction($conn);

$facturaId = null;

// ── 1. Si viene con factura ───────────────────────────────────────────
if ($facturado) {
    $proveedorId  = (int)($body['proveedorId']  ?? 0);
    $rfc          = trim($body['rfc']           ?? '');
    $fechaFactura = $body['fechaFactura']        ?? date('Y-m-d');

    if (!$proveedorId || !$rfc) {
        sqlsrv_rollback($conn);
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Faltan datos de factura (proveedor o RFC)"]);
        exit;
    }

    // Calcular total desde los detalles
    $total = array_reduce($detalles, function ($sum, $d) {
        return $sum + ((float)($d['precioCompra'] ?? 0) * (int)($d['cantidad'] ?? 0));
    }, 0.0);

    // sp_RegistrarFactura
    $statusFact = 0;
    $paramsFact = [
        [$proveedorId,  SQLSRV_PARAM_IN],
        [$total,        SQLSRV_PARAM_IN],
        [$fechaFactura, SQLSRV_PARAM_IN],
        [$rfc,          SQLSRV_PARAM_IN],
        [&$statusFact, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT]
    ];

    $stmtFact = sqlsrv_query($conn, "{CALL sp_RegistrarFactura(?, ?, ?, ?, ?)}", $paramsFact);
    if ($stmtFact === false) {
        sqlsrv_rollback($conn);
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Error al registrar factura", "debug" => sqlsrv_errors()]);
        exit;
    }

    $rowFact   = sqlsrv_fetch_array($stmtFact, SQLSRV_FETCH_ASSOC);
    $facturaId = $rowFact['FacturaId'];
    while (sqlsrv_next_result($stmtFact) !== null);

    if ($statusFact !== 1 || !$facturaId) {
        sqlsrv_rollback($conn);
        ob_clean();
        echo json_encode(["status" => "error", "message" => "No se pudo obtener el ID de la factura"]);
        exit;
    }

    // sp_InsertarDetalleFactura por cada producto
    foreach ($detalles as $d) {
        $pid      = (int)($d['productoId']  ?? 0);
        $cant     = (int)($d['cantidad']    ?? 0);
        $precio   = (float)($d['precioCompra'] ?? 0);
        $lote     = !empty($d['lote'])            ? $d['lote']            : null;
        $fechaVenc = !empty($d['fechaVencimiento']) ? $d['fechaVencimiento'] : null;

        $statusDF = 0;
        $paramsDF = [
            [$facturaId, SQLSRV_PARAM_IN],
            [$pid,        SQLSRV_PARAM_IN],
            [$cant,       SQLSRV_PARAM_IN],
            [$precio,     SQLSRV_PARAM_IN],
            [$lote,       SQLSRV_PARAM_IN],
            [$fechaVenc,  SQLSRV_PARAM_IN],
            [&$statusDF, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT]
        ];

        $stmtDF = sqlsrv_query($conn, "{CALL sp_InsertarDetalleFactura(?, ?, ?, ?, ?, ?, ?)}", $paramsDF);
        if ($stmtDF === false) {
            sqlsrv_rollback($conn);
            ob_clean();
            echo json_encode(["status" => "error", "message" => "Error en DetallesFactura", "debug" => sqlsrv_errors()]);
            exit;
        }
        while (sqlsrv_next_result($stmtDF) !== null);

        if ($statusDF !== 1) {
            sqlsrv_rollback($conn);
            ob_clean();
            echo json_encode(["status" => "error", "message" => "Error al insertar detalle de factura"]);
            exit;
        }
    }
}

// ── 2. sp_RegistrarEntradaMovimiento ─────────────────────────────────
$statusEnt = 0;
$fid       = $facturaId; // null si no hay factura
$paramsEnt = [
    [$fid,        SQLSRV_PARAM_IN],
    [$sucursalId, SQLSRV_PARAM_IN],
    [$empleadoId, SQLSRV_PARAM_IN],
    [$tipo,       SQLSRV_PARAM_IN],
    [$motivo,     SQLSRV_PARAM_IN],
    [&$statusEnt, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT]
];

$stmtEnt = sqlsrv_query($conn, "{CALL sp_RegistrarEntradaMovimiento(?, ?, ?, ?, ?, ?)}", $paramsEnt);
if ($stmtEnt === false) {
    sqlsrv_rollback($conn);
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Error al registrar entrada", "debug" => sqlsrv_errors()]);
    exit;
}

$rowEnt    = sqlsrv_fetch_array($stmtEnt, SQLSRV_FETCH_ASSOC);
$entradaId = $rowEnt['EntradaId'];
while (sqlsrv_next_result($stmtEnt) !== null);

if ($statusEnt !== 1 || !$entradaId) {
    sqlsrv_rollback($conn);
    ob_clean();
    echo json_encode(["status" => "error", "message" => "No se pudo obtener el ID de la entrada"]);
    exit;
}

// ── 3. sp_InsertarDetalleEntrada (trigger actualiza inventario) ───────
foreach ($detalles as $d) {
    $pid  = (int)($d['productoId'] ?? 0);
    $cant = (int)($d['cantidad']   ?? 0);

    if (!$pid || $cant <= 0) {
        sqlsrv_rollback($conn);
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Producto o cantidad inválido"]);
        exit;
    }

    $statusDE = 0;
    $paramsDE = [
        [$entradaId, SQLSRV_PARAM_IN],
        [$pid,        SQLSRV_PARAM_IN],
        [$cant,       SQLSRV_PARAM_IN],
        [&$statusDE, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT]
    ];

    $stmtDE = sqlsrv_query($conn, "{CALL sp_InsertarDetalleEntrada(?, ?, ?, ?)}", $paramsDE);
    if ($stmtDE === false) {
        sqlsrv_rollback($conn);
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Error en DetalleEntrada", "debug" => sqlsrv_errors()]);
        exit;
    }
    while (sqlsrv_next_result($stmtDE) !== null);

    if ($statusDE !== 1) {
        sqlsrv_rollback($conn);
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Error al insertar detalle de entrada"]);
        exit;
    }
}

sqlsrv_commit($conn);
ob_clean();
echo json_encode([
    "status"  => "ok",
    "message" => "Entrada registrada correctamente",
    "data"    => ["entradaId" => $entradaId]
]);
sqlsrv_close($conn);
?>