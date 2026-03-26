<?php
// eliminar_usuario.php
header('Content-Type: application/json; charset=utf-8');

$serverName = "zervepos-rasshid-2026.database.windows.net";

$connectionOptions = array(
    "Database"               => "ZervePos",
    "Uid"                    => "adminZerve",
    "PWD"                    => "ContraZervePos1234",
    "CharacterSet"           => "UTF-8",
    "TrustServerCertificate" => false,
    "Encrypt"                => true
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()[0]['message']]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['UsuariosId'])) {
    echo json_encode(["status" => "error", "message" => "ID de usuario no proporcionado."]);
    exit;
}

$usuarioId = intval($data['UsuariosId']);

// Iniciar transacción: primero obtenemos el EmpleadoId, luego borramos Usuario y Empleado
sqlsrv_begin_transaction($conn);

try {
    // 1. Obtener el EmpleadoId asociado
    $sqlGet = "SELECT EmpleadoId FROM Usuarios WHERE UsuariosId = ?";
    $stmtGet = sqlsrv_query($conn, $sqlGet, array($usuarioId));

    if ($stmtGet === false) {
        throw new Exception("Error al buscar el usuario: " . sqlsrv_errors()[0]['message']);
    }

    $row = sqlsrv_fetch_array($stmtGet, SQLSRV_FETCH_ASSOC);
    if (!$row) {
        throw new Exception("No se encontró el usuario con ese ID.");
    }

    $empleadoId = $row['EmpleadoId'];
    sqlsrv_free_stmt($stmtGet);

    // 2. Eliminar de Usuarios primero (por la FK)
    $sqlDelUsuario = "DELETE FROM Usuarios WHERE UsuariosId = ?";
    $stmtDelUsuario = sqlsrv_query($conn, $sqlDelUsuario, array($usuarioId));

    if ($stmtDelUsuario === false) {
        throw new Exception("Error al eliminar el usuario: " . sqlsrv_errors()[0]['message']);
    }

    // 3. Eliminar de Empleados
    $sqlDelEmpleado = "DELETE FROM Empleados WHERE EmpleadoId = ?";
    $stmtDelEmpleado = sqlsrv_query($conn, $sqlDelEmpleado, array($empleadoId));

    if ($stmtDelEmpleado === false) {
        throw new Exception("Error al eliminar el empleado: " . sqlsrv_errors()[0]['message']);
    }

    sqlsrv_commit($conn);
    echo json_encode(["status" => "ok", "message" => "Usuario eliminado correctamente."]);

} catch (Exception $e) {
    sqlsrv_rollback($conn);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

sqlsrv_close($conn);
?>
