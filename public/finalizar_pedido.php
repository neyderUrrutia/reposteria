<?php
session_start();

require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit;
}

$total = 0;
$items = [];

foreach ($_SESSION['carrito'] as $id => $cantidad) {

    $producto = $db->productos->findOne([
        '_id' => new MongoDB\BSON\ObjectId($id)
    ]);

    if ($producto) {

        $subtotal = $producto->precio * $cantidad;

        $total += $subtotal;

        $items[] = [
            'producto_id' => $producto->_id,
            'nombre' => $producto->nombre,
            'precio' => $producto->precio,
            'cantidad' => $cantidad,
            'subtotal' => $subtotal
        ];
    }
}

// Guardar pedido
$resultado = $db->pedidos->insertOne([

    'usuario_id' => new MongoDB\BSON\ObjectId($_SESSION['usuario_id']),

    'items' => $items,

    'total' => $total,

    'estado' => 'Pendiente',

    'fecha' => new MongoDB\BSON\UTCDateTime()
]);

// Vaciar carrito
$_SESSION['carrito'] = [];

header("Location: pedido_exitoso.php");

exit;