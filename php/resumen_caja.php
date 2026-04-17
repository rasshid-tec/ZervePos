<?php
$rawBody = file_get_contents("php://input");
ob_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);

$serverName        = "zerveposs-rasshid-2026.database.windows.net";
$connectionOptions = [
    "Database" => "ZervePos", "Uid" => "adminZerve",
    "PWD" => "ContraZervePos1234", "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => false, "Encrypt" => true
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    ob_clean();
    echo json_encode(["status" => 0, "mensaje" => "Error de conexión"]);
    exit;
}

$body   = json_decode($rawBody, true);
$cajaId = intval($body['cajaId'] ?? 0);

if (!$cajaId) {
    ob_clean();
    echo json_encode(["status" => 0, "mensaje" => "CajaId no proporcionado"]);
    sqlsrv_close($conn);
    exit;
}

$sql    = "{CALL sp_ResumenCaja(?)}";
$params = [[$cajaId, SQLSRV_PARAM_IN]];
$stmt   = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    ob_clean();
    echo json_encode(["status" => 0, "debug" => sqlsrv_errors()]);
    sqlsrv_close($conn);
    exit;
}

// Resultset 1: datos generales
$general = null;
if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $general = $row;
    if (isset($general['FechaApertura']) && $general['FechaApertura'] instanceof DateTime) {
        $general['FechaApertura'] = $general['FechaApertura']->format('Y-m-d H:i:s');
    }
}

// Resultset 2: ventas por método de pago
sqlsrv_next_result($stmt);
$metodos = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $metodos[] = $row;
}

// Resultset 3: movimientos
sqlsrv_next_result($stmt);
$movimientos = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    if (isset($row['FechaMovimiento']) && $row['FechaMovimiento'] instanceof DateTime) {
        $row['FechaMovimiento'] = $row['FechaMovimiento']->format('Y-m-d H:i:s');
    }
    $movimientos[] = $row;
}

// Resultset 4: totales
sqlsrv_next_result($stmt);
$totales = null;
if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $totales = $row;
}

while (sqlsrv_next_result($stmt) !== null);
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

ob_clean();
echo json_encode([
    "status"       => 1,
    "general"      => $general,
    "metodos"      => $metodos,
    "movimientos"  => $movimientos,
    "totales"      => $totales
]);
?>