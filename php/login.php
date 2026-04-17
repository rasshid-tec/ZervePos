<?php
ob_start();
require_once 'conexion.php';


header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$data       = json_decode(file_get_contents("php://input"), true);
$usuario    = $data["NombreUsuario"] ?? '';
$contrasena = $data["Contrasena"] ?? '';

$status = 0;

// --- PASO 1: AUTENTICACIÓN (Tu SP original) ---
$params = [
    [$usuario,    SQLSRV_PARAM_IN],
    [$contrasena, SQLSRV_PARAM_IN],
    [&$status,    SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT]
];

$sql  = "{CALL IniciarSesion(?, ?, ?)}";
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(["status" => 0, "mensaje" => "Error de base de datos", "debug" => sqlsrv_errors()]);
    exit;
}

$userData = null;
if ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $userData = $fila;
}
while (sqlsrv_next_result($stmt)) {} 

// --- PASO 2: OBTENER CONTEXTO DE SUCURSALES (Si login es exitoso) ---
if ($status === 1 && $userData !== null) {
    $sucursalesPermitidas = [];
    $empId = $userData['EmpleadoId'];
    $rol   = $userData['Rol'];

    // SP que creamos para filtrar por Dueño, Admin o Cajero
    $sqlSuc    = "{CALL sp_ObtenerContextoSucursal(?, ?)}";
    $paramsSuc = [$empId, $rol];
    $stmtSuc   = sqlsrv_query($conn, $sqlSuc, $paramsSuc);

    if ($stmtSuc !== false) {
        while ($fs = sqlsrv_fetch_array($stmtSuc, SQLSRV_FETCH_ASSOC)) {
            $sucursalesPermitidas[] = $fs;
        }
    }

    echo json_encode([
        "status"           => 1,
        "UsuarioId"        => $userData['UsuariosId'],
        "EmpleadoId"       => $empId,
        "NombreCompleto"   => $userData['NombreCompleto'],
        "Rol"              => $rol,
        "SucursalId"       => $userData['SucursalId'], 
        "AccesoSucursales" => $sucursalesPermitidas   
    ]);
} else {
    echo json_encode([
        "status"  => 0,
        "mensaje" => "Usuario o contraseña incorrectos"
    ]);
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>