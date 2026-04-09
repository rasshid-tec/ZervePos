<?php


// ...
require_once 'conexion.php';

// Cabeceras para CORS y tipo de contenido
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Manejo de la solicitud pre-flight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Obtener datos del cuerpo de la petición (JSON)
$data = json_decode(file_get_contents("php://input"), true);
$usuario = $data["NombreUsuario"] ?? '';
$contrasena = $data["Contrasena"] ?? '';

$status = 0; // Inicializamos la variable de salida

// Configuración de los parámetros para el SP
$params = [
    [$usuario, SQLSRV_PARAM_IN],
    [$contrasena, SQLSRV_PARAM_IN],
    // Parámetro de salida: pasamos por referencia (&$status) y definimos los tipos explícitos
    [&$status, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT, SQLSRV_SQLTYPE_INT]
];

$sql = "{CALL IniciarSesion(?, ?, ?)}";
$stmt = sqlsrv_query($conn, $sql, $params);


// Validar errores en la ejecución de la consulta
if ($stmt === false) {
    echo json_encode(["status" => 0, "debug" => sqlsrv_errors()]);
    exit;
}

$row = null;

// 1. Leemos los resultados del SELECT (si es que la validación fue exitosa)
if ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $row = $fila;
}

// 2. IMPORTANTE: Avanzamos todos los conjuntos de resultados.
// Esto es obligatorio para que el driver de SQL Server asigne el valor final a la variable $status
while (sqlsrv_next_result($stmt)) {
    // Solo vaciamos el buffer, no necesitamos hacer nada dentro del ciclo
}

// 3. Evaluamos la respuesta final
if ($status === 1 && $row !== null) {
    // Éxito: el SP devolvió Status = 1 y obtuvimos los datos del SELECT
    echo json_encode([
        "status" => 1,
        "UsuarioId" => $row['UsuariosId'],
        "NombreCompleto" => $row['NombreCompleto'],
        "Rol" => $row['Rol'],
        "SucursalId" => $row['SucursalId']
    ]);
} else {
    // Fracaso: el SP devolvió Status = 0 (credenciales inválidas o usuario inactivo)
    echo json_encode([
        "status" => 0,
        "mensaje" => "Credenciales incorrectas o usuario inactivo"
    ]);
}

// 4. Liberamos memoria y cerramos conexión
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>