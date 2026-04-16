<?php
// obtener_usuarios.php
header('Content-Type: application/json; charset=utf-8');

$serverName = "zerveposs-rasshid-2026.database.windows.net";

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

$sql = "SELECT 
            u.UsuariosId,
            u.NombreUsuario,
            e.EmpleadoId,
            e.Nombre,
            e.Apellidos,
            e.Rol,
            e.Telefono,
            e.Ciudad,
            e.FechaNacimiento
        FROM Usuarios u
        INNER JOIN Empleados e ON u.EmpleadoId = e.EmpleadoId
        ORDER BY e.Apellidos, e.Nombre";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()[0]['message']]);
    exit;
}

$usuarios = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Convertir FechaNacimiento a string si viene como objeto DateTime
    if ($row['FechaNacimiento'] instanceof DateTime) {
        $row['FechaNacimiento'] = $row['FechaNacimiento']->format('Y-m-d');
    }
    $usuarios[] = $row;
}

echo json_encode(["status" => "ok", "data" => $usuarios]);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
