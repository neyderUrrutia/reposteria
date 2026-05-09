<?php
session_start();

use MongoDB\BSON\ObjectId;

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// =============================
// VALIDAR ID
// =============================
if (!isset($_GET['id'])) {
    header("Location: pedidos.php");
    exit;
}

try {

    $id = new ObjectId($_GET['id']);

    $pedido = $db->pedidos->findOne([
        '_id' => $id
    ]);

} catch (Exception $e) {

    header("Location: pedidos.php");
    exit;

}

if (!$pedido) {
    header("Location: pedidos.php");
    exit;
}

?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Detalle del Pedido</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../public/css/styles.css">
<style>
    :root {
        --cream:   #fdf6ee;
        --warm:    #f5e6d0;
        --mocha:   #3b2314;
        --caramel: #c0703a;
    }

    body {
        background-color: var(--cream) !important;
    }

    /* Navbar */
    .navbar-admin {
        background-color: var(--mocha) !important;
    }

    .btn-volver {
        background-color: var(--caramel);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 6px 16px;
        text-decoration: none;
        transition: .3s;
    }
    .btn-volver:hover {
        background-color: var(--cream);
        color: var(--mocha);
    }

    /* Título */
    h3, h5 {
        color: var(--mocha) !important;
        font-family: 'Cormorant Garamond', serif;
    }

    /* Card */
    .card {
        border: 1px solid #d4b896 !important;
        border-radius: 18px !important;
        background: white !important;
    }

    .card strong {
        color: var(--mocha);
    }

    /* Encabezado tabla */
    thead.thead-admin tr {
        background-color: var(--warm) !important;
    }
    thead.thead-admin th {
        color: var(--mocha) !important;
        font-weight: 600;
        border: none;
    }

    /* Celdas */
    .table td {
        vertical-align: middle;
        border-color: #f1e4d8 !important;
    }

    /* Total */
    h4.total {
        color: var(--mocha) !important;
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.5rem;
    }

    hr {
        border-color: #d4b896;
    }
</style>
</head>

<body>

<nav class="navbar navbar-admin px-4">
    <span class="navbar-brand fw-bold text-white">📦 Detalle del pedido</span>
    <a href="pedidos.php" class="btn-volver">Volver</a>
</nav>

<div class="container my-5">

    <h3 class="fw-bold text-center mb-4">Detalle del pedido</h3>

    <div class="card p-4">

        <p><strong>ID:</strong> <?= (string)$pedido->_id ?></p>

        <p><strong>Estado:</strong> <?= ucfirst($pedido->estado ?? 'pendiente') ?></p>

        <p>
            <strong>Fecha:</strong>
            <?php
            if (isset($pedido->fecha)) {
                try {
                    echo $pedido->fecha->toDateTime()->format('Y-m-d H:i');
                } catch (Exception $e) {
                    echo '';
                }
            }
            ?>
        </p>

        <hr>

        <h5>Productos</h5>

        <table class="table table-bordered">

            <thead class="thead-admin">
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($pedido->productos as $item): ?>
                <tr>
                    <td><?= h($item['nombre'] ?? '') ?></td>
                    <td>$<?= number_format($item['precio'] ?? 0, 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>

        <h4 class="total text-end">
            Total: $<?= number_format($pedido->total ?? 0, 0, ',', '.') ?>
        </h4>

    </div>

</div>

</body>
</html>