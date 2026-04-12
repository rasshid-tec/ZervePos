<?php
// php/obtener_productos_sucursal.php
header('Content-Type: application/json; charset=utf-8');

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
    echo json_encode([
        "status" => "error",
        "message" => "Error de conexión a la base de datos"
    ]);
    exit;
}

$sucursalId = isset($_GET['sucursalId']) ? intval($_GET['sucursalId']) : 0;

if ($sucursalId === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "SucursalId no proporcionado"
    ]);
    exit;
}

try {
    // Obtener productos de la sucursal con su inventario actual
    $sql = "SELECT 
                p.ProductoId,
                p.NombreProducto,
                p.PrecioUnitario,
                i.Inventario
            FROM [dbo].[Productos] p
            LEFT JOIN [dbo].[Inventario] i ON p.ProductoId = i.ProductoId AND i.SucursalId = ?
            ORDER BY p.NombreProducto";

    $stmt = sqlsrv_query($conn, $sql, [$sucursalId]);

    if ($stmt === false) {
        throw new Exception("Error en la consulta");
    }

    $productos = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $productos[] = $row;
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);

    echo json_encode([
        "status" => "ok",
        "data" => $productos
    ]);

} catch (Exception $e) {
    sqlsrv_close($conn);
    echo json_encode([
        "status" => "error",
        "message" => "Error al obtener productos: " . $e->getMessage()
    ]);
    exit;
}
?>
