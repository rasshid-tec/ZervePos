<?php
ob_start();
require_once '../conexion.php';
require_once '../helpers.php';

header('Content-Type: application/json');

$sucursalId = isset($_GET['sucursalId']) ? (int)$_GET['sucursalId'] : 0;

if (!$sucursalId) {
    echo jsonError('SucursalId requerido');
    exit;
}

$sql = "SELECT TOP 1 v.VentaId, v.FechaVenta, v.Total,
            CONCAT(em.Nombre, ' ', em.Apellidos) AS Cajero,
            ISNULL(c.NombreCliente, 'Público General') AS Cliente
        FROM Ventas v
        JOIN Caja ca ON v.CajaId = ca.CajaId
        JOIN Empleados em ON v.EmpleadoId = em.EmpleadoId
        LEFT JOIN Clientes c ON v.ClienteId = c.ClienteId
        WHERE ca.SucursalId = ?
        ORDER BY v.VentaId DESC";

$params = [[$sucursalId, SQLSRV_PARAM_IN]];
$stmt = sqlsrv_query($conn, $sql, $params);

if (!$stmt) {
    echo jsonError('Error al obtener último ticket');
    exit;
}

$ticket = null;
if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    if ($row['FechaVenta'] instanceof DateTime) $row['FechaVenta'] = $row['FechaVenta']->format('Y-m-d H:i:s');
    $ticket = $row;
}

echo json_encode(['success' => true, 'ticket' => $ticket]);