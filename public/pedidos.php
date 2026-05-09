<?php
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

use MongoDB\BSON\UTCDateTime;

// VALIDAR SESIÓN
if (!isset($_SESSION['usuario'])) {
  header('Location: login.php');
  exit;
}

$usuario = $_SESSION['usuario'];
$mensaje = '';

// PROCESAR PEDIDO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $producto  = trim($_POST['producto'] ?? '');
  $cantidad  = (int)($_POST['cantidad'] ?? 0);
  $direccion = trim($_POST['direccion'] ?? '');
  $telefono  = trim($_POST['telefono'] ?? '');

  if ($producto && $cantidad > 0 && $direccion && $telefono) {

    $db->pedidos->insertOne([

      'usuario_id' => $usuario['id'] ?? null,
      'producto'   => $producto,
      'cantidad'   => $cantidad,
      'direccion'  => $direccion,
      'telefono'   => $telefono,
      'estado'     => 'pendiente',
      'fecha'      => new UTCDateTime()

    ]);

    $mensaje = "✅ Pedido realizado correctamente. ¡Gracias por tu compra!";

  } else {
    $mensaje = "⚠️ Por favor completa todos los campos.";
  }
}

// OBTENER HISTORIAL
$pedidos = $db->pedidos->find(
  ['usuario_id' => $usuario['id'] ?? null],
  ['sort' => ['fecha' => -1]]
);
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mis pedidos | Atrato Dulce 🍰</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- BANNER -->
<section class="text-center text-white d-flex align-items-center justify-content-center"
style="height: 30vh; background: linear-gradient(rgba(214,40,40,0.6), rgba(214,40,40,0.6)), url('assets/img/pedidos-bg.jpg') center/cover no-repeat;">
  <div>
    <h1 class="fw-bold">Haz tu pedido 🍓</h1>
    <p class="lead">Endulza tu día con Atrato Dulce</p>
  </div>
</section>

<!-- FORMULARIO -->
<div class="container py-5" style="max-width: 700px;">
  <h3 class="text-center text-danger fw-bold mb-4">Realizar pedido</h3>

  <?php if ($mensaje): ?>
    <div class="alert alert-info text-center"><?= $mensaje ?></div>
  <?php endif; ?>

  <form method="post" class="p-4 shadow-sm rounded-4 bg-white">

    <div class="mb-3">
      <label>Producto</label>
      <input type="text" name="producto" class="form-control" required>
    </div>

    <div class="mb-3">
      <label>Cantidad</label>
      <input type="number" name="cantidad" class="form-control" min="1" value="1" required>
    </div>

    <div class="mb-3">
      <label>Dirección</label>
      <input type="text" name="direccion" class="form-control" required>
    </div>

    <div class="mb-3">
      <label>Teléfono</label>
      <input type="text" name="telefono" class="form-control" required>
    </div>

    <button class="btn btn-danger w-100">
      <i class="bi bi-bag-heart"></i> Realizar pedido
    </button>

  </form>
</div>

<!-- HISTORIAL -->
<div class="container pb-5">

<h4 class="text-danger fw-bold text-center mb-3">
Mis pedidos anteriores
</h4>

<div class="table-responsive">

<table class="table table-bordered text-center">

<thead class="table-danger">
<tr>
<th>Producto</th>
<th>Cantidad</th>
<th>Dirección</th>
<th>Estado</th>
<th>Fecha</th>
</tr>
</thead>

<tbody>

<?php foreach ($pedidos as $p): ?>

<tr>

<td><?= h($p->producto ?? '') ?></td>

<td><?= $p->cantidad ?? 0 ?></td>

<td><?= h($p->direccion ?? '') ?></td>

<td>
<span class="badge bg-
<?= ($p->estado ?? 'pendiente') == 'pendiente' ? 'warning' :
   (($p->estado ?? '') == 'en proceso' ? 'info' : 'success') ?>">
<?= ucfirst($p->estado ?? 'pendiente') ?>
</span>
</td>

<td>
<?php
if (isset($p->fecha)) {
  echo $p->fecha->toDateTime()->format('d/m/Y H:i');
}
?>
</td>

</tr>

<?php endforeach; ?>

</tbody>
</table>

</div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>