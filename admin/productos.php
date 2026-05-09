<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

use MongoDB\BSON\ObjectId;

$collection = $db->productos;

// 🔥 ELIMINAR
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $collection->deleteOne(['_id' => new ObjectId($id)]);
    header("Location: productos.php");
    exit;
}

// 🔥 OBTENER PRODUCTOS
$productos = $collection->find([], ['sort' => ['creado_en' => -1]]);
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Productos Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../public/css/styles.css">
<style>
    :root {
        --cream:    #fdf6ee;
        --warm:     #f5e6d0;
        --mocha:    #3b2314;
        --caramel:  #c0703a;
    }

    body {
        background-color: var(--cream) !important;
    }

    h2 {
        color: var(--mocha) !important;
        font-family: 'Cormorant Garamond', serif;
    }

    /* Botón Volver */
    .btn-volver {
        background: transparent;
        border: 1.5px solid var(--mocha);
        color: var(--mocha) !important;
        border-radius: 50px !important;
        padding: 6px 18px;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        transition: .3s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-volver:hover {
        background: var(--mocha);
        color: white !important;
    }

    /* Botón Nuevo Producto */
    a.btn-success,
    button.btn-success {
        background: var(--caramel) !important;
        border: none !important;
        color: white !important;
        border-radius: 50px !important;
        transition: .3s;
    }
    a.btn-success:hover,
    button.btn-success:hover {
        background: var(--mocha) !important;
    }

    /* Botón Editar */
    a.btn-primary,
    button.btn-primary {
        background: var(--mocha) !important;
        border: none !important;
        color: white !important;
        border-radius: 50px !important;
        transition: .3s;
    }
    a.btn-primary:hover,
    button.btn-primary:hover {
        background: var(--caramel) !important;
    }

    /* Botón Eliminar */
    a.btn-danger,
    button.btn-danger {
        background: #7b3f2a !important;
        border: none !important;
        color: white !important;
        border-radius: 50px !important;
        transition: .3s;
    }
    a.btn-danger:hover,
    button.btn-danger:hover {
        background: #522416 !important;
    }

    /* Tabla */
    .table thead {
        background: var(--warm) !important;
    }
    .table th {
        color: var(--mocha) !important;
        font-weight: 600;
        border: none;
    }
    .table td {
        vertical-align: middle;
        border-color: #f1e4d8 !important;
    }
    table img {
        border-radius: 12px;
        object-fit: cover;
    }
</style>
</head>
<body>

<div class="container py-5">

    <!-- Encabezado con botón volver -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="index.php" class="btn-volver">
            ← Volver
        </a>
        <h2 class="mb-0">Gestión de Productos</h2>
    </div>

    <a href="crear_producto.php" class="btn btn-success mb-3">+ Nuevo producto</a>

    <table class="table table-bordered bg-white">
        <thead>
            <tr>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $p): ?>
            <tr>
                <td>
                    <?php if (!empty($p->imagen)): ?>
                    <img src="../public/assets/uploads/<?= htmlspecialchars($p->imagen) ?>" width="60">
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($p->nombre ?? '') ?></td>
                <td>$<?= number_format($p->precio ?? 0, 0, ',', '.') ?></td>
                <td>
                    <a href="editar_producto.php?id=<?= (string)$p->_id ?>" class="btn btn-sm btn-primary">Editar</a>
                    <a href="productos.php?eliminar=<?= (string)$p->_id ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('¿Eliminar producto?')">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>
</body>
</html>