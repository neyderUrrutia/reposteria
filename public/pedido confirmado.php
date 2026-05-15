<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\BSON\ObjectId;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$pedido_id = $_GET['pedido'] ?? null;
$pedido    = null;

if ($pedido_id) {
    try {
        $pedido = $db->pedidos->findOne(['_id' => new ObjectId($pedido_id)]);

        // Enviar correo de confirmación al cliente
        if ($pedido && !empty($pedido['correo'])) {
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'urrutianeyder002@gmail.com';
                $mail->Password   = 'cezfumycecxwuqez';
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('urrutianeyder002@gmail.com', 'Atrato Dulce');
                $mail->addAddress($pedido['correo'], $pedido['nombre_cliente'] ?? 'Cliente');
                $mail->isHTML(true);
                $mail->Subject = '🧁 Pedido recibido - Atrato Dulce';

                $tabla_productos = '';
                foreach ($pedido['productos'] as $item) {
                    $sub = ($item['precio'] ?? 0) * ($item['cantidad'] ?? 1);
                    $tabla_productos .= "
                    <tr>
                        <td style='padding:10px; border-bottom:1px solid #f1e4d8;'>{$item['nombre']}</td>
                        <td style='padding:10px; border-bottom:1px solid #f1e4d8; text-align:center;'>{$item['cantidad']}</td>
                        <td style='padding:10px; border-bottom:1px solid #f1e4d8; text-align:right;'>$" . number_format($sub, 0, ',', '.') . "</td>
                    </tr>";
                }

                $total_fmt = '$' . number_format($pedido['total'] ?? 0, 0, ',', '.');

                $mail->Body = "
                <div style='font-family:DM Sans,sans-serif; background:#fdf6ee; padding:30px;'>
                  <div style='max-width:560px; margin:0 auto; background:white; border-radius:20px; overflow:hidden; box-shadow:0 4px 20px rgba(59,35,20,0.1);'>

                    <div style='background:#3b2314; padding:30px; text-align:center;'>
                      <h1 style='font-family:Georgia,serif; color:#c9a84c; font-size:1.6rem; margin:0;'>Atrato Dulce</h1>
                      <p style='color:rgba(255,255,255,0.6); font-size:12px; margin:6px 0 0; letter-spacing:2px; text-transform:uppercase;'>Confirmación de pedido</p>
                    </div>

                    <div style='height:4px; background:linear-gradient(90deg,#3b2314,#c0703a);'></div>

                    <div style='padding:30px;'>

                      <p style='color:#3b2314; font-size:15px; margin-bottom:6px;'>
                        Hola <strong>{$pedido['nombre_cliente']}</strong>, ¡recibimos tu pedido! 🎉
                      </p>
                      <p style='color:#8a6f5e; font-size:13px; margin-bottom:24px;'>
                        Tu pedido está registrado. Recuerda que el pago será <strong>en efectivo al momento de la entrega</strong>.
                      </p>

                      <div style='background:#f5e6d0; border-radius:12px; padding:14px 18px; margin-bottom:20px; font-size:13px; color:#3b2314;'>
                        <div style='display:flex; justify-content:space-between; margin-bottom:6px;'>
                          <span style='color:#8a6f5e;'>ID del pedido</span>
                          <span style='font-weight:600;'>#{$pedido_id}</span>
                        </div>
                        <div style='display:flex; justify-content:space-between;'>
                          <span style='color:#8a6f5e;'>Método de pago</span>
                          <span>💵 Efectivo al recibir</span>
                        </div>
                      </div>

                      <table style='width:100%; border-collapse:collapse; font-size:13px;'>
                        <thead>
                          <tr style='background:#f5e6d0;'>
                            <th style='padding:10px; text-align:left; color:#3b2314; font-weight:600;'>Producto</th>
                            <th style='padding:10px; text-align:center; color:#3b2314; font-weight:600;'>Cant.</th>
                            <th style='padding:10px; text-align:right; color:#3b2314; font-weight:600;'>Subtotal</th>
                          </tr>
                        </thead>
                        <tbody>{$tabla_productos}</tbody>
                      </table>

                      <div style='display:flex; justify-content:space-between; align-items:center;
                                  margin-top:16px; padding-top:14px; border-top:2px solid #f1e4d8;'>
                        <span style='font-size:14px; color:#8a6f5e;'>Total a pagar</span>
                        <span style='font-family:Georgia,serif; font-size:1.5rem; font-weight:700; color:#c0703a;'>{$total_fmt}</span>
                      </div>

                      <p style='font-size:12px; color:#b0907a; margin-top:24px; text-align:center;'>
                        Pronto nos pondremos en contacto contigo para coordinar la entrega. 🍰
                      </p>

                    </div>

                    <div style='background:#3b2314; padding:18px; text-align:center;'>
                      <p style='color:rgba(255,255,255,0.4); font-size:11px; margin:0;'>
                        © Atrato Dulce · Quibdó, Chocó, Colombia
                      </p>
                    </div>

                  </div>
                </div>
                ";

                $mail->send();

            } catch (Exception $e) {
                // Silencioso
            }
        }

    } catch (\Exception $e) {
        // ID inválido
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>¡Pedido confirmado! | Atrato Dulce</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
    :root { --cream:#fdf6ee; --warm:#f5e6d0; --mocha:#3b2314; --caramel:#c0703a; }
    body { background:var(--cream); font-family:'DM Sans',sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem 1rem; }

    .card-confirmado {
        background: white;
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(59,35,20,.1);
        padding: 2.5rem 2rem;
        max-width: 460px;
        width: 100%;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .card-confirmado::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--mocha), var(--caramel));
    }

    .icon-wrap {
        width: 72px; height: 72px;
        background: #faeada;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1.25rem;
        font-size: 2rem;
        color: var(--caramel);
    }

    h2 { font-family:'Cormorant Garamond',serif; color:var(--mocha); font-size:1.8rem; margin-bottom:.5rem; }
    .sub { color:#8a6f5e; font-size:14px; margin-bottom:1.5rem; }

    .info-box {
        background: var(--warm);
        border-radius: 12px;
        padding: 1rem 1.25rem;
        text-align: left;
        font-size: 13px;
        color: var(--mocha);
        margin-bottom: 1rem;
    }
    .info-row { display:flex; justify-content:space-between; padding:5px 0; border-bottom:1px solid rgba(192,112,58,.15); }
    .info-row:last-child { border:none; }
    .info-label { color:#8a6f5e; }

    .efectivo-note {
        background: #faf3e0;
        border: 1px solid #e8d5a0;
        border-radius: 12px;
        padding: 0.9rem 1.1rem;
        font-size: 13px;
        color: #7a5c10;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        text-align: left;
    }
    .efectivo-note i { font-size: 1.1rem; flex-shrink: 0; margin-top: 1px; }

    .btn-home {
        background: var(--caramel);
        color: white; border: none;
        border-radius: 50px; padding: 11px 28px;
        font-size: 14px; font-weight: 500;
        text-decoration: none; transition: .3s;
        display: inline-flex; align-items: center; gap: 8px;
    }
    .btn-home:hover { background: var(--mocha); color: white; }

    .correo-note { font-size: 12px; color: #b0907a; margin-top: 1rem; }
</style>
</head>
<body>

<div class="card-confirmado">

    <div class="icon-wrap">🧁</div>

    <h2>¡Pedido confirmado!</h2>
    <p class="sub">Tu pedido fue registrado exitosamente.</p>

    <?php if ($pedido): ?>
    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Cliente</span>
            <span><?= htmlspecialchars($pedido['nombre_cliente'] ?? '') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">ID Pedido</span>
            <span>#<?= htmlspecialchars($pedido_id) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Total</span>
            <span style="color:var(--caramel);font-weight:700;">
                $<?= number_format($pedido['total'] ?? 0, 0, ',', '.') ?>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Estado</span>
            <span>⏳ Pendiente</span>
        </div>
    </div>
    <?php endif; ?>

    <div class="efectivo-note">
        <i class="bi bi-cash-coin"></i>
        <span>Recuerda que el pago es <strong>en efectivo al momento de la entrega</strong>. Pronto nos pondremos en contacto contigo.</span>
    </div>

    <a href="index.php" class="btn-home">
        <i class="bi bi-house"></i> Volver al inicio
    </a>

    <p class="correo-note">📧 Te enviamos un correo con el resumen de tu pedido.</p>

</div>

</body>
</html>