<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require 'config.php'; // Tu configuración de PDO, wallets y APIs

// --- Recibir datos ---
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['producto_id'], $data['cantidad'], $data['metodo'])) {
    echo json_encode(["status" => false, "message" => "Datos incompletos"]);
    exit;
}

$producto_id = (int)$data['producto_id'];
$cantidad = (int)$data['cantidad'];
$metodo = $data['metodo'];

// --- Validar producto y stock ---
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$producto_id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo json_encode(["status" => false, "message" => "Producto no encontrado"]);
    exit;
}

if ($producto['stock'] < $cantidad) {
    echo json_encode(["status" => false, "message" => "Stock insuficiente"]);
    exit;
}

// --- Crear transacción pendiente ---
$total = $producto['precio'] * $cantidad;
$stmt = $pdo->prepare("INSERT INTO transacciones (producto_id, cantidad, metodo, estado, total, fecha) VALUES (?, ?, ?, 'pendiente', ?, NOW())");
$stmt->execute([$producto_id, $cantidad, $metodo, $total]);
$transaccion_id = $pdo->lastInsertId();

// --- Generar instrucción de pago según método ---
try {
    $response = ["status" => true, "transaccion_id" => $transaccion_id, "platform" => $metodo];

    switch ($metodo) {
        case "criptomoneda":
            // Generar dirección/QR único para tu wallet
            $direccion = generarDireccionCrypto($producto_id, $cantidad, $transaccion_id); // Función propia
            $response['init_point'] = [
                "direccion" => $direccion,
                "cantidad" => $total,
                "moneda" => "BTC" // o tu cripto
            ];
            break;

        case "paypal":
            // Generar URL de pago en tu cuenta PayPal propia
            $paypalUrl = generarPaypalLink($producto['nombre'], $total, $transaccion_id); // Función propia
            $response['init_point'] = $paypalUrl;
            break;

        case "googlepay":
        case "applepay":
            // Generar token de pago que tu backend captura
            $token = generarTokenWallet($producto['nombre'], $total, $transaccion_id, $metodo); // Función propia
            $response['init_point'] = $token;
            break;

        case "tarjeta":
        case "banco":
            // Generar referencia o formulario de pago interno
            $referencia = generarReferenciaBanco($producto['nombre'], $total, $transaccion_id); // Función propia
            $response['init_point'] = $referencia;
            break;

        default:
            throw new Exception("Método de pago no soportado");
    }

    echo json_encode($response);

} catch(Exception $e) {
    // Marcar transacción como error
    $stmt = $pdo->prepare("UPDATE transacciones SET estado = 'error', mensaje = ? WHERE id = ?");
    $stmt->execute([$e->getMessage(), $transaccion_id]);
    echo json_encode(["status" => false, "message" => $e->getMessage()]);
}

// =========================================================
// Funciones de ejemplo (debes implementar según tu infraestructura)
// =========================================================

function generarDireccionCrypto($producto_id, $cantidad, $transaccion_id) {
    // Aquí llamas a tu wallet para generar dirección única
    return "1ExampleCryptoAddressForTx".$transaccion_id;
}

function generarPaypalLink($producto_nombre, $total, $transaccion_id) {
    // Genera URL de pago propia
    return "https://tupaypal.com/pay?tx=".$transaccion_id."&amount=".$total;
}

function generarTokenWallet($producto_nombre, $total, $transaccion_id, $metodo) {
    // Genera token de pago para Google/Apple Pay
    return "TOKEN_".$metodo."_".$transaccion_id;
}

function generarReferenciaBanco($producto_nombre, $total, $transaccion_id) {
    // Genera referencia bancaria única
    return "REF".$transaccion_id;
}
?>
