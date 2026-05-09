<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit;
}

// Datos del cliente (puedes mejorar formulario después)
$cliente = $_POST['cliente'] ?? 'Cliente';
$email = $_POST['email'] ?? 'sin_correo';
$total = 0;

foreach ($_SESSION['carrito'] as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

// 1. Guardar pedido
$stmt = $pdo->prepare("INSERT INTO pedidos (cliente, email, total, estado, fecha) VALUES (?, ?, ?, 'pendiente', NOW())");
$stmt->execute([$cliente, $email, $total]);

$pedido_id = $pdo->lastInsertId();

// 2. Guardar productos del pedido
foreach ($_SESSION['carrito'] as $item) {
    $stmt = $pdo->prepare("INSERT INTO pedido_items (pedido_id, producto_id, nombre, cantidad, precio) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $pedido_id,
        $item['id'],
        $item['nombre'],
        $item['cantidad'],
        $item['precio']
    ]);
}

// 3. Vaciar carrito
unset($_SESSION['carrito']);

// 4. Redirigir a página de confirmación
header("Location: pedido_realizado.php?id=$pedido_id");
exit;
?>
