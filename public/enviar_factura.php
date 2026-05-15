<<?php

session_start();

require_once __DIR__ . '/../includes/enviar_factura.php';

// ID DEL PEDIDO
$pedido_id = $_GET['pedido'] ?? null;

// DATOS DEL CLIENTE
$correo_cliente = $_SESSION['correo'] ?? '';
$nombre_cliente = $_SESSION['nombre'] ?? 'Cliente';
$total = $_SESSION['total'] ?? 0;

// ENVIAR FACTURA SOLO SI HAY CORREO
if ($correo_cliente) {

    enviarFactura(
        $correo_cliente,
        $nombre_cliente,
        $pedido_id,
        $total
    );
}

?>

<!doctype html>
<html lang="es">

<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Pago pendiente | Atrato Dulce</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">

<style>

    :root{
        --cream:#fdf6ee;
        --warm:#f5e6d0;
        --mocha:#3b2314;
        --caramel:#c0703a;
        --gold:#c9a84c;
        --muted:#8a6f5e;
    }

    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
    }

    body{

        background:linear-gradient(
            135deg,
            var(--cream),
            var(--warm)
        );

        font-family:'DM Sans',sans-serif;

        min-height:100vh;

        display:flex;
        align-items:center;
        justify-content:center;

        padding:2rem 1rem;
    }

    .card-status{

        background:white;

        border-radius:28px;

        box-shadow:
            0 10px 35px rgba(59,35,20,.10),
            0 4px 10px rgba(59,35,20,.05);

        padding:3rem 2.3rem;

        max-width:460px;
        width:100%;

        text-align:center;

        position:relative;

        overflow:hidden;
    }

    .card-status::before{

        content:'';

        position:absolute;

        top:0;
        left:0;
        right:0;

        height:6px;

        background:linear-gradient(
            90deg,
            var(--caramel),
            var(--gold)
        );
    }

    .icon-wrap{

        width:90px;
        height:90px;

        background:#faf3e0;

        border-radius:50%;

        display:flex;
        align-items:center;
        justify-content:center;

        margin:0 auto 1.5rem;

        font-size:2.6rem;

        color:#8a6510;

        box-shadow:0 6px 18px rgba(192,112,58,.15);
    }

    h2{

        font-family:'Cormorant Garamond',serif;

        color:var(--mocha);

        font-size:2.2rem;

        margin-bottom:.7rem;

        font-weight:700;
    }

    .sub{

        color:var(--muted);

        font-size:15px;

        line-height:1.6;

        margin-bottom:1.8rem;
    }

    .info-box{

        background:var(--warm);

        border-radius:16px;

        padding:1.2rem 1.3rem;

        font-size:14px;

        color:var(--mocha);

        margin-bottom:2rem;

        line-height:1.6;
    }

    .pedido-id{

        margin-top:12px;

        display:inline-block;

        background:white;

        padding:8px 14px;

        border-radius:10px;

        font-weight:600;

        color:var(--caramel);

        box-shadow:0 2px 8px rgba(0,0,0,.05);
    }

    .btn-home{

        background:linear-gradient(
            135deg,
            var(--caramel),
            var(--gold)
        );

        color:white;

        border:none;

        border-radius:50px;

        padding:13px 30px;

        font-size:14px;

        font-weight:600;

        text-decoration:none;

        transition:.3s ease;

        display:inline-flex;
        align-items:center;
        gap:8px;
    }

    .btn-home:hover{

        background:var(--mocha);

        transform:translateY(-2px);

        color:white;
    }

</style>

</head>

<body>

<div class="card-status">

    <div class="icon-wrap">

        <i class="bi bi-hourglass-split"></i>

    </div>

    <h2>
        Pago pendiente
    </h2>

    <p class="sub">

        Tu pago está siendo procesado correctamente.
        Te notificaremos cuando sea confirmado por el sistema.

    </p>

    <div class="info-box">

        ⏳ Los pagos en efectivo pueden tardar algunos minutos
        o incluso horas dependiendo del método utilizado.

        <?php if ($pedido_id): ?>

            <div class="pedido-id">

                Pedido #<?= htmlspecialchars($pedido_id) ?>

            </div>

        <?php endif; ?>

    </div>

    <a href="index.php" class="btn-home">

        <i class="bi bi-house-door-fill"></i>

        Volver al inicio

    </a>

</div>

</body>
</html>