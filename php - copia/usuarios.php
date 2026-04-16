<?php
// php/usuarios.php
header('Content-Type: application/json; charset=utf-8');

$serverName = "zerveposs-rasshid-2026.database.windows.net";
$connectionOptions = array(
    "Database" => "ZervePos",
    "Uid" => "adminZerve",
    "PWD" => "ContraZervePos1234",
    "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => false,
    "Encrypt" => true
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error de conexión a la base de datos",
        "details" => sqlsrv_errors()[0]['message'] ?? "Desconocido"
    ]);
    exit;
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($action) {
        
        case 'obtener':
            obtenerUsuarios($conn);
            break;
            
        case 'crear':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            crearUsuario($conn);
            break;
            
        case 'sucursales-disponibles':
            obtenerSucursalesDisponibles($conn);
            break;
            
        case 'roles-permitidos':
            obtenerRolesPermitidos($conn);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Acción no reconocida: {$action}"
            ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    sqlsrv_close($conn);
}

// ────────────────────────────────────────────────────────────────
// FUNCIÓN: Obtener usuarios con filtro por rol y sucursal
// ────────────────────────────────────────────────────────────────
function obtenerUsuarios($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['empleadoId']) || !isset($data['rol'])) {
        throw new Exception('Faltan parámetros: empleadoId, rol');
    }
    
    $empleadoId = (int)$data['empleadoId'];
    $rol = (string)$data['rol'];
    
    $sql = "DECLARE @Status INT;
            EXEC sp_ObtenerUsuariosConFiltro 
                @EmpleadoIdSolicitante = ?,
                @RolSolicitante = ?,
                @Status = @Status OUTPUT;";
    
    $params = [$empleadoId, $rol];
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        throw new Exception('Error en SP: ' . sqlsrv_errors()[0]['message']);
    }
    
    $usuarios = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $usuarios[] = $row;
    }
    
    echo json_encode([
        "status" => "ok",
        "data" => $usuarios
    ]);
    
    sqlsrv_free_stmt($stmt);
}

