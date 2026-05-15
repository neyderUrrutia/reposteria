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
// CAMBIAR ESTADO
// =============================
if (isset($_POST['pedido_id'], $_POST['estado'])) {

    try {

        $id = new ObjectId($_POST['pedido_id']);

        $db->pedidos->updateOne(
            ['_id' => $id],
            [
                '$set' => [
                    'estado' => $_POST['estado']
                ]
            ]
        );

    } catch (Exception $e) {
    }

    header("Location: pedidos.php");
    exit;
}

// =============================
// OBTENER PEDIDOS
// =============================
$pedidos = $db->pedidos->find(
    [],
    [
        'sort' => ['fecha' => -1]
    ]
)->toArray();

?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pedidos | Atrato Dulce Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../public/css/styles.css">
<style>
    :root {
        --cream: #fdf6ee;
        --warm:  #f5e6d0;
        --mocha: #3b2314;
        --caramel: #c0703a;
    }

    body {
        background-color: var(--cream) !important;
    }

    /* Navbar */
    .navbar-admin {
        background-color: var(--mocha) !important;
    }

    /* Título */
    h3 {
        color: var(--mocha) !important;
        font-family: 'Cormorant Garamond', serif;
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

    /* Badges de estado */
    .badge-pendiente   { background-color: #c9a84c !important; color: #fff !important; }
    .badge-preparacion { background-color: var(--caramel) !important; color: #fff !important; }
    .badge-listo       { background-color: var(--mocha) !important; color: #fff !important; }
    .badge-entregado   { background-color: #4a7c59 !important; color: #fff !important; }

    /* Botón Ver */
    a.btn-info, button.btn-info {
        background-color: var(--caramel) !important;
        border: none !important;
        color: white !important;
        border-radius: 50px !important;
        transition: .3s;
    }
    a.btn-info:hover, button.btn-info:hover {
        background-color: var(--mocha) !important;
    }

    /* Botón Guardar */
    a.btn-danger, button.btn-danger {
        background-color: var(--mocha) !important;
        border: none !important;
        color: white !important;
        border-radius: 50px !important;
        transition: .3s;
    }
    a.btn-danger:hover, button.btn-danger:hover {
        background-color: var(--caramel) !important;
    }

    /* Botón Eliminar */
    a.btn-dark, button.btn-dark {
        background-color: #7b3f2a !important;
        border: none !important;
        color: white !important;
        border-radius: 50px !important;
        transition: .3s;
    }
    a.btn-dark:hover, button.btn-dark:hover {
        background-color: #522416 !important;
    }

    /* Botón Volver */
    .btn-volver {
        background-color: var(--caramel);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 6px 16px;
        transition: .3s;
    }
    .btn-volver:hover {
        background-color: var(--cream);
        color: var(--mocha);
    }

    /* Select */
    .form-select {
        border-color: #d4b896 !important;
        border-radius: 10px !important;
    }
    .form-select:focus {
        border-color: var(--caramel) !important;
        box-shadow: 0 0 0 .2rem rgba(192,112,58,.25) !important;
    }

    /* Alert sin pedidos */
    .alert-warning {
        background-color: var(--warm) !important;
        border-color: #d4b896 !important;
        color: var(--mocha) !important;
    }
</style>
</head>

<body>

<nav class="navbar navbar-admin px-4">
  <span class="navbar-brand fw-bold text-white">📦 Pedidos</span>
  <a href="index.php" class="btn-volver">Volver</a>
</nav>

<div class="container my-5">

    <h3 class="fw-bold text-center mb-4">Gestión de pedidos</h3>

    <?php if ($pedidos): ?>

    <div class="table-responsive">

        <table class="table table-bordered table-hover text-center bg-white">

            <thead class="thead-admin">
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Detalle</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

            <?php foreach ($pedidos as $p): ?>

                <?php
                $estado = $p->estado ?? 'pendiente';
                $badgeClass = match($estado) {
                    'pendiente'      => 'badge-pendiente',
                    'en_preparacion' => 'badge-preparacion',
                    'listo'          => 'badge-listo',
                    'entregado'      => 'badge-entregado',
                    default          => 'badge-pendiente'
                };
                ?>

                <tr>

                    <td><?= (string)$p->_id ?></td>

                    <td>
                    <?php
                    if (isset($p->fecha)) {
                        try {
                            echo $p->fecha->toDateTime()->format('Y-m-d H:i');
                        } catch (Exception $e) {
                            echo '';
                        }
                    }
                    ?>
                    </td>

                    <td>$<?= number_format($p->total ?? 0, 0, ',', '.') ?></td>

                    <td>
                        <span class="badge <?= $badgeClass ?>">
                            <?= ucfirst($estado) ?>
                        </span>
                    </td>

                    <td>
                        <a href="detalle_pedido.php?id=<?= (string)$p->_id ?>"
                           class="btn btn-info btn-sm">Ver</a>
                    </td>

                    <td>
                        <form method="post" class="d-flex gap-2">
                            <input type="hidden" name="pedido_id" value="<?= (string)$p->_id ?>">
                            <select name="estado" class="form-select form-select-sm">
                                <option value="pendiente"      <?= $estado=='pendiente'      ?'selected':'' ?>>Pendiente</option>
                                <option value="en_preparacion" <?= $estado=='en_preparacion' ?'selected':'' ?>>En preparación</option>
                                <option value="listo"          <?= $estado=='listo'          ?'selected':'' ?>>Listo</option>
                                <option value="entregado"      <?= $estado=='entregado'      ?'selected':'' ?>>Entregado</option>
                            </select>
                            <button class="btn btn-sm btn-danger">Guardar</button>
                        </form>
                        <br>
                        <a href="eliminar_pedido.php?id=<?= (string)$p->_id ?>"
                           class="btn btn-sm btn-dark"
                           onclick="return confirm('¿Eliminar este pedido?')">Eliminar</a>
                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

    <?php else: ?>

    <div class="alert alert-warning text-center">
        No hay pedidos registrados.
    </div>

    <?php endif; ?>

</div>

</body>
</html>