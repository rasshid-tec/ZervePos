<?php
// php/obtener_inventario_completo.php
header('Content-Type: application/json; charset=utf-8');

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
    echo json_encode([
        "status" => "error",
        "message" => "Error de conexión a la base de datos",
        "details" => sqlsrv_errors()
    ]);
    exit;
}

try {
    // Ejecutar el SP
    $sql = "EXEC sp_ObtenerInventarioCompleto";
    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        throw new Exception(json_encode(sqlsrv_errors()));
    }

    $inventario = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $inventario[] = $row;
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);

    echo json_encode([
        "status" => "ok",
        "data" => $inventario
    ]);

} catch (Exception $e) {
    sqlsrv_close($conn);
    echo json_encode([
        "status" => "error",
        "message" => "Error al obtener inventario",
        "details" => $e->getMessage()
    ]);
    exit;
}
?>
