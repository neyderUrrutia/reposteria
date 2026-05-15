<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/enviar_factura.php';

use MongoDB\BSON\UTCDateTime;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;

if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit;
}

MercadoPagoConfig::setAccessToken("APP_USR-8653626796901740-051502-a38586b6b63378babebb4038cd991bf3-3404522624");

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $metodo = $_POST['metodo_pago'] ?? 'mercadopago'; // 'mercadopago' o 'efectivo'

    if (!$nombre || !$correo) {
        $mensaje = "❌ Debes llenar todos los campos.";
    } else {

        $total     = 0;
        $productos = [];
        $items_mp  = [];

        foreach ($_SESSION['carrito'] as $item) {
            $subtotal   = $item['precio'] * $item['cantidad'];
            $total     += $subtotal;
            $productos[] = [
                'nombre'   => $item['nombre'],
                'cantidad' => $item['cantidad'],
                'precio'   => $item['precio']
            ];
            $items_mp[] = [
                'id'          => $item['id'],
                'title'       => $item['nombre'],
                'quantity'    => (int)$item['cantidad'],
                'unit_price'  => (float)$item['precio'],
                'currency_id' => 'COP'
            ];
        }

        // Guardar pedido en MongoDB
        $resultado = $db->pedidos->insertOne([
            'nombre_cliente' => $nombre,
            'correo'         => $correo,
            'fecha'          => new UTCDateTime(),
            'total'          => $total,
            'productos'      => $productos,
            'metodo_pago'    => $metodo,
            'estado'         => 'Pendiente'
        ]);

        $pedido_id = (string)$resultado->getInsertedId();

        // ── EFECTIVO ──
if ($metodo === 'efectivo') {

    // ENVIAR FACTURA AL CORREO
    enviarFactura(
        $correo,
        $nombre,
        $pedido_id,
        $total
    );

    // LIMPIAR CARRITO
    $_SESSION['carrito'] = [];

    // REDIRECCIÓN
    header("Location: pago_pending.php?pedido=" . $pedido_id . "&tipo=efectivo");

    exit;
}

        // ── MERCADO PAGO ──
        try {
            $base_url = "https://atrato-dulce-je6g.onrender.com/public";

            $client     = new PreferenceClient();
            $preference = $client->create([
                'items' => $items_mp,
                'payer' => [
                    'name'  => $nombre,
                    'email' => $correo,
                ],
                'back_urls' => [
                    'success' => $base_url . '/pago_success.php?pedido=' . $pedido_id,
                    'failure' => $base_url . '/pago_failure.php?pedido=' . $pedido_id,
                    'pending' => $base_url . '/pago_pending.php?pedido=' . $pedido_id,
                ],
                'auto_return'          => 'approved',
                'external_reference'   => $pedido_id,
                'statement_descriptor' => 'Atrato Dulce'
            ]);

            $_SESSION['carrito'] = [];
            header("Location: " . $preference->init_point);
            exit;

        } catch (MPApiException $e) {
            $api_content = $e->getApiResponse() ? json_encode($e->getApiResponse()->getContent()) : 'sin respuesta';
            $mensaje = "❌ Error al conectar con Mercado Pago: " . $e->getMessage() . " | " . $api_content;
        } catch (\Exception $e) {
            $mensaje = "❌ Error general: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Finalizar pedido | Atrato Dulce</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
    :root {
        --cream:   #fdf6ee;
        --warm:    #f5e6d0;
        --mocha:   #3b2314;
        --caramel: #c0703a;
    }
    * { box-sizing: border-box; }
    body {
        background-color: var(--cream);
        font-family: 'DM Sans', sans-serif;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }
    .checkout-wrap { width: 100%; max-width: 480px; }

    .checkout-title {
        font-family: 'Cormorant Garamond', serif;
        color: var(--mocha);
        font-size: 2rem;
        font-weight: 700;
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .checkout-card {
        background: white;
        border: 1px solid rgba(192,112,58,0.2);
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(59,35,20,0.08);
        padding: 2.5rem 2rem 2rem;
        position: relative;
        overflow: hidden;
    }
    .checkout-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--mocha), var(--caramel));
    }

    .form-label {
        color: var(--mocha) !important;
        font-weight: 500 !important;
        font-size: 12px !important;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: 6px !important;
    }

    .input-wrap { position: relative; }
    .input-icon {
        position: absolute; left: 13px; top: 50%;
        transform: translateY(-50%);
        color: #b0907a; font-size: 14px; pointer-events: none;
    }

    .form-control {
        border: 1.5px solid #e8d5c4 !important;
        border-radius: 10px !important;
        padding: 10px 14px 10px 36px !important;
        font-size: 14px !important;
        background-color: #fffaf6 !important;
        color: var(--mocha) !important;
        transition: .3s !important;
        box-shadow: none !important;
    }
    .form-control:focus {
        border-color: var(--caramel) !important;
        box-shadow: 0 0 0 3px rgba(192,112,58,0.15) !important;
        background-color: white !important;
    }
    .form-control::placeholder { color: #c4a98a !important; opacity: 1 !important; }

    .resumen {
        background: var(--warm);
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        font-size: 13px;
        color: var(--mocha);
    }
    .resumen-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: .5rem;
    }
    .resumen-item {
        display: flex;
        justify-content: space-between;
        padding: 3px 0;
        border-bottom: 1px solid rgba(192,112,58,0.15);
    }
    .resumen-item:last-child { border: none; }
    .resumen-total {
        display: flex;
        justify-content: space-between;
        font-weight: 700;
        font-size: 1rem;
        margin-top: .5rem;
        color: var(--caramel);
        font-family: 'Cormorant Garamond', serif;
    }

    .checkout-divider {
        border: none !important;
        border-top: 1px solid #f1e4d8 !important;
        margin: 1.25rem 0 !important;
    }

    /* ── MÉTODOS DE PAGO ── */
    .metodos-title {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .05em;
        font-weight: 500;
        color: var(--mocha);
        margin-bottom: 10px;
    }
    .metodo-option {
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1.5px solid #e8d5c4;
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all .2s;
        background: #fffaf6;
    }
    .metodo-option:has(input:checked) {
        border-color: var(--caramel);
        background: #fff7f0;
        box-shadow: 0 0 0 3px rgba(192,112,58,0.12);
    }
    .metodo-option input[type="radio"] { accent-color: var(--caramel); width: 16px; height: 16px; }
    .metodo-icon { font-size: 1.4rem; }
    .metodo-info { flex: 1; }
    .metodo-info strong { font-size: 13.5px; color: var(--mocha); display: block; }
    .metodo-info small { font-size: 11.5px; color: #9a7b6a; }

    .btn-pagar {
        background: var(--caramel) !important;
        color: white !important;
        border: none !important;
        border-radius: 50px !important;
        padding: 13px !important;
        font-size: 15px !important;
        font-weight: 500 !important;
        width: 100% !important;
        transition: .3s !important;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-pagar:hover {
        background: var(--mocha) !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(59,35,20,0.2) !important;
    }

    .btn-volver {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        color: #9a7b6a;
        font-size: 13px;
        text-decoration: none;
        margin-bottom: 1.25rem;
        transition: .2s;
    }
    .btn-volver:hover { color: var(--caramel); }

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

    .seguro-note {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 11.5px;
        color: #b0907a;
        margin-top: 1rem;
    }
</style>
</head>
<body>

<div class="checkout-wrap">

    <a href="carrito.php" class="btn-volver">
        <i class="bi bi-arrow-left"></i> Volver al carrito
    </a>

    <h2 class="checkout-title">Finalizar Pedido 🧁</h2>

    <div class="checkout-card">

        <?php if ($mensaje): ?>
        <div class="alert-error"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <!-- Resumen del carrito -->
        <div class="resumen">
            <div class="resumen-title">Resumen de tu pedido</div>
            <?php
            $total_resumen = 0;
            foreach ($_SESSION['carrito'] as $item):
                $sub = $item['precio'] * $item['cantidad'];
                $total_resumen += $sub;
            ?>
            <div class="resumen-item">
                <span><?= htmlspecialchars($item['nombre']) ?> x<?= $item['cantidad'] ?></span>
                <span>$<?= number_format($sub, 0, ',', '.') ?></span>
            </div>
            <?php endforeach; ?>
            <div class="resumen-total">
                <span>Total</span>
                <span>$<?= number_format($total_resumen, 0, ',', '.') ?></span>
            </div>
        </div>

        <form method="post">

            <div class="mb-3">
                <label class="form-label">Tu nombre</label>
                <div class="input-wrap">
                    <i class="bi bi-person input-icon"></i>
                    <input type="text" name="nombre" class="form-control"
                        placeholder="Ej: María López"
                        value="<?= htmlspecialchars($_POST['nombre'] ?? $_SESSION['usuario_nombre'] ?? '') ?>"
                        required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Correo electrónico</label>
                <div class="input-wrap">
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email" name="correo" class="form-control"
                        placeholder="Ej: maria@email.com"
                        value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                        required>
                </div>
            </div>

            <hr class="checkout-divider">

            <!-- MÉTODO DE PAGO -->
            <div class="metodos-title">Método de pago</div>

            <label class="metodo-option">
                <input type="radio" name="metodo_pago" value="mercadopago"
                    <?= ($_POST['metodo_pago'] ?? 'mercadopago') === 'mercadopago' ? 'checked' : '' ?>>
                <span class="metodo-icon">💳</span>
                <div class="metodo-info">
                    <strong>Mercado Pago</strong>
                    <small>Tarjeta, PSE, Nequi, Daviplata y más</small>
                </div>
            </label>

            <label class="metodo-option">
                <input type="radio" name="metodo_pago" value="efectivo"
                    <?= ($_POST['metodo_pago'] ?? '') === 'efectivo' ? 'checked' : '' ?>>
                <span class="metodo-icon">💵</span>
                <div class="metodo-info">
                    <strong>Efectivo</strong>
                    <small>Paga al recibir tu pedido</small>
                </div>
            </label>

            <hr class="checkout-divider">

            <button type="submit" class="btn-pagar">
                <i class="bi bi-bag-check"></i> Confirmar pedido
            </button>

        </form>

        <div class="seguro-note">
            <i class="bi bi-shield-check"></i>
            Tus datos están seguros con nosotros
        </div>

    </div>

</div>

</body>
</html>