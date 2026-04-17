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

$body       = json_decode($rawBody, true);
$cajaId     = intval($body['cajaId']     ?? 0);
$montoFinal = floatval($body['montoFinal'] ?? 0);

if (!$cajaId) {
    ob_clean();
    echo json_encode(["status" => 0, "mensaje" => "CajaId no proporcionado"]);
    sqlsrv_close($conn);
    exit;
}

$sql    = "{CALL sp_CerrarCaja(?, ?)}";
$params = [
    [$cajaId,     SQLSRV_PARAM_IN],
    [$montoFinal, SQLSRV_PARAM_IN],
];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    $errors  = sqlsrv_errors();
    $mensaje = !empty($errors[0]['message']) ? $errors[0]['message'] : 'Error al cerrar caja';
    ob_clean();
    echo json_encode(["status" => 0, "mensaje" => $mensaje, "debug" => $errors]);
    sqlsrv_close($conn);
    exit;
}

$resumen = null;
if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $resumen = $row;
}

while (sqlsrv_next_result($stmt) !== null);
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

ob_clean();
echo json_encode([
    "status"  => 1,
    "resumen" => $resumen
]);
?>