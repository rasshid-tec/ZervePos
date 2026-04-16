<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

$serverName = "zerveposs-rasshid-2026.database.windows.net";
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
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Error de conexión"]);
    exit;
}

$body            = json_decode(file_get_contents("php://input"), true);
$sucursalId      = (int)$body['sucursalId'];
$tipo            = $body['tipo'];
$motivo          = $body['motivo'];
$productos       = $body['productos'];
$sucursalDestino = isset($body['sucursalDestino']) ? (int)$body['sucursalDestino'] : null;
$empleadoId      = 1;

sqlsrv_begin_transaction($conn);

// ── 1. Insertar Salida ────────────────────────────────────────
$sqlSalida = "INSERT INTO Salidas (SucursalId, EmpleadoId, Tipo, Motivo, Fecha)
              VALUES (?, ?, ?, ?, GETDATE());
              SELECT SCOPE_IDENTITY() AS NuevoId;";

$stmtS = sqlsrv_query($conn, $sqlSalida, [$sucursalId, $empleadoId, $tipo, $motivo]);

if ($stmtS === false) {
    sqlsrv_rollback($conn);
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Error en Salidas", "debug" => sqlsrv_errors()]);
    exit;
}

sqlsrv_next_result($stmtS);
$rowS     = sqlsrv_fetch_array($stmtS, SQLSRV_FETCH_ASSOC);
$salidaId = $rowS['NuevoId'];

// ── 2. Detalles e Inventario ──────────────────────────────────
foreach ($productos as $p) {
    $pid  = (int)$p['ProductoId'];
    $cant = (int)$p['Cantidad'];

    // Verificar stock suficiente
    $sqlCheck  = "SELECT Inventario FROM Inventario WHERE ProductoId = ? AND SucursalId = ?";
    $stmtCheck = sqlsrv_query($conn, $sqlCheck, [$pid, $sucursalId]);
    $rowCheck  = sqlsrv_fetch_array($stmtCheck, SQLSRV_FETCH_ASSOC);

    if (!$rowCheck || $rowCheck['Inventario'] < $cant) {
        sqlsrv_rollback($conn);
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Stock insuficiente del producto ID $pid"]);
        exit;
    }

    // Insertar DetallesSalida
    $sqlDet  = "INSERT INTO DetallesSalida (SalidaId, ProductoId, Cantidad) VALUES (?, ?, ?)";
    $stmtDet = sqlsrv_query($conn, $sqlDet, [$salidaId, $pid, $cant]);

    if ($stmtDet === false) {
        sqlsrv_rollback($conn);
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Error en DetallesSalida", "debug" => sqlsrv_errors()]);
        exit;
    }

    // Restar inventario en sucursal origen
    $sqlResta  = "UPDATE Inventario SET Inventario = Inventario - ? WHERE ProductoId = ? AND SucursalId = ?";
    $stmtResta = sqlsrv_query($conn, $sqlResta, [$cant, $pid, $sucursalId]);

    if ($stmtResta === false) {
        sqlsrv_rollback($conn);
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Error al actualizar inventario origen", "debug" => sqlsrv_errors()]);
        exit;
    }

    // Si es transferencia, sumar en sucursal destino
    if ($tipo === 'TRANSFERENCIA' && $sucursalDestino) {
        $sqlTransf = "
            IF EXISTS (SELECT 1 FROM Inventario WHERE ProductoId = ? AND SucursalId = ?)
                UPDATE Inventario SET Inventario = Inventario + ? WHERE ProductoId = ? AND SucursalId = ?
            ELSE
                INSERT INTO Inventario (ProductoId, SucursalId, Inventario) VALUES (?, ?, ?)
        ";
        $stmtTransf = sqlsrv_query($conn, $sqlTransf, [
            $pid, $sucursalDestino,
            $cant, $pid, $sucursalDestino,
            $pid, $sucursalDestino, $cant
        ]);

        if ($stmtTransf === false) {
            sqlsrv_rollback($conn);
            ob_clean();
            echo json_encode(["status" => "error", "message" => "Error en transferencia", "debug" => sqlsrv_errors()]);
            exit;
        }
    }
}

sqlsrv_commit($conn);
ob_clean();
echo json_encode(["status" => "ok", "message" => "Salida registrada con éxito."]);
sqlsrv_close($conn);
?>