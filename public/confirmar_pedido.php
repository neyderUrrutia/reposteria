<?php
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

use MongoDB\BSON\UTCDateTime;

// Si el carrito está vacío, redirigir
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit;
}

// Recibir datos del formulario
$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');

// Validar datos
if ($nombre == "" || $telefono == "") {

    header("Location: checkout.php");
    exit;

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

    $productos[] = [

        'producto_id' => $item['id'],
        'nombre' => $item['nombre'],
        'precio' => $item['precio'],
        'cantidad' => $item['cantidad']

    ];

}

// =========================
// INSERTAR PEDIDO EN MONGO
// =========================

$resultado = $db->pedidos->insertOne([

    'nombre_cliente' => $nombre,
    'telefono' => $telefono,
    'total' => $total,
    'fecha' => new UTCDateTime(),
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
// REDIRIGIR A GRACIAS
// =========================

header("Location: gracias.php?pedido=" . $pedido_id);

exit;
?>