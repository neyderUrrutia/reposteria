<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);

    if ($nombre !== "") {

        // Insertar en Mongo
        $db->categorias->insertOne([
            'nombre' => $nombre
        ]);

        header("Location: categorias.php");
        exit;

    } else {
        $mensaje = "El nombre no puede estar vacío";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nueva Categoría</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
  <h2 class="fw-bold text-danger mb-4">Crear categoría</h2>

  <?php if ($mensaje): ?>
    <div class="alert alert-danger"><?= $mensaje ?></div>
  <?php endif; ?>

  <form method="POST" class="bg-white p-4 shadow rounded">
    <label class="form-label fw-bold">Nombre de la categoría</label>
    <input type="text" name="nombre" class="form-control mb-3" required>

    <button class="btn btn-primary">Guardar</button>
    <a href="categorias.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

</body>
</html>