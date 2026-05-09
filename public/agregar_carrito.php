<?php
session_start();

use MongoDB\BSON\ObjectId;

require_once __DIR__ . '/../includes/db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("Producto inválido");
}

// =============================
// BUSCAR PRODUCTO EN MONGODB
// =============================
try {

    $producto = $db->productos->findOne([
        '_id' => new ObjectId($id)
    ]);

} catch (Exception $e) {
    die("Producto inválido");
}

if (!$producto) {
    die("Producto no encontrado");
}

// =============================
// CREAR CARRITO SI NO EXISTE
// =============================
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// =============================
// AGREGAR PRODUCTO AL CARRITO
// =============================
if (isset($_SESSION['carrito'][$id])) {

    $_SESSION['carrito'][$id]['cantidad']++;

} else {

    $_SESSION['carrito'][$id] = [
        'id' => $id,
        'nombre' => $producto->nombre,
        'precio' => $producto->precio,
        'imagen' => $producto->imagen ?? '',
        'cantidad' => 1
    ];

}

// =============================
// REDIRIGIR AL CARRITO
// =============================
header("Location: carrito.php");
exit;