// ────────────────────────────────────────────────────────────────
// FUNCIÓN: Crear usuario con validaciones y asignación de sucursales
// ────────────────────────────────────────────────────────────────
function crearUsuario($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Validaciones básicas
    $campos = [
        'empleadoIdSolicitante', 'rolSolicitante', 'nombre', 'apellidos',
        'fechaNacimiento', 'direccion', 'ciudad', 'codigoPostal', 'telefono',
        'rolNuevo', 'nombreUsuario', 'contrasena', 'sucursales'
    ];
    
    foreach ($campos as $campo) {
        if (!isset($data[$campo])) {
            throw new Exception("Falta el parámetro: {$campo}");
        }
    }
    
    // Preparar parámetros
    $empleadoIdSolicitante = (int)$data['empleadoIdSolicitante'];
    $rolSolicitante = (string)$data['rolSolicitante'];
    $nombre = (string)$data['nombre'];
    $apellidos = (string)$data['apellidos'];
    $fechaNacimiento = (string)$data['fechaNacimiento'];
    $direccion = (string)$data['direccion'];
    $ciudad = (string)$data['ciudad'];
    $codigoPostal = (string)$data['codigoPostal'];
    $telefono = (string)$data['telefono'];
    $rolNuevo = (string)$data['rolNuevo'];
    $nombreUsuario = (string)$data['nombreUsuario'];
    $contrasena = (string)$data['contrasena'];
    
    // Convertir array de sucursales a JSON string
    $sucursalesArray = $data['sucursales'] ?? [];
    $sucursalesJSON = json_encode($sucursalesArray);
    
    // Si es Dueño, sucursalesJSON debe ser null
    if ($rolNuevo === 'Dueño') {
        $sucursalesJSON = null;
    }
    
    // Variables de salida para OUTPUT parameters
    $EmpleadoIdNuevo = 0;
    $Status = 0;
    
    // Ejecutar SP con OUTPUT parameters correctamente
    $sql = "DECLARE @EmpleadoIdNuevo INT;
            DECLARE @Status INT;
            
            EXEC sp_CrearUsuarioConSucursales 
                @EmpleadoIdSolicitante = ?,
                @RolSolicitante = ?,
                @Nombre = ?,
                @Apellidos = ?,
                @FechaNacimiento = ?,
                @Direccion = ?,
                @Ciudad = ?,
                @CodigoPostal = ?,
                @Telefono = ?,
                @RolNuevo = ?,
                @NombreUsuario = ?,
                @Contrasena = ?,
                @SucursalesJSON = ?,
                @EmpleadoIdNuevo = @EmpleadoIdNuevo OUTPUT,
                @Status = @Status OUTPUT;
            
            SELECT @EmpleadoIdNuevo AS EmpleadoIdNuevo, @Status AS Status;";
    
    $params = array(
        array($empleadoIdSolicitante, SQLSRV_PARAM_IN),
        array($rolSolicitante, SQLSRV_PARAM_IN),
        array($nombre, SQLSRV_PARAM_IN),
        array($apellidos, SQLSRV_PARAM_IN),
        array($fechaNacimiento, SQLSRV_PARAM_IN),
        array($direccion, SQLSRV_PARAM_IN),
        array($ciudad, SQLSRV_PARAM_IN),
        array($codigoPostal, SQLSRV_PARAM_IN),
        array($telefono, SQLSRV_PARAM_IN),
        array($rolNuevo, SQLSRV_PARAM_IN),
        array($nombreUsuario, SQLSRV_PARAM_IN),
        array($contrasena, SQLSRV_PARAM_IN),
        array($sucursalesJSON, SQLSRV_PARAM_IN)
    );
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        throw new Exception('Error en SP: ' . sqlsrv_errors()[0]['message']);
    }
    
    $resultado = null;
    if (sqlsrv_fetch($stmt)) {
    $resultado = array(
        'EmpleadoIdNuevo' => sqlsrv_get_field($stmt, 0),
        'Status' => sqlsrv_get_field($stmt, 1)
    );
}
    
    sqlsrv_free_stmt($stmt);
    
    if (!$resultado || $resultado['Status'] != 1) {
        error_log("Error: Status=" . ($resultado['Status'] ?? 'null'));     
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Error al crear el usuario"
        ]);
        return;
    }
    
    echo json_encode([
        "status" => "ok",
        "message" => "Usuario creado correctamente",
        "empleadoId" => $resultado['EmpleadoIdNuevo']
    ]);
}

// ────────────────────────────────────────────────────────────────
// FUNCIÓN: Obtener sucursales disponibles para asignar
// ────────────────────────────────────────────────────────────────
function obtenerSucursalesDisponibles($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['empleadoId']) || !isset($data['rol'])) {
        throw new Exception('Faltan parámetros: empleadoId, rol');
    }
    
    $empleadoId = (int)$data['empleadoId'];
    $rol = (string)$data['rol'];
    
    $sql = "DECLARE @Status INT;
            EXEC sp_ObtenerSucursalesDisponibles 
                @EmpleadoIdSolicitante = ?,
                @RolSolicitante = ?,
                @Status = @Status OUTPUT;";
    
    $params = [$empleadoId, $rol];
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        throw new Exception('Error en SP: ' . sqlsrv_errors()[0]['message']);
    }
    
    $sucursales = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $sucursales[] = $row;
    }
    
    echo json_encode([
        "status" => "ok",
        "data" => $sucursales
    ]);
    
    sqlsrv_free_stmt($stmt);
}

// ────────────────────────────────────────────────────────────────
// FUNCIÓN: Obtener roles que el solicitante puede crear
// ────────────────────────────────────────────────────────────────
function obtenerRolesPermitidos($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['rol'])) {
        throw new Exception('Falta parámetro: rol');
    }
    
    $rol = (string)$data['rol'];
    
    $rolesPermitidos = [];
    
    if ($rol === 'Dueño') {
    $rolesPermitidos = ['Dueño', 'Administrador', 'Cajero'];
    } elseif ($rol === 'Administrador') {
        $rolesPermitidos = ['Cajero'];  // Solo Cajero
    } else {
        $rolesPermitidos = [];
    }
    
    echo json_encode([
        "status" => "ok",
        "data" => $rolesPermitidos
    ]);
}
?>
