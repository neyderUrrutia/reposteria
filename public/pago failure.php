<?php
session_start();
$pedido_id = $_GET['pedido'] ?? null;
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pago fallido | Atrato Dulce</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
    :root { --cream:#fdf6ee; --warm:#f5e6d0; --mocha:#3b2314; --caramel:#c0703a; }
    body { background:var(--cream); font-family:'DM Sans',sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem 1rem; }
    .card-status { background:white; border-radius:20px; box-shadow:0 8px 30px rgba(59,35,20,.1); padding:2.5rem 2rem; max-width:420px; width:100%; text-align:center; position:relative; overflow:hidden; }
    .card-status::before { content:''; position:absolute; top:0;left:0;right:0; height:4px; background:linear-gradient(90deg,#c0703a,#d4737a); }
    .icon-wrap { width:72px;height:72px; background:#fdeeed; border-radius:50%; display:flex;align-items:center;justify-content:center; margin:0 auto 1.25rem; font-size:2rem; color:#b04a52; }
    h2 { font-family:'Cormorant Garamond',serif; color:var(--mocha); font-size:1.8rem; margin-bottom:.5rem; }
    .sub { color:#8a6f5e; font-size:14px; margin-bottom:1.5rem; }
    .btn-retry { background:var(--caramel); color:white; border:none; border-radius:50px; padding:11px 28px; font-size:14px; font-weight:500; text-decoration:none; transition:.3s; display:inline-flex; align-items:center; gap:8px; margin:4px; }
    .btn-retry:hover { background:var(--mocha); color:white; }
    .btn-home { background:transparent; color:var(--mocha); border:1.5px solid var(--mocha); border-radius:50px; padding:10px 28px; font-size:14px; font-weight:500; text-decoration:none; transition:.3s; display:inline-flex; align-items:center; gap:8px; margin:4px; }
    .btn-home:hover { background:var(--mocha); color:white; }
</style>
</head>
<body>
<div class="card-status">
    <div class="icon-wrap"><i class="bi bi-x-lg"></i></div>
    <h2>Pago no completado</h2>
    <p class="sub">Hubo un problema con tu pago. No se realizó ningún cobro. Puedes intentarlo de nuevo.</p>
    <a href="carrito.php" class="btn-retry"><i class="bi bi-arrow-repeat"></i> Intentar de nuevo</a>
    <a href="index.php" class="btn-home"><i class="bi bi-house"></i> Ir al inicio</a>
</div>
</body>
</html>