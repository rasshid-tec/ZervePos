<?php
// Forzar que los errores se muestren en pantalla como texto plano
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNÓSTICO DE CONEXIÓN ZERVEPOS ===\n\n";

$serverName = "zervepos-rasshid-2026.database.windows.net";
$connectionOptions = array(
    "Database" => "ZervePos",
    "Uid" => "adminZerve",
    "PWD" => "ContraZervePos1234",
    "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => false,
    "Encrypt" => true
);

echo "1. Intentando conectar a Azure...\n";
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    echo "❌ ERROR DE CONEXIÓN:\n";
    print_r(sqlsrv_errors());
    exit;
}
echo "✅ Conexión exitosa a la Base de Datos.\n\n";

echo "2. Verificando tablas críticas...\n";
$tablas = ['Productos', 'Sucursales', 'Empleados', 'Facturas', 'Entradas', 'DetallesFactura'];

foreach ($tablas as $tabla) {
    $sql = "SELECT TOP 1 * FROM $tabla";
    $stmt = sqlsrv_query($conn, $sql);
    if ($stmt === false) {
        echo "❌ Error en tabla [$tabla]: La tabla NO existe o no tienes permisos.\n";
    } else {
        echo "✅ Tabla [$tabla] detectada correctamente.\n";
    }
}

echo "\n3. Verificando Empleado ID 1 (Administrador)...\n";
$sqlEmp = "SELECT Nombre FROM Empleados WHERE EmpleadoId = 1";
$stmtEmp = sqlsrv_query($conn, $sqlEmp);
if ($row = sqlsrv_fetch_array($stmtEmp, SQLSRV_FETCH_ASSOC)) {
    echo "✅ Empleado ID 1 encontrado: " . $row['Nombre'] . "\n";
} else {
    echo "❌ ERROR: No existe el EmpleadoId = 1. El INSERT de Entradas fallará por FK.\n";
}

sqlsrv_close($conn);
echo "\n=== FIN DEL DIAGNÓSTICO ===";
?>