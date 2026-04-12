<?php
// php/registrar_entrada.php
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

try {
    // Obtener datos del POST
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        throw new Exception("Datos inválidos");
    }

    $sucursalId = intval($data['sucursalId']);
    $empleadoId = intval($data['empleadoId']);
    $tipo = $data['tipo'];
    $motivo = $data['motivo'] ?? null;
    $detalles = $data['detalles'] ?? [];
    $fecha = date('Y-m-d H:i:s');

    // Validaciones
    if (!$sucursalId || !$empleadoId || !$tipo || empty($detalles)) {
        throw new Exception("Faltan datos requeridos");
    }

    // Iniciar transacción
    sqlsrv_begin_transaction($conn);

    // Insertar la entrada
    $sqlEntrada = "INSERT INTO [dbo].[Entradas] (SucursalId, EmpleadoId, Tipo, Motivo, Fecha)
                   VALUES (?, ?, ?, ?, ?)";
    
    $stmtEntrada = sqlsrv_query($conn, $sqlEntrada, [$sucursalId, $empleadoId, $tipo, $motivo, $fecha]);

    if ($stmtEntrada === false) {
        throw new Exception("Error al insertar entrada");
    }

    // Obtener el ID de la entrada insertada
    $sqlGetId = "SELECT @@IDENTITY AS EntradaId";
    $stmtGetId = sqlsrv_query($conn, $sqlGetId);
    $row = sqlsrv_fetch_array($stmtGetId, SQLSRV_FETCH_ASSOC);
    $entradaId = intval($row['EntradaId']);
    sqlsrv_free_stmt($stmtGetId);

    // Insertar detalles y actualizar inventario
    foreach ($detalles as $detalle) {
        $productoId = intval($detalle['productoId']);
        $cantidad = intval($detalle['cantidad']);

        // Insertar en DetalleEntrada
        $sqlDetalle = "INSERT INTO [dbo].[DetalleEntrada] (EntradaId, ProductoId, Cantidad)
                       VALUES (?, ?, ?)";
        
        $stmtDetalle = sqlsrv_query($conn, $sqlDetalle, [$entradaId, $productoId, $cantidad]);

        if ($stmtDetalle === false) {
            throw new Exception("Error al insertar detalle");
        }

        sqlsrv_free_stmt($stmtDetalle);

        // Actualizar o insertar en Inventario
        $sqlUpdateInventario = "MERGE INTO [dbo].[Inventario] AS target
                                USING (SELECT ? AS ProductoId, ? AS SucursalId, ? AS Cantidad) AS source
                                ON target.ProductoId = source.ProductoId AND target.SucursalId = source.SucursalId
                                WHEN MATCHED THEN
                                    UPDATE SET Inventario = Inventario + source.Cantidad
                                WHEN NOT MATCHED THEN
                                    INSERT (ProductoId, SucursalId, Inventario)
                                    VALUES (source.ProductoId, source.SucursalId, source.Cantidad);";

        $stmtInventario = sqlsrv_query($conn, $sqlUpdateInventario, [$productoId, $sucursalId, $cantidad]);

        if ($stmtInventario === false) {
            throw new Exception("Error al actualizar inventario");
        }

        sqlsrv_free_stmt($stmtInventario);
    }

    // Confirmar transacción
    sqlsrv_commit($conn);
    sqlsrv_close($conn);

    echo json_encode([
        "status" => "ok",
        "message" => "Entrada registrada correctamente",
        "data" => [
            "entradaId" => $entradaId
        ]
    ]);

} catch (Exception $e) {
    sqlsrv_rollback($conn);
    sqlsrv_close($conn);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
    exit;
}
?>
