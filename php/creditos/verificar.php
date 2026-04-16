<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../helpers.php';

$clienteId = $_GET['ClienteId'] ?? null;
if (!$clienteId) jsonError('Falta parámetro ClienteId');

$sql = "{ CALL sp_VerificarCredito(?) }";
$params = [[$clienteId, SQLSRV_PARAM_IN]];

$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) jsonError('Error al verificar crédito', 500);

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($stmt);

jsonResponse(['success' => true, 'credito' => $row]);