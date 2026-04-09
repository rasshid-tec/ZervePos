<?php
header('Content-Type: application/json; charset=utf-8');
// Desactivamos errores visuales para que no rompan el JSON, pero los guardamos en el log
ini_set('display_errors', 0); 
error_reporting(E_ALL);

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
    echo json_encode(["status" => "error", "message" => "Error de conexión a BD"]);
    exit;
}

$body = json_decode(file_get_contents("php://input"), true);

// Validación básica de datos recibidos
if (!$body) {
    echo json_encode(["status" => "error", "message" => "No se recibieron datos (JSON inválido)"]);
    exit;
}

$sucursalId  = (int)$body['sucursalId'];
$tipo        = $body['tipo'];
$descripcion = $body['descripcion'];
$facturado   = $body['facturado'];
$productos   = $body['productos'];
$empleadoId  = 1; 

sqlsrv_begin_transaction($conn);

$facturaId = null;

if ($facturado === 'si') {
    $proveedorId  = (int)$body['proveedorId'];
    $rfc          = $body['rfc'];
    $fechaFactura = $body['fechaFactura']; 
    $totalFactura = floatval($body['totalFactura']);

    // CAMBIO CRÍTICO: Usamos SCOPE_IDENTITY() en lugar de OUTPUT para Azure
    $sqlFactura = "INSERT INTO Facturas (ProveedorId, Total, Fecha, RFC) VALUES (?, ?, ?, ?); SELECT SCOPE_IDENTITY() AS LastId;";
    $stmtF = sqlsrv_query($conn, $sqlFactura, [$proveedorId, $totalFactura, $fechaFactura, $rfc]);

    if ($stmtF === false) {
        sqlsrv_rollback($conn);
        echo json_encode(["status" => "error", "message" => "Error al insertar Factura", "debug" => sqlsrv_errors()]);
        exit;
    }

    // Avanzamos al segundo set de resultados (el SELECT SCOPE_IDENTITY)
    sqlsrv_next_result($stmtF);
    $rowF = sqlsrv_fetch_array($stmtF, SQLSRV_FETCH_ASSOC);
    $facturaId = $rowF['LastId'];

    foreach ($productos as $p) {
        $lote = !empty($p['Lote']) ? $p['Lote'] : null;
        $fVenc = !empty($p['FechaVencimiento']) ? $p['FechaVencimiento'] : null;

        $sqlDet = "INSERT INTO DetallesFactura (FacturaId, ProductoId, Cantidad, PrecioCompra, Lote, FechaVencimiento) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtDet = sqlsrv_query($conn, $sqlDet, [$facturaId, (int)$p['ProductoId'], (int)$p['Cantidad'], floatval($p['PrecioCompra']), $lote, $fVenc]);

        if ($stmtDet === false) {
            sqlsrv_rollback($conn);
            echo json_encode(["status" => "error", "message" => "Error en detalle de factura", "debug" => sqlsrv_errors()]);
            exit;
        }
    }
}

// 2. Insertar Entrada
$sqlEnt = "INSERT INTO Entradas (FacturaId, SucursalId, EmpleadoId, Tipo, Fecha, Descripcion) VALUES (?, ?, ?, ?, GETDATE(), ?)";
$stmtEnt = sqlsrv_query($conn, $sqlEnt, [$facturaId, $sucursalId, $empleadoId, $tipo, $descripcion]);

if ($stmtEnt === false) {
    sqlsrv_rollback($conn);
    echo json_encode(["status" => "error", "message" => "Error al registrar Entrada", "debug" => sqlsrv_errors()]);
    exit;
}

// 3. Actualizar Inventario
foreach ($productos as $p) {
    $cant = (int)$p['Cantidad'];
    $pid  = (int)$p['ProductoId'];

    $sqlInv = "IF EXISTS (SELECT 1 FROM Inventario WHERE ProductoId = ? AND SucursalId = ?)
               UPDATE Inventario SET Inventario = Inventario + ? WHERE ProductoId = ? AND SucursalId = ?
               ELSE
               INSERT INTO Inventario (ProductoId, SucursalId, Inventario) VALUES (?, ?, ?)";
               
    $stmtInv = sqlsrv_query($conn, $sqlInv, [$pid, $sucursalId, $cant, $pid, $sucursalId, $pid, $sucursalId, $cant]);

    if ($stmtInv === false) {
        sqlsrv_rollback($conn);
        echo json_encode(["status" => "error", "message" => "Error al actualizar stock", "debug" => sqlsrv_errors()]);
        exit;
    }
}

sqlsrv_commit($conn);
echo json_encode(["status" => "ok", "message" => "Entrada registrada correctamente."]);
sqlsrv_close($conn);
?>