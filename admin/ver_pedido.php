<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$pedidos = $db->pedidos->find([], ['sort' => ['fecha' => -1]]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pedidos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-5">

<h2 class="text-danger">Pedidos</h2>

<?php foreach ($pedidos as $p): ?>
<div class="card mb-3 p-3">

<strong>Fecha:</strong> <?= $p->fecha ?><br>
<strong>Total:</strong> $<?= number_format($p->total) ?>

<hr>

<?php foreach ($p->productos as $prod): ?>
- <?= $prod['nombre'] ?> ($<?= number_format($prod['precio']) ?>)<br>
<?php endforeach; ?>

</div>
<?php endforeach; ?>

</body>
</html>