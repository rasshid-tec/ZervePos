<?php
// 1. El servidor (tu dirección de Azure)
$serverName = "zervepos-rasshid-2026.database.windows.net"; 

// 2. Las opciones con la estructura correcta ("Clave" => "Valor")
$connectionOptions = [
    "Database" => "ZervePos",
    "Uid"      => "adminZerve",
    "PWD"      => "ContraZervePos1234",
    "TrustServerCertificate" => true,
    "CharacterSet" => "UTF-8" // <-- ¡ESTA ES LA MAGIA QUE FALTABA!
];

// 3. Intentamos la conexión
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    echo "No se pudo conectar al servidor.<br />";
    die(print_r(sqlsrv_errors(), true));
}
// Puedes descomentar la siguiente línea para probar que ya funciona
// echo "¡Conexión exitosa!";
?>