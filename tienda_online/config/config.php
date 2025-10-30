<?php
// Permitir llamadas desde cualquier origen
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// --- Configuración de base de datos ---
$dbHost = 'localhost';        // Host de la base de datos           
$dbName = 'tienda_online';      // Nombre de tu base de datos
$dbUser = 'usuario';          // Usuario de la base de datos
$dbPass = 'contraseña';       // Contraseña de la base de datos
$dbCharset = 'utf8mb4';

try {
    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=$dbCharset";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "Error de conexión a la base de datos: " . $e->getMessage()
    ]);
    exit;
}

// --- Configuración de pagos / wallets (ejemplo) ---
$walletApiKey = 'TU_API_KEY_CRIPTO';
$paypalClientId = 'TU_PAYPAL_CLIENT_ID';
$paypalSecret = 'TU_PAYPAL_SECRET';
// Puedes añadir GooglePay / ApplePay / bancos aquí también

?>
