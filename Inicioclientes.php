<?php
// ─────────────────────────────────────────
//  clientes.php  —  ZervePos
//  API REST para la tabla Clientes
//  Métodos: GET | POST | PUT | DELETE
// ─────────────────────────────────────────

header("Content-Type: application/json; charset=UTF-8");

// ── Conexión a SQL Server ──────────────────
$servidor  = "localhost";          // Cambia por tu servidor de Azure SQL
$baseDatos = "ZervePos";
$usuario   = "tu_usuario";        // Cambia por tu usuario
$contrasena = "tu_contrasena";    // Cambia por tu contraseña

$connStr = "Server=$servidor; Database=$baseDatos; UID=$usuario; PWD=$contrasena; Encrypt=yes; TrustServerCertificate=no;";

$conn = sqlsrv_connect($servidor, [
    "Database"             => $baseDatos,
    "UID"                  => $usuario,
    "PWD"                  => $contrasena,
    "CharacterSet"         => "UTF-8",
    "Encrypt"              => true,
    "TrustServerCertificate" => false
]);

if (!$conn) {
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "No se pudo conectar a la base de datos.",
        "detalle" => print_r(sqlsrv_errors(), true)
    ]);
    exit;
}

// ── Leer método HTTP ───────────────────────
$metodo = $_SERVER["REQUEST_METHOD"];

// Para PUT y DELETE, leemos el body JSON
$body = [];
if ($metodo === "POST" || $metodo === "PUT" || $metodo === "DELETE") {
    $rawBody = file_get_contents("php://input");
    $body    = json_decode($rawBody, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "JSON inválido en el cuerpo de la petición."]);
        sqlsrv_close($conn);
        exit;
    }
}

// ─────────────────────────────────────────
//  GET — Obtener todos los clientes
// ─────────────────────────────────────────
if ($metodo === "GET") {

    $sql  = "SELECT ClienteId, Negocio, NombreCliente, TituloContacto,
                    Direccion, Ciudad, Colonia, CodigoPostal, Telefono, Correo
             FROM Clientes
             ORDER BY Negocio ASC";

    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Error al consultar clientes."]);
        sqlsrv_close($conn);
        exit;
    }

    $clientes = [];
    while ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Limpiamos espacios de NCHAR(5)
        $fila["ClienteId"] = trim($fila["ClienteId"]);
        $clientes[] = $fila;
    }

    echo json_encode(["status" => "ok", "clientes" => $clientes]);
    sqlsrv_free_stmt($stmt);

// ─────────────────────────────────────────
//  POST — Crear nuevo cliente
// ─────────────────────────────────────────
} elseif ($metodo === "POST") {

    // Validaciones
    if (empty($body["ClienteId"]) || empty($body["Negocio"])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "ClienteId y Negocio son obligatorios."]);
        sqlsrv_close($conn);
        exit;
    }

    $id = strtoupper(trim($body["ClienteId"]));
    if (strlen($id) > 5) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "El ClienteId no puede tener más de 5 caracteres."]);
        sqlsrv_close($conn);
        exit;
    }

    // Verificar que el ID no exista ya
    $sqlCheck = "SELECT COUNT(*) AS total FROM Clientes WHERE ClienteId = ?";
    $stmtCheck = sqlsrv_query($conn, $sqlCheck, [$id]);
    $rowCheck  = sqlsrv_fetch_array($stmtCheck, SQLSRV_FETCH_ASSOC);
    if ($rowCheck["total"] > 0) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "Ya existe un cliente con ese ID."]);
        sqlsrv_free_stmt($stmtCheck);
        sqlsrv_close($conn);
        exit;
    }
    sqlsrv_free_stmt($stmtCheck);

    $sql = "INSERT INTO Clientes
                (ClienteId, Negocio, NombreCliente, TituloContacto,
                 Direccion, Ciudad, Colonia, CodigoPostal, Telefono, Correo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $params = [
        $id,
        $body["Negocio"]        ?? null,
        $body["NombreCliente"]  ?? null,
        $body["TituloContacto"] ?? null,
        $body["Direccion"]      ?? null,
        $body["Ciudad"]         ?? null,
        $body["Colonia"]        ?? null,
        $body["CodigoPostal"]   ?? null,
        $body["Telefono"]       ?? null,
        $body["Correo"]         ?? null
    ];

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Error al crear el cliente."]);
        sqlsrv_close($conn);
        exit;
    }

    echo json_encode(["status" => "ok", "message" => "Cliente creado correctamente."]);
    sqlsrv_free_stmt($stmt);

