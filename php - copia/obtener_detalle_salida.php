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

$salidaId = intval($_GET['salidaId'] ?? 0);
if (!$salidaId) {
    echo json_encode(["status" => "error", "message" => "SalidaId no proporcionado"]);
    sqlsrv_close($conn);
    exit;
}

// ── 1. Datos generales de la salida ──────────────────────────
$sqlSalida = "
    SELECT
        s.SalidaId,
        s.Tipo,
        s.Motivo,
        s.Fecha,
        src.NombreSucursal  AS NombreSucursal,
        dst.NombreSucursal  AS NombreSucursalDestino,
        emp.Nombre + ' ' + emp.Apellidos AS NombreEmpleado
    FROM Salidas s
    INNER JOIN Sucursales  src ON src.SucursalId  = s.SucursalId
    INNER JOIN Empleados   emp ON emp.EmpleadoId  = s.EmpleadoId
    LEFT  JOIN Sucursales  dst ON dst.SucursalId  = s.SucursalDestino
    WHERE s.SalidaId = ?
";

$stmtS = sqlsrv_query($conn, $sqlSalida, [$salidaId]);
if ($stmtS === false) {
    echo json_encode(["status" => "error", "message" => "Error al obtener salida", "debug" => sqlsrv_errors()]);
    sqlsrv_close($conn);
    exit;
}

$salida = sqlsrv_fetch_array($stmtS, SQLSRV_FETCH_ASSOC);
if (!$salida) {
    echo json_encode(["status" => "error", "message" => "Salida no encontrada"]);
    sqlsrv_close($conn);
    exit;
}

if ($salida['Fecha'] instanceof DateTime) {
    $salida['Fecha'] = $salida['Fecha']->format('d/m/Y H:i');
}

// ── 2. Productos del detalle ──────────────────────────────────
$sqlDetalle = "
    SELECT
        p.NombreProducto,
        d.Cantidad,
        p.PrecioCompra,
        (d.Cantidad * p.PrecioCompra) AS Subtotal
    FROM DetallesSalida d
    INNER JOIN Productos p ON p.ProductoId = d.ProductoId
    WHERE d.SalidaId = ?
    ORDER BY p.NombreProducto
";

$stmtD = sqlsrv_query($conn, $sqlDetalle, [$salidaId]);
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
    "data"   => ["salida" => $salida, "detalles" => $detalles]
]);
?>