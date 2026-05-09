<?php
session_start();

require_once __DIR__ . '/../includes/db.php';

$usuarios = $db->usuarios;

if (isset($_SESSION['usuario_id'])) {
    header("Location: /");
    exit;
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = trim($_POST['correo']);
    $clave = trim($_POST['clave']);

    // 🔍 Buscar usuario
    $user = $usuarios->findOne(['correo' => $correo]);

    if ($user) {
        // ✅ Usuario existe → validar contraseña
        if (password_verify($clave, $user['clave'])) {

            $_SESSION['usuario_id'] = (string)$user['_id'];
            $_SESSION['usuario_nombre'] = $user['nombre'];

            header("Location: /");
            exit;

        } else {
            $mensaje = "Contraseña incorrecta.";
        }

    } else {

        // 🔥 SI NO EXISTE → LO CREA AUTOMÁTICAMENTE
        $nuevo = [
            'nombre' => 'Usuario',
            'correo' => $correo,
            'clave' => password_hash($clave, PASSWORD_DEFAULT)
        ];

        $insert = $usuarios->insertOne($nuevo);

        $_SESSION['usuario_id'] = (string)$insert->getInsertedId();
        $_SESSION['usuario_nombre'] = 'Usuario';

        header("Location: /");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Atrato Dulce</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-4">

            <div class="card shadow">

                <div class="card-body">

                    <h3 class="text-center text-danger">
                        Iniciar Sesión
                    </h3>

                    <form method="POST">

                        <?php if ($mensaje): ?>
                            <div class="alert alert-danger">
                                <?= $mensaje ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label>Email</label>

                            <input
                                type="email"
                                name="correo"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label>Contraseña</label>

                            <input
                                type="password"
                                name="clave"
                                class="form-control"
                                required
                            >
                        </div>

                        <button class="btn btn-danger w-100">
                            Ingresar
                        </button>

                        <p class="text-center mt-3">
                            💡 Si no tienes cuenta, se crea automáticamente
                        </p>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>