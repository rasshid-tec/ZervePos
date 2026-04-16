<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);

$serverName = "zerveposs-rasshid-2026.database.windows.net";
$connectionOptions = [
    "Database" => "ZervePos", "Uid" => "adminZerve",
    "PWD" => "ContraZervePos1234", "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => false, "Encrypt" => true
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    echo json_encode(["status" => "error", "message" => "Error de conexión"]);
    exit;
}

$entradaId = intval($_GET['entradaId'] ?? 0);
if (!$entradaId) {
    echo json_encode(["status" => "error", "message" => "EntradaId no proporcionado"]);
    sqlsrv_close($conn);
    exit;
}

// ── 1. Datos generales de la entrada ─────────────────────────
$sqlEntrada = "
    SELECT 
        e.EntradaId,
        e.Tipo,
        e.Fecha,
        e.Descripcion,
        s.NombreSucursal,
        emp.Nombre + ' ' + emp.Apellidos AS NombreEmpleado,
        f.FacturaId,
        f.RFC,
        f.Total AS TotalFactura,
        f.Fecha AS FechaFactura,
        p.NombreEmpresa AS Proveedor
    FROM Entradas e
    INNER JOIN Sucursales   s   ON s.SucursalId   = e.SucursalId
    INNER JOIN Empleados    emp ON emp.EmpleadoId  = e.EmpleadoId
    LEFT  JOIN Facturas     f   ON f.FacturaId     = e.FacturaId
    LEFT  JOIN Proveedores  p   ON p.ProveedorId   = f.ProveedorId
    WHERE e.EntradaId = ?
";

$stmtE = sqlsrv_query($conn, $sqlEntrada, [$entradaId]);
if ($stmtE === false) {
    echo json_encode(["status" => "error", "message" => "Error al obtener entrada", "debug" => sqlsrv_errors()]);
    sqlsrv_close($conn);
    exit;
}

$entrada = sqlsrv_fetch_array($stmtE, SQLSRV_FETCH_ASSOC);
if (!$entrada) {
    echo json_encode(["status" => "error", "message" => "Entrada no encontrada"]);
    sqlsrv_close($conn);
    exit;
}

// Formatear fecha
if ($entrada['Fecha'] instanceof DateTime) {
    $entrada['Fecha'] = $entrada['Fecha']->format('d/m/Y H:i');
}
if ($entrada['FechaFactura'] instanceof DateTime) {
    $entrada['FechaFactura'] = $entrada['FechaFactura']->format('d/m/Y');
}

// ── 2. Productos del detalle ──────────────────────────────────
$sqlDetalle = "
    SELECT 
        p.NombreProducto,
        d.Cantidad,
        p.PrecioCompra,
        (d.Cantidad * p.PrecioCompra) AS Subtotal
    FROM DetalleEntrada d
    INNER JOIN Productos p ON p.ProductoId = d.ProductoId
    WHERE d.EntradaId = ?
    ORDER BY p.NombreProducto
";

$stmtD = sqlsrv_query($conn, $sqlDetalle, [$entradaId]);
if ($stmtD === false) {
    echo json_encode(["status" => "error", "message" => "Error al obtener detalle", "debug" => sqlsrv_errors()]);
    sqlsrv_close($conn);
    exit;
}

$detalles = [];
while ($row = sqlsrv_fetch_array($stmtD, SQLSRV_FETCH_ASSOC)) {
    $detalles[] = $row;
}

sqlsrv_close($conn);
echo json_encode([
    "status" => "ok",
    "data"   => [
        "entrada"  => $entrada,
        "detalles" => $detalles
    ]
]);
?>