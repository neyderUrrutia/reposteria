<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/db.php';

use MongoDB\BSON\UTCDateTime;

$mensaje = "";

// 🚀 CREAR PRODUCTO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $nombre = trim($_POST['nombre']);
        $categoria = trim($_POST['categoria']);
        $precio = (float)$_POST['precio'];
        $descripcion = trim($_POST['descripcion']);
        $disponible = isset($_POST['disponible']) ? true : false;

        // Validación
        if (!$nombre || !$categoria || !$precio || !$descripcion) {
            $mensaje = "⚠️ Todos los campos son obligatorios";
        } else {

            $imagen = null;

            // 📸 SUBIR IMAGEN
            if (!empty($_FILES['imagen']['name'])) {

                $carpeta = __DIR__ . '/../public/assets/uploads/';

                if (!is_dir($carpeta)) {
                    mkdir($carpeta, 0777, true);
                }

                $archivo = time() . '_' . basename($_FILES['imagen']['name']);
                $ruta = $carpeta . $archivo;

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta)) {
                    $imagen = $archivo;
                } else {
                    $mensaje = "❌ Error al subir la imagen";
                }
            }

            // 🟢 INSERTAR EN MONGO
            $db->productos->insertOne([
                'nombre' => $nombre,
                'categoria' => $categoria,
                'precio' => $precio,
                'descripcion' => $descripcion,
                'disponible' => $disponible,
                'imagen' => $imagen,
                'creado_en' => new UTCDateTime()
            ]);

            // 🔥 REDIRECCIÓN
            header("Location: productos.php?mensaje=creado");
            exit;
        }

    } catch (Exception $e) {
        $mensaje = "❌ Error: " . $e->getMessage();
    }
}
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nuevo Producto | Atrato Dulce Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">
  <div class="card shadow-sm p-4 mx-auto" style="max-width: 700px;">

    <h2 class="text-center text-success fw-bold mb-4">
      Añadir Nuevo Producto
    </h2>

    <?php if ($mensaje): ?>
      <div class="alert alert-danger text-center"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">

      <div class="mb-3">
        <label class="form-label fw-semibold">Nombre del producto</label>
        <input type="text" name="nombre" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Categoría</label>
        <select name="categoria" class="form-select" required>
          <option value="">Seleccione...</option>
          <option>Tortas</option>
          <option>Postres</option>
          <option>Panadería</option>
          <option>Galletería</option>
          <option>Bebidas</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Precio</label>
        <input type="number" name="precio" step="0.01" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Descripción</label>
        <textarea name="descripcion" class="form-control" rows="4" required></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Imagen</label>
        <input type="file" name="imagen" class="form-control">
      </div>

      <div class="form-check mb-4">
        <input type="checkbox" class="form-check-input" id="disponible" name="disponible" checked>
        <label class="form-check-label" for="disponible">Producto disponible</label>
      </div>

      <div class="d-flex justify-content-between">
        <a href="productos.php" class="btn btn-outline-secondary">Volver</a>
        <button type="submit" class="btn btn-success">Guardar Producto</button>
      </div>

    </form>

  </div>
</div>

</body>
</html>