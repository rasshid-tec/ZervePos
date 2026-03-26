<?php
header('Content-Type: application/json; charset=utf-8');

// Función auxiliar para obtener mensaje de error de sqlsrv de forma segura
function sqlError() {
    $errors = sqlsrv_errors();
    if ($errors && isset($errors[0]['message'])) {
        return $errors[0]['message'];
    }
    return "Error desconocido en la base de datos.";
}

$serverName = "zervepos-rasshid-2026.database.windows.net";
$connectionOptions = array(
    "Database" => "ZervePos",
    "Uid" => "adminZerve",
    "PWD" => "ContraZervePos1234",
    "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => false,
    "Encrypt" => true
);

$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    echo json_encode(["status" => "error", "message" => sqlError()]);
    exit;
}

$body = json_decode(file_get_contents("php://input"), true);

if (!$body) {
    echo json_encode(["status" => "error", "message" => "No se recibieron datos."]);
    exit;
}

$sucursalId  = (int)$body['sucursalId'];
$tipo        = $body['tipo'];
$descripcion = $body['descripcion'];
$facturado   = $body['facturado'];
$productos   = $body['productos'];
$empleadoId  = (int)sessionStorage ?? 1; // TODO: reemplazar con EmpleadoId real de sesión

sqlsrv_begin_transaction($conn);

$facturaId = null;

// ── 1. Si está facturado, insertar Factura y DetallesFactura ──
if ($facturado === 'si') {
    $proveedorId  = (int)$body['proveedorId'];
    $rfc          = $body['rfc'];
    $fechaFactura = $body['fechaFactura'];
    $totalFactura = floatval($body['totalFactura']);

    $sqlFactura = "INSERT INTO Facturas (ProveedorId, Total, Fecha, RFC)
                   OUTPUT INSERTED.FacturaId
                   VALUES (?, ?, ?, ?)";
    $stmtF = sqlsrv_query($conn, $sqlFactura, [$proveedorId, $totalFactura, $fechaFactura, $rfc]);

    if ($stmtF === false) {
        sqlsrv_rollback($conn);
        echo json_encode(["status" => "error", "message" => sqlError()]);
        exit;
    }

    $rowF      = sqlsrv_fetch_array($stmtF, SQLSRV_FETCH_ASSOC);
    $facturaId = $rowF['FacturaId'];

    // DetallesFactura
    foreach ($productos as $p) {
        $lote             = !empty($p['Lote'])             ? $p['Lote']             : null;
        $fechaVencimiento = !empty($p['FechaVencimiento']) ? $p['FechaVencimiento'] : null;

        $sqlDet  = "INSERT INTO DetallesFactura (FacturaId, ProductoId, Cantidad, PrecioCompra, Lote, FechaVencimiento)
                    VALUES (?, ?, ?, ?, ?, ?)";
        $stmtDet = sqlsrv_query($conn, $sqlDet, [
            $facturaId,
            (int)$p['ProductoId'],
            (int)$p['Cantidad'],
            floatval($p['PrecioCompra']),
            $lote,
            $fechaVencimiento
        ]);

        if ($stmtDet === false) {
            sqlsrv_rollback($conn);
            echo json_encode(["status" => "error", "message" => sqlError()]);
            exit;
        }
    }
}

// ── 2. Insertar Entrada ───────────────────────────────────────
$sqlEnt  = "INSERT INTO Entradas (FacturaId, SucursalId, EmpleadoId, Tipo, Fecha, Descripcion)
            VALUES (?, ?, ?, ?, GETDATE(), ?)";
$stmtEnt = sqlsrv_query($conn, $sqlEnt, [$facturaId, $sucursalId, $empleadoId, $tipo, $descripcion]);

if ($stmtEnt === false) {
    sqlsrv_rollback($conn);
    echo json_encode(["status" => "error", "message" => sqlError()]);
    exit;
}

// ── 3. Actualizar Inventario (upsert) ─────────────────────────
foreach ($productos as $p) {
    $cant = (int)$p['Cantidad'];
    $pid  = (int)$p['ProductoId'];

    $sqlInv = "
        IF EXISTS (SELECT 1 FROM Inventario WHERE ProductoId = ? AND SucursalId = ?)
            UPDATE Inventario SET Inventario = Inventario + ? WHERE ProductoId = ? AND SucursalId = ?
        ELSE
            INSERT INTO Inventario (ProductoId, SucursalId, Inventario) VALUES (?, ?, ?)
    ";
    $stmtInv = sqlsrv_query($conn, $sqlInv, [
        $pid, $sucursalId,
        $cant, $pid, $sucursalId,
        $pid, $sucursalId, $cant
    ]);

    if ($stmtInv === false) {
        sqlsrv_rollback($conn);
        echo json_encode(["status" => "error", "message" => sqlError()]);
        exit;
    }
}

sqlsrv_commit($conn);
echo json_encode(["status" => "ok", "message" => "Entrada registrada correctamente."]);
sqlsrv_close($conn);
?>
