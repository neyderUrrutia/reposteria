<?php
session_start();

require_once __DIR__ . '/../includes/db.php';

use MongoDB\BSON\UTCDateTime;

// Si el carrito está vacío
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit;
}

$mensaje = "";

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre   = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);

    if ($nombre == "" || $telefono == "") {

        $mensaje = "❌ Debes llenar todos los campos.";

    } else {

        $total = 0;
        foreach ($_SESSION['carrito'] as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        $productos = [];
        foreach ($_SESSION['carrito'] as $item) {
            $productos[] = [
                'producto_id' => $item['id'],
                'nombre'      => $item['nombre'],
                'cantidad'    => $item['cantidad'],
                'precio'      => $item['precio']
            ];
        }

        $resultado = $db->pedidos->insertOne([
            'nombre_cliente' => $nombre,
            'telefono'       => $telefono,
            'fecha'          => new UTCDateTime(),
            'total'          => $total,
            'productos'      => $productos,
            'estado'         => 'Pendiente'
        ]);

        $pedido_id = (string) $resultado->getInsertedId();

        $_SESSION['carrito'] = [];

        header("Location: whatsapp_factura.php?id=" . $pedido_id);
        exit;
    }
}
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Finalizar pedido | Atrato Dulce</title>

<!-- 1️⃣ Bootstrap primero -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">

<!-- 2️⃣ Tus estilos DESPUÉS para que ganen -->
<style>
    :root {
        --cream:   #fdf6ee;
        --warm:    #f5e6d0;
        --mocha:   #3b2314;
        --caramel: #c0703a;
    }

    body {
        background-color: var(--cream) !important;
        font-family: 'DM Sans', sans-serif;
    }

    .checkout-title {
        font-family: 'Cormorant Garamond', serif;
        color: var(--mocha) !important;
        font-size: 2rem;
        font-weight: 700;
    }

    .checkout-card {
        background: white;
        border: 1px solid rgba(192,112,58,0.2);
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(59,35,20,0.08);
        padding: 2.5rem 2rem 2rem;
        max-width: 480px;
        margin: 0 auto;
        position: relative;
        overflow: hidden;
    }

    /* Línea decorativa superior */
    .checkout-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--mocha), var(--caramel));
    }

    /* Sobreescribir Bootstrap en labels */
    .form-label {
        color: var(--mocha) !important;
        font-weight: 500 !important;
        font-size: 13.5px !important;
        margin-bottom: 6px !important;
    }

    /* Sobreescribir Bootstrap en inputs */
    .form-control {
        border: 1.5px solid #e8d5c4 !important;
        border-radius: 10px !important;
        padding: 10px 14px !important;
        font-size: 14px !important;
        background-color: #fffaf6 !important;
        color: var(--mocha) !important;
        transition: border-color .3s, box-shadow .3s !important;
        box-shadow: none !important;
    }

    .form-control:focus {
        border-color: var(--caramel) !important;
        box-shadow: 0 0 0 3px rgba(192,112,58,0.15) !important;
        background-color: white !important;
        outline: none !important;
    }

    .form-control::placeholder {
        color: #c4a98a !important;
        opacity: 1 !important;
    }

    /* Botón confirmar */
    .btn-confirmar {
        background: var(--caramel) !important;
        color: white !important;
        border: none !important;
        border-radius: 50px !important;
        padding: 12px !important;
        font-size: 15px !important;
        font-weight: 500 !important;
        width: 100% !important;
        transition: .3s !important;
        letter-spacing: 0.02em;
        cursor: pointer;
    }

    .btn-confirmar:hover {
        background: var(--mocha) !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(59,35,20,0.2) !important;
        color: white !important;
    }

    /* Botón volver */
    .btn-volver {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #9a7b6a !important;
        font-size: 13px;
        text-decoration: none !important;
        margin-bottom: 1.5rem;
        transition: .2s;
    }

    .btn-volver:hover {
        color: var(--caramel) !important;
    }

    /* Alerta error */
    .alert-error {
        background: #fdeeed !important;
        border: 1px solid #f0c4c4 !important;
        color: #8b2e2e !important;
        border-radius: 12px !important;
        padding: 12px 16px !important;
        font-size: 13.5px;
        text-align: center;
        margin-bottom: 1.25rem;
    }

    /* Divider */
    .checkout-divider {
        border: none !important;
        border-top: 1px solid #f1e4d8 !important;
        margin: 1.25rem 0 !important;
    }

    /* Nota inferior */
    .checkout-note {
        text-align: center;
        font-size: 12px;
        color: #b0907a;
        margin-top: 1rem;
        margin-bottom: 0;
    }
</style>
</head>

<body>

<div class="container py-5">

    <!-- Botón volver -->
    <div class="text-center mb-2">
        <a href="carrito.php" class="btn-volver">← Volver al carrito</a>
    </div>

    <!-- Título -->
    <h2 class="checkout-title text-center mb-4">Finalizar Pedido 🧁</h2>

    <!-- Card -->
    <div class="checkout-card">

        <?php if ($mensaje): ?>
        <div class="alert-error"><?= $mensaje ?></div>
        <?php endif; ?>

        <form method="post">

            <div class="mb-3">
                <label class="form-label">Tu nombre</label>
                <input
                    type="text"
                    name="nombre"
                    class="form-control"
                    placeholder="Ej: María López"
                    value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                    required
                >
            </div>

            <div class="mb-4">
                <label class="form-label">Número de WhatsApp</label>
                <input
                    type="text"
                    name="telefono"
                    class="form-control"
                    placeholder="Ej: 3008144054"
                    value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"
                    required
                >
            </div>

            <hr class="checkout-divider">

            <button type="submit" class="btn-confirmar">
                Confirmar pedido
            </button>

        </form>

        <p class="checkout-note">
            🔒 Tu pedido se enviará por WhatsApp de forma segura
        </p>

    </div>

</div>

</body>
</html>