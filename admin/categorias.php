<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

// Obtener categorías desde Mongo
$categorias = $db->categorias->find([], ['sort' => ['nombre' => 1]]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Categorías | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
  <h2 class="fw-bold text-danger mb-4">Gestionar Categorías</h2>

  <!-- BOTÓN CORREGIDO -->
  <a href="categoria_crear.php" class="btn btn-primary mb-3">➕ Nueva categoría</a>

  <table class="table table-bordered bg-white">
    <thead class="table-danger">
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>

      <?php foreach ($categorias as $c): ?>
        <tr>
          <td><?= (string)$c['_id'] ?></td>
          <td><?= htmlspecialchars($c['nombre']) ?></td>
          <td>
            <a href="categoria_eliminar.php?id=<?= (string)$c['_id'] ?>" 
               class="btn btn-danger btn-sm"
               onclick="return confirm('¿Eliminar categoría?');">
               🗑 Eliminar
            </a>
          </td>
        </tr>
      <?php endforeach; ?>

    </tbody>
  </table>

  <a href="index.php" class="btn btn-secondary mt-3">⬅ Volver al panel</a>
</div>

</body>
</html>