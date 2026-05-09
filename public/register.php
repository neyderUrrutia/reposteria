<?php 
session_start();
require_once __DIR__ . '/../includes/db.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $clave  = $_POST['clave'] ?? '';

    if ($nombre == "" || $correo == "" || $clave == "") {
        $mensaje = "Todos los campos son obligatorios.";
    } else {

        // 🔍 Verificar si el correo ya existe en Mongo
        $existe = $db->usuarios->findOne([
            'correo' => $correo
        ]);

        if ($existe) {

            $mensaje = "El correo ya está registrado.";

        } else {

            // 🔐 Encriptar contraseña
            $hash = password_hash($clave, PASSWORD_DEFAULT);

            // 💾 Insertar usuario en Mongo
            $db->usuarios->insertOne([
                'nombre' => $nombre,
                'correo' => $correo,
                'clave'  => $hash,
                'fecha'  => new MongoDB\BSON\UTCDateTime()
            ]);

            // 👉 Redirigir al login
            header("Location: login.php?registrado=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro - Atrato Dulce</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
<div class="row justify-content-center">
<div class="col-md-4">

<div class="card shadow">
<div class="card-body">

<h3 class="text-center text-danger">Crear Cuenta</h3>

<?php if ($mensaje): ?>
<div class="alert alert-danger"><?= $mensaje ?></div>
<?php endif; ?>

<form method="POST">

<div class="mb-3">
<label>Nombre completo</label>
<input type="text" name="nombre" class="form-control" required>
</div>

<div class="mb-3">
<label>Email</label>
<input type="email" name="correo" class="form-control" required>
</div>

<div class="mb-3">
<label>Contraseña</label>
<input type="password" name="clave" class="form-control" required>
</div>

<button class="btn btn-danger w-100">
Registrarme
</button>

<p class="text-center mt-3">
¿Ya tienes cuenta?
<a href="login.php">Inicia sesión</a>
</p>

</form>

</div>
</div>

</div>
</div>
</div>

</body>
</html>