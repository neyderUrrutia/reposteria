<?php
session_start();

require_once __DIR__ . '/../includes/db.php';

use MongoDB\BSON\UTCDateTime;

// Si el carrito está vacío
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit;
}

$mensaje = "";

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);

    if ($nombre == "" || $telefono == "") {

        $mensaje = "❌ Debes llenar todos los campos.";

    } else {

        // =========================
        // CALCULAR TOTAL
        // =========================

        $total = 0;

        foreach ($_SESSION['carrito'] as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        // =========================
        // CREAR ARRAY DE PRODUCTOS
        // =========================

        $productos = [];

        foreach ($_SESSION['carrito'] as $item) {

            $productos[] = [
                'producto_id' => $item['id'],
                'nombre'      => $item['nombre'],
                'cantidad'    => $item['cantidad'],
                'precio'      => $item['precio']
            ];

        }

        // =========================
        // INSERTAR PEDIDO EN MONGODB
        // =========================

        $resultado = $db->pedidos->insertOne([

            'nombre_cliente' => $nombre,
            'telefono' => $telefono,
            'fecha' => new UTCDateTime(),
            'total' => $total,
            'productos' => $productos,
            'estado' => 'Pendiente'

        ]);

        // Obtener ID del pedido
        $pedido_id = (string) $resultado->getInsertedId();

        // =========================
        // VACIAR CARRITO
        // =========================

        $_SESSION['carrito'] = [];

        // =========================
        // REDIRIGIR A WHATSAPP
        // =========================

        header("Location: whatsapp_factura.php?id=" . $pedido_id);
        exit;

    }
}
?>

<!doctype html>
<html lang="es">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Finalizar pedido | Atrato Dulce</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container py-5">

<h2 class="text-center mb-4 text-danger fw-bold">
Finalizar Pedido 🧁
</h2>

<?php if ($mensaje): ?>

<div class="alert alert-danger text-center">

<?= $mensaje ?>

</div>

<?php endif; ?>

<div class="card shadow p-4 mx-auto" style="max-width: 500px;">

<form method="post">

<div class="mb-3">

<label class="form-label">

Tu nombre

</label>

<input
type="text"
name="nombre"
class="form-control"
required
>

</div>

<div class="mb-3">

<label class="form-label">

Número de WhatsApp

</label>

<input
type="text"
name="telefono"
class="form-control"
required
placeholder="Ej: 3008144054"
>

</div>

<button class="btn btn-danger w-100 fw-bold">

Confirmar pedido

</button>

</form>

</div>

</div>

</body>
</html>