<?php
// php/registrar_salida.php
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

    // Insertar la salida
    $sqlSalida = "INSERT INTO [dbo].[Salidas] (SucursalId, EmpleadoId, Tipo, Motivo, Fecha)
                  VALUES (?, ?, ?, ?, ?)";
    
    $stmtSalida = sqlsrv_query($conn, $sqlSalida, [$sucursalId, $empleadoId, $tipo, $motivo, $fecha]);

    if ($stmtSalida === false) {
        throw new Exception("Error al insertar salida");
    }

    // Obtener el ID de la salida insertada
    $sqlGetId = "SELECT @@IDENTITY AS SalidaId";
    $stmtGetId = sqlsrv_query($conn, $sqlGetId);
    $row = sqlsrv_fetch_array($stmtGetId, SQLSRV_FETCH_ASSOC);
    $salidaId = intval($row['SalidaId']);
    sqlsrv_free_stmt($stmtGetId);

    // Insertar detalles y actualizar inventario
    foreach ($detalles as $detalle) {
        $productoId = intval($detalle['productoId']);
        $cantidad = intval($detalle['cantidad']);

        // Validar que hay stock disponible
        $sqlCheckStock = "SELECT Inventario FROM [dbo].[Inventario] 
                         WHERE ProductoId = ? AND SucursalId = ?";
        $stmtCheck = sqlsrv_query($conn, $sqlCheckStock, [$productoId, $sucursalId]);
        
        if ($stmtCheck) {
            $rowStock = sqlsrv_fetch_array($stmtCheck, SQLSRV_FETCH_ASSOC);
            sqlsrv_free_stmt($stmtCheck);
            
            if (!$rowStock || $rowStock['Inventario'] < $cantidad) {
                throw new Exception("Stock insuficiente para el producto ID: $productoId");
            }
        }

        // Insertar en DetalleSalida
        $sqlDetalle = "INSERT INTO [dbo].[DetalleSalida] (SalidaId, ProductoId, Cantidad)
                       VALUES (?, ?, ?)";
        
        $stmtDetalle = sqlsrv_query($conn, $sqlDetalle, [$salidaId, $productoId, $cantidad]);

        if ($stmtDetalle === false) {
            throw new Exception("Error al insertar detalle");
        }

        sqlsrv_free_stmt($stmtDetalle);

        // Actualizar inventario
        $sqlUpdateInventario = "UPDATE [dbo].[Inventario] 
                                SET Inventario = Inventario - ?
                                WHERE ProductoId = ? AND SucursalId = ?";

        $stmtInventario = sqlsrv_query($conn, $sqlUpdateInventario, [$cantidad, $productoId, $sucursalId]);

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
        "message" => "Salida registrada correctamente",
        "data" => [
            "salidaId" => $salidaId
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
