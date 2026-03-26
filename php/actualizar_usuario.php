<?php
// actualizar_usuario.php
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

if (empty($data['EmpleadoId'])) {
    echo json_encode(["status" => "error", "message" => "EmpleadoId no proporcionado."]);
    exit;
}

sqlsrv_begin_transaction($conn);

try {
    // 1. Actualizar datos del Empleado
    $sqlEmpleado = "UPDATE Empleados SET
                        Nombre          = ?,
                        Apellidos       = ?,
                        FechaNacimiento = ?,
                        Direccion       = ?,
                        Ciudad          = ?,
                        CodigoPostal    = ?,
                        Telefono        = ?,
                        Rol             = ?
                    WHERE EmpleadoId = ?";

    $paramsEmpleado = array(
        trim($data['nombre']),
        trim($data['apellidos']),
        isset($data['fechaNacimiento']) ? trim($data['fechaNacimiento']) : null,
        isset($data['domicilio'])       ? trim($data['domicilio'])       : null,
        isset($data['ciudad'])          ? trim($data['ciudad'])          : null,
        isset($data['codigoPostal'])    ? trim($data['codigoPostal'])    : null,
        isset($data['telefono'])        ? trim($data['telefono'])        : null,
        trim($data['rol']),
        intval($data['EmpleadoId'])
    );

    $stmtEmpleado = sqlsrv_query($conn, $sqlEmpleado, $paramsEmpleado);
    if ($stmtEmpleado === false) {
        throw new Exception("Error al actualizar empleado: " . sqlsrv_errors()[0]['message']);
    }

    // 2. Actualizar NombreUsuario (y Contrasena solo si viene con valor)
    if (!empty($data['contrasena'])) {
        $sqlUsuario = "UPDATE Usuarios SET NombreUsuario = ?, Contrasena = ? WHERE EmpleadoId = ?";
        $paramsUsuario = array(trim($data['usuario']), trim($data['contrasena']), intval($data['EmpleadoId']));
    } else {
        $sqlUsuario = "UPDATE Usuarios SET NombreUsuario = ? WHERE EmpleadoId = ?";
        $paramsUsuario = array(trim($data['usuario']), intval($data['EmpleadoId']));
    }

    $stmtUsuario = sqlsrv_query($conn, $sqlUsuario, $paramsUsuario);
    if ($stmtUsuario === false) {
        $error = sqlsrv_errors()[0];
        // Error de usuario duplicado
        if ($error['code'] == 2627) {
            throw new Exception("El nombre de usuario ya está en uso por otro usuario.");
        }
        throw new Exception("Error al actualizar usuario: " . $error['message']);
    }

    sqlsrv_commit($conn);
    echo json_encode(["status" => "ok", "message" => "Usuario actualizado correctamente."]);

} catch (Exception $e) {
    sqlsrv_rollback($conn);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

sqlsrv_close($conn);
?>
