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
$creditoId  = intval($body['creditoId']  ?? 0);
$empleadoId = intval($body['empleadoId'] ?? 0);
$cajaId     = intval($body['cajaId']     ?? 0);
$montoPago  = floatval($body['montoPago'] ?? 0);
$metodoPago = trim($body['metodoPago']   ?? '');

if (!$creditoId || !$empleadoId || !$cajaId || $montoPago <= 0 || !$metodoPago) {
    ob_clean();
    echo json_encode(["status" => 0, "mensaje" => "Faltan parámetros"]);
    sqlsrv_close($conn);
    exit;
}

$abonoId = 0;
$sql     = "{CALL sp_RegistrarPagoCredito(?, ?, ?, ?, ?, ?)}";
$params  = [
    [$creditoId,  SQLSRV_PARAM_IN],
    [$empleadoId, SQLSRV_PARAM_IN],
    [$cajaId,     SQLSRV_PARAM_IN],
    [$montoPago,  SQLSRV_PARAM_IN],
    [$metodoPago, SQLSRV_PARAM_IN],
    [&$abonoId,   SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT],
];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    $errors  = sqlsrv_errors();
    $mensaje = !empty($errors[0]['message']) ? $errors[0]['message'] : 'Error al registrar pago';
    ob_clean();
    echo json_encode(["status" => 0, "mensaje" => $mensaje, "debug" => $errors]);
    sqlsrv_close($conn);
    exit;
}

$pago = null;
if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $pago = $row;
}

while (sqlsrv_next_result($stmt) !== null);
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

ob_clean();
echo json_encode([
    "status" => 1,
    "pago"   => $pago
]);
?>