// ─────────────────────────────────────────
//  PUT — Actualizar cliente existente
// ─────────────────────────────────────────
} elseif ($metodo === "PUT") {

    if (empty($body["ClienteId"]) || empty($body["Negocio"])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "ClienteId y Negocio son obligatorios."]);
        sqlsrv_close($conn);
        exit;
    }

    $id = strtoupper(trim($body["ClienteId"]));

    // Verificar que el cliente exista
    $sqlCheck = "SELECT COUNT(*) AS total FROM Clientes WHERE ClienteId = ?";
    $stmtCheck = sqlsrv_query($conn, $sqlCheck, [$id]);
    $rowCheck  = sqlsrv_fetch_array($stmtCheck, SQLSRV_FETCH_ASSOC);
    if ($rowCheck["total"] === 0) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "No se encontró el cliente con ese ID."]);
        sqlsrv_free_stmt($stmtCheck);
        sqlsrv_close($conn);
        exit;
    }
    sqlsrv_free_stmt($stmtCheck);

    $sql = "UPDATE Clientes SET
                Negocio        = ?,
                NombreCliente  = ?,
                TituloContacto = ?,
                Direccion      = ?,
                Ciudad         = ?,
                Colonia        = ?,
                CodigoPostal   = ?,
                Telefono       = ?,
                Correo         = ?
            WHERE ClienteId = ?";

    $params = [
        $body["Negocio"]        ?? null,
        $body["NombreCliente"]  ?? null,
        $body["TituloContacto"] ?? null,
        $body["Direccion"]      ?? null,
        $body["Ciudad"]         ?? null,
        $body["Colonia"]        ?? null,
        $body["CodigoPostal"]   ?? null,
        $body["Telefono"]       ?? null,
        $body["Correo"]         ?? null,
        $id
    ];

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Error al actualizar el cliente."]);
        sqlsrv_close($conn);
        exit;
    }

    echo json_encode(["status" => "ok", "message" => "Cliente actualizado correctamente."]);
    sqlsrv_free_stmt($stmt);

// ─────────────────────────────────────────
//  DELETE — Eliminar cliente
// ─────────────────────────────────────────
} elseif ($metodo === "DELETE") {

    if (empty($body["ClienteId"])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "ClienteId es obligatorio para eliminar."]);
        sqlsrv_close($conn);
        exit;
    }

    $id = strtoupper(trim($body["ClienteId"]));

    // Verificar que no tenga ventas asociadas antes de eliminar
    $sqlCheck = "SELECT COUNT(*) AS total FROM Ventas WHERE ClienteId = ?";
    $stmtCheck = sqlsrv_query($conn, $sqlCheck, [$id]);
    $rowCheck  = sqlsrv_fetch_array($stmtCheck, SQLSRV_FETCH_ASSOC);
    if ($rowCheck["total"] > 0) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "No se puede eliminar: el cliente tiene ventas registradas."]);
        sqlsrv_free_stmt($stmtCheck);
        sqlsrv_close($conn);
        exit;
    }
    sqlsrv_free_stmt($stmtCheck);

    // También verificar créditos
    $sqlCheckC = "SELECT COUNT(*) AS total FROM Creditos WHERE ClienteId = ?";
    $stmtCheckC = sqlsrv_query($conn, $sqlCheckC, [$id]);
    $rowCheckC  = sqlsrv_fetch_array($stmtCheckC, SQLSRV_FETCH_ASSOC);
    if ($rowCheckC["total"] > 0) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "No se puede eliminar: el cliente tiene créditos registrados."]);
        sqlsrv_free_stmt($stmtCheckC);
        sqlsrv_close($conn);
        exit;
    }
    sqlsrv_free_stmt($stmtCheckC);

    $sql  = "DELETE FROM Clientes WHERE ClienteId = ?";
    $stmt = sqlsrv_query($conn, $sql, [$id]);

    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Error al eliminar el cliente."]);
        sqlsrv_close($conn);
        exit;
    }

    $filasAfectadas = sqlsrv_rows_affected($stmt);
    if ($filasAfectadas === 0) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "No se encontró el cliente con ese ID."]);
    } else {
        echo json_encode(["status" => "ok", "message" => "Cliente eliminado correctamente."]);
    }

    sqlsrv_free_stmt($stmt);

// ─────────────────────────────────────────
//  Método no permitido
// ─────────────────────────────────────────
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método HTTP no permitido."]);
}

sqlsrv_close($conn);
?>
