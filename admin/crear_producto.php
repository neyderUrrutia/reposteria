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

        // ✅ Validación
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

            // 🟢 INSERTAR EN MONGODB
            $db->productos->insertOne([
                'nombre'      => $nombre,
                'categoria'   => $categoria,
                'precio'      => $precio,
                'descripcion' => $descripcion,
                'disponible'  => $disponible,
                'imagen'      => $imagen,
                'creado_en'   => new UTCDateTime()
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

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>

        :root{
            --cream:   #fdf6ee;
            --warm:    #f5e6d0;
            --mocha:   #3b2314;
            --caramel: #c0703a;
            --rose:    #d4737a;
            --gold:    #c9a84c;
            --text:    #2c1a0e;
            --muted:   #8a6f5e;
        }

        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body{
            background: linear-gradient(135deg, var(--cream), var(--warm));
            font-family: 'Poppins', sans-serif;
            color: var(--text);
            min-height: 100vh;
        }

        .main-container{
            padding: 60px 20px;
        }

        .card-form{

            max-width: 760px;
            margin: auto;

            background: #fff;

            border-radius: 28px;

            padding: 45px;

            box-shadow:
                0 10px 30px rgba(0,0,0,0.08),
                0 4px 10px rgba(0,0,0,0.04);

            border: none;
        }

        .top-decoration{
            width: 100%;
            height: 8px;

            background: linear-gradient(
                90deg,
                var(--caramel),
                var(--gold),
                var(--rose)
            );

            border-radius: 20px;

            margin-bottom: 30px;
        }

        .title{
            text-align: center;
            color: var(--mocha);
            font-weight: 700;
            font-size: 2.3rem;
            margin-bottom: 10px;
        }

        .subtitle{
            text-align: center;
            color: var(--muted);
            margin-bottom: 35px;
            font-size: 15px;
        }

        .form-label{
            font-weight: 600;
            color: var(--mocha);
            margin-bottom: 8px;
        }

        .form-control,
        .form-select{

            border-radius: 15px;

            border: 2px solid #ececec;

            padding: 14px 16px;

            transition: all .3s ease;

            font-size: 15px;
        }

        .form-control:focus,
        .form-select:focus{

            border-color: var(--caramel);

            box-shadow: 0 0 0 .15rem rgba(192,112,58,.20);
        }

        textarea.form-control{
            resize: none;
        }

        .form-check-input{
            width: 20px;
            height: 20px;
        }

        .form-check-input:checked{
            background-color: var(--caramel);
            border-color: var(--caramel);
        }

        .form-check-label{
            margin-left: 8px;
            color: var(--text);
            font-weight: 500;
        }

        .btn-custom{

            background: linear-gradient(
                135deg,
                var(--caramel),
                var(--gold)
            );

            border: none;

            color: white;

            border-radius: 14px;

            padding: 12px 28px;

            font-weight: 600;

            transition: .3s ease;
        }

        .btn-custom:hover{

            transform: translateY(-2px);

            opacity: .95;

            color: white;
        }

        .btn-outline-custom{

            border: 2px solid var(--caramel);

            color: var(--caramel);

            border-radius: 14px;

            padding: 12px 28px;

            font-weight: 600;

            transition: .3s ease;
        }

        .btn-outline-custom:hover{

            background: var(--caramel);

            color: white;
        }

        .alert{
            border-radius: 14px;
            border: none;
        }

        .input-group-text{
            border-radius: 15px 0 0 15px;
            border: 2px solid #ececec;
            background: var(--warm);
            color: var(--mocha);
            font-weight: 600;
        }

        .price-input{
            border-left: none;
        }

        @media(max-width: 768px){

            .card-form{
                padding: 30px 22px;
            }

            .title{
                font-size: 1.9rem;
            }
        }

    </style>

</head>

<body>

<div class="main-container">

    <div class="card-form">

        <div class="top-decoration"></div>

        <h2 class="title">
            Añadir Nuevo Producto
        </h2>

        <p class="subtitle">
            Completa la información del producto para agregarlo al catálogo
        </p>

        <?php if ($mensaje): ?>

            <div class="alert alert-danger text-center">

                <?= htmlspecialchars($mensaje) ?>

            </div>

        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">

            <!-- NOMBRE -->
            <div class="mb-4">

                <label class="form-label">
                    Nombre del producto
                </label>

                <input
                    type="text"
                    name="nombre"
                    class="form-control"
                    placeholder="Ej: Torta de Chocolate"
                    required
                >

            </div>

            <!-- CATEGORÍA -->
            <div class="mb-4">

                <label class="form-label">
                    Categoría
                </label>

                <select name="categoria" class="form-select" required>

                    <option value="">
                        Seleccione una categoría
                    </option>

                    <option>Tortas</option>
                    <option>Postres</option>
                    <option>Panadería</option>
                    <option>Galletería</option>
                    <option>Bebidas</option>

                </select>

            </div>

            <!-- PRECIO -->
            <div class="mb-4">

                <label class="form-label">
                    Precio
                </label>

                <div class="input-group">

                    <span class="input-group-text">
                        $
                    </span>

                    <input
                        type="number"
                        name="precio"
                        step="0.01"
                        class="form-control price-input"
                        placeholder="0.00"
                        required
                    >

                </div>

            </div>

            <!-- DESCRIPCIÓN -->
            <div class="mb-4">

                <label class="form-label">
                    Descripción
                </label>

                <textarea
                    name="descripcion"
                    class="form-control"
                    rows="5"
                    placeholder="Describe el producto..."
                    required
                ></textarea>

            </div>

            <!-- IMAGEN -->
            <div class="mb-4">

                <label class="form-label">
                    Imagen del producto
                </label>

                <input
                    type="file"
                    name="imagen"
                    class="form-control"
                >

            </div>

            <!-- DISPONIBLE -->
            <div class="form-check mb-4">

                <input
                    type="checkbox"
                    class="form-check-input"
                    id="disponible"
                    name="disponible"
                    checked
                >

                <label class="form-check-label" for="disponible">

                    Producto disponible

                </label>

            </div>

            <!-- BOTONES -->
            <div class="d-flex justify-content-between align-items-center mt-4">

                <a href="productos.php" class="btn btn-outline-custom">

                    Volver

                </a>

                <button type="submit" class="btn btn-custom">

                    Guardar Producto

                </button>

            </div>

        </form>

    </div>

</div>

</body>
</html>