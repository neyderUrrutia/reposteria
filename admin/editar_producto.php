<?php
require_once __DIR__ . '/../includes/db.php';

use MongoDB\BSON\ObjectId;

$collection = $db->productos;

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID inválido");
}

// 🔥 BUSCAR
$producto = $collection->findOne([
    '_id' => new ObjectId($id)
]);

if (!$producto) {
    die("Producto no encontrado");
}

// 🔥 ACTUALIZAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $imagen = $producto->imagen ?? null;

    if (!empty($_FILES['imagen']['name'])) {

        $carpeta = __DIR__ . '/../public/assets/uploads/';

        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        $archivo = time() . '_' . $_FILES['imagen']['name'];

        move_uploaded_file(
            $_FILES['imagen']['tmp_name'],
            $carpeta . $archivo
        );

        $imagen = $archivo;
    }

    $collection->updateOne(
        ['_id' => new ObjectId($id)],
        ['$set' => [
            'nombre' => $_POST['nombre'],
            'categoria' => $_POST['categoria'],
            'precio' => (float)$_POST['precio'],
            'descripcion' => $_POST['descripcion'],
            'disponible' => isset($_POST['disponible']),
            'imagen' => $imagen
        ]]
    );

    header("Location: productos.php");
    exit;
}
?>

<!doctype html>
<html>
<head>
<title>Editar producto</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container py-5">

<form method="post" enctype="multipart/form-data" class="bg-white p-4 shadow">

<input name="nombre" class="form-control mb-2"
value="<?= htmlspecialchars($producto->nombre ?? '') ?>">

<input name="precio" class="form-control mb-2"
value="<?= htmlspecialchars($producto->precio ?? 0) ?>">

<textarea name="descripcion" class="form-control mb-2">
<?= htmlspecialchars($producto->descripcion ?? '') ?>
</textarea>

<select name="categoria" class="form-control mb-2">
<option><?= htmlspecialchars($producto->categoria ?? '') ?></option>
<option>Tortas</option>
<option>Postres</option>
<option>Panadería</option>
</select>

<?php if (!empty($producto->imagen)): ?>
<img src="../public/assets/uploads/<?= htmlspecialchars($producto->imagen) ?>" width="120">
<?php endif; ?>

<input type="file" name="imagen" class="form-control mb-2">

<label>
<input type="checkbox" name="disponible"
<?= ($producto->disponible ?? false) ? 'checked' : '' ?>>
Disponible
</label>

<button class="btn btn-danger mt-3">Actualizar</button>

</form>

</div>

</body>
</html>