<?php
require_once __DIR__ . '/../includes/db.php';

use MongoDB\BSON\ObjectId;

if (!isset($_GET['id'])) {
    die("ID de pedido no proporcionado.");
}

$pedido_id = $_GET['id'];

// 1️⃣ Obtener datos del pedido
try {
    $pedido = $db->pedidos->findOne(['_id' => new ObjectId($pedido_id)]);
} catch (Exception $e) {
    die("ID de pedido inválido.");
}

if (!$pedido) {
    die("Pedido no encontrado.");
}

// Número de WhatsApp del negocio
$numero_negocio = "573103164314"; // ← Tu número con indicativo 57

// 2️⃣ Crear mensaje
$nombre_cliente = $pedido['nombre_cliente'] ?? $pedido['telefono'] ?? 'Cliente';
$telefono       = $pedido['telefono'] ?? 'No registrado';
$total          = $pedido['total'] ?? 0;
$productos      = $pedido['productos'] ?? [];

$mensaje  = "🧁 *Factura de tu pedido - Atrato Dulce*\n";
$mensaje .= "----------------------------------\n";
$mensaje .= "👤 Cliente: " . $nombre_cliente . "\n";
$mensaje .= "📱 Teléfono: " . $telefono . "\n";
$mensaje .= "🧾 ID Pedido: #" . $pedido_id . "\n";
$mensaje .= "----------------------------------\n";
$mensaje .= "🛒 *Productos:*\n";

foreach ($productos as $item) {
    $nombre_p   = $item['nombre']   ?? 'Producto';
    $cantidad   = $item['cantidad'] ?? 1;
    $precio_u   = $item['precio']   ?? 0;
    $subtotal   = $precio_u * $cantidad;
    $mensaje .= "- " . $nombre_p . " x" . $cantidad . " → $" . number_format($subtotal, 0, ',', '.') . "\n";
}

$mensaje .= "----------------------------------\n";
$mensaje .= "💵 *Total:* $" . number_format($total, 0, ',', '.') . "\n";
$mensaje .= "----------------------------------\n";
$mensaje .= "¡Gracias por comprar en Atrato Dulce! 🩷🍰";

// 3️ Redirigir a WhatsApp
$mensaje_encoded = urlencode($mensaje);
header("Location: https://wa.me/$numero_negocio?text=$mensaje_encoded");
exit;