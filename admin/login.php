<?php
session_start();

require_once __DIR__ . '/../includes/db.php';

$mensaje = "";

// SOLO cuando se envía el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Evita errores si aún no hay datos
    $correo = trim($_POST['correo'] ?? '');
    $clave  = trim($_POST['clave'] ?? '');

    // Buscar administrador en Mongo
    $admin = $db->usuarios->findOne([
        'correo' => $correo,
        'rol' => 'admin'
    ]);

    if ($admin && password_verify($clave, $admin['clave'])) {

        $_SESSION['admin_id'] = (string)$admin['_id'];
        $_SESSION['admin_nombre'] = $admin['nombre'];

        header("Location: index.php");
        exit;

    } else {
        $mensaje = "Correo o contraseña incorrectos";
    }
}
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Login Administrador | Atrato Dulce</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/css/style.css" rel="stylesheet">
</head>

<body class="d-flex justify-content-center align-items-center" style="height:100vh;background:#fff8f8;">

<div class="card p-4 shadow-lg" style="width:380px;border-radius:20px;">

<div class="text-center mb-3">
    <h4 class="text-danger">Panel de Administración</h4>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-danger">
<?= $mensaje ?>
</div>
<?php endif; ?>

<form method="POST">

<div class="mb-3">
<label>Correo electrónico</label>
<input type="email" name="correo" class="form-control" required>
</div>

<div class="mb-3">
<label>Contraseña</label>
<input type="password" name="clave" class="form-control" required>
</div>

<button class="btn btn-danger w-100">
Ingresar
</button>

</form>

</div>

</body>
</html>