<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require 'config.php'; // PDO, credenciales, wallets, APIs

// --- Recibir datos de confirmación ---
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['transaccion_id'], $data['metodo'], $data['status'])) {
    echo json_encode(["status" => false, "message" => "Datos incompletos"]);
    exit;
}

$transaccion_id = (int)$data['transaccion_id'];
$metodo = $data['metodo'];
$status = strtolower($data['status']); // aprobado, pendiente, rechazado, cancelado
$mensaje = isset($data['mensaje']) ? $data['mensaje'] : null;

// --- Validar transacción ---
$stmt = $pdo->prepare("SELECT * FROM transacciones WHERE id = ?");
$stmt->execute([$transaccion_id]);
$transaccion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaccion) {
    echo json_encode(["status" => false, "message" => "Transacción no encontrada"]);
    exit;
}

// --- Mapear estado interno ---
$estadoInterno = 'pendiente';
switch ($status) {
    case "aprobado":
    case "completado":
        $estadoInterno = "completada";
        break;
    case "rechazado":
    case "error":
        $estadoInterno = "fallida";
        break;
    case "pendiente":
        $estadoInterno = "pendiente";
        break;
    case "cancelado":
        $estadoInterno = "cancelada";
        break;
    default:
        $estadoInterno = "pendiente";
}

// --- Actualizar transacción ---
$stmt = $pdo->prepare("UPDATE transacciones SET estado = ?, mensaje = ? WHERE id = ?");
$stmt->execute([$estadoInterno, $mensaje, $transaccion_id]);

// --- Actualizar stock si fue completada ---
if ($estadoInterno === "completada") {
    $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
    $stmt->execute([$transaccion['cantidad'], $transaccion['producto_id']]);
}

echo json_encode([
    "status" => true,
    "transaccion_id" => $transaccion_id,
    "nuevo_estado" => $estadoInterno
]);
?>
