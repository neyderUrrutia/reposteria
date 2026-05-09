<?php
session_start();

require_once __DIR__ . '/../includes/db.php';

use MongoDB\BSON\UTCDateTime;

// Verificar carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    die("El carrito está vacío.");
}

// Recibir datos
$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');

// Validar datos
if ($nombre === "" || $telefono === "") {
    die("Faltan datos del cliente.");
}

// =========================
// CALCULAR TOTAL
// =========================

$total = 0;

foreach ($_SESSION['carrito'] as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

// =========================
// PREPARAR PRODUCTOS
// =========================

$productos = [];

foreach ($_SESSION['carrito'] as $item) {

    $subtotal = $item['precio'] * $item['cantidad'];

    $productos[] = [

        'producto_id' => $item['id'],
        'nombre' => $item['nombre'],
        'precio' => $item['precio'],
        'cantidad' => $item['cantidad'],
        'subtotal' => $subtotal

    ];
}

// =========================
// INSERTAR PEDIDO EN MONGO
// =========================

$resultado = $db->pedidos->insertOne([

    'nombre_cliente' => $nombre,
    'telefono' => $telefono,
    'fecha' => new UTCDateTime(),
    'total' => $total,
    'estado' => 'Pendiente',
    'productos' => $productos

]);

// Obtener ID del pedido
$pedido_id = (string) $resultado->getInsertedId();

// =========================
// VACIAR CARRITO
// =========================

$_SESSION['carrito'] = [];

// =========================
// REDIRIGIR
// =========================

header("Location: pedido_exitoso.php?id=" . $pedido_id);

exit;