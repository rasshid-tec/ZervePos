<?php
header('Content-Type: application/json');
require_once 'conexion.php';

$data = json_decode(file_get_contents('php://input'), true);
$empleadoId = isset($data['empleadoId']) ? (int)$data['empleadoId'] : 0;
$rol = isset($data['rol']) ? $data['rol'] : '';

$status = 0;
$sql = "{ CALL sp_ObtenerSucursalesPorEmpleado(?, ?, ?) }";
$params = array(
    array($empleadoId, SQLSRV_PARAM_IN),
    array($rol, SQLSRV_PARAM_IN),
    array(&$status, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT)
);

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(['status' => 0, 'sucursales' => [], 'error' => sqlsrv_errors()]);
    exit;
}

$sucursales = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $sucursales[] = [
        'sucursalId' => (int)$row['SucursalId'],
        'nombre'     => $row['NombreSucursal'],
        'direccion' => $row['Direccion']
    ];
}

while (sqlsrv_next_result($stmt) !== null) {}

echo json_encode(['status' => $status, 'sucursales' => $sucursales]);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);