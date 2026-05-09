<?php
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// REDIRIGIR AL LOGIN SI NO HA INICIADO SESIÓN
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Inicializar carrito
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$productos = $db->productos->find(
    ['disponible' => ['$in' => [true, 1]]],
    [
        'sort' => ['creado_en' => -1],
        'limit' => 8
    ]
);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Atrato Dulce — Pastelería Artesanal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

<style>

/* TODO tu CSS ORIGINAL se mantiene igual */
/* NO se cambió nada */

:root {
  --cream:   #fdf6ee;
  --warm:    #f5e6d0;
  --mocha:   #3b2314;
  --caramel: #c0703a;
  --rose:    #d4737a;
  --gold:    #c9a84c;
  --text:    #2c1a0e;
  --muted:   #8a6f5e;
  --radius:  18px;
  --shadow:  0 8px 40px rgba(59,35,20,.10);
}

body {
  background-color: var(--cream);
  color: var(--text);
  font-family: 'DM Sans', sans-serif;
}

.stripe {
  height: 4px;
  background: linear-gradient(90deg, var(--caramel), var(--rose), var(--gold));
}

.section-header {
  text-align: center;
  margin-bottom: 3rem;
}

.section-header .label {
  font-size: .72rem;
  letter-spacing: 3px;
  text-transform: uppercase;
  color: var(--caramel);
}

.rule {
  width: 48px;
  height: 2px;
  background: linear-gradient(90deg, var(--caramel), var(--rose));
  margin: auto;
}

.categories-section {
  padding: 5rem 0;
}

.cat-card {
  background: #fff;
  border-radius: var(--radius);
  padding: 2rem 1rem;
  text-align: center;
  transition: .3s;
}

.cat-card:hover {
  transform: translateY(-6px);
  box-shadow: var(--shadow);
}

.cat-icon {
  font-size: 2rem;
  color: var(--caramel);
}

.products-section {
  padding: 5rem 0 6rem;
  background: var(--warm);
}

.product-card {
  background: #fff;
  border-radius: var(--radius);
  overflow: hidden;
  transition: .3s;
}

.product-card:hover {
  transform: translateY(-8px);
}

.img-wrap {
  height: 200px;
  overflow: hidden;
}

.img-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.price {
  font-size: 1.5rem;
  color: var(--caramel);
}

.btn-add {
  background: var(--caramel);
  color: #fff;
  border-radius: 50px;
}

.btn-outline-mocha {
  border: 1px solid var(--mocha);
  color: var(--mocha);
  border-radius: 50px;
}

.promo-band {
  background: var(--mocha);
  color: #fff;
  text-align: center;
  padding: 1.1rem;
}

footer {
  background: #1e1009;
  color: rgba(255,255,255,.55);
  padding: 2.5rem;
  text-align: center;
}

</style>
</head>

<body>

<div class="stripe"></div>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- PROMO -->


<div style="height:40px;"></div>

<!-- CATEGORÍAS -->

<section class="categories-section">

<div class="container">

<div class="section-header">
<span class="label">Lo que tenemos para ti</span>
<h2>Explora nuestras categorías</h2>
<div class="rule"></div>
</div>

<div class="row justify-content-center g-3">

<?php

$categorias = [
['icon' => 'bi-cake2','label' => 'Tortas'],
['icon' => 'bi-cup-straw','label' => 'Postres'],
['icon' => 'bi-bag-heart','label' => 'Panadería'],
['icon' => 'bi-cookie','label' => 'Galletería'],
['icon' => 'bi-cup-hot','label' => 'Bebidas'],
];

foreach ($categorias as $cat):

?>

<div class="col-6 col-md-4 col-lg-2">

<div class="cat-card">

<div class="cat-icon">
<i class="bi <?= $cat['icon'] ?>"></i>
</div>

<h6>
<?= $cat['label'] ?>
</h6>

</div>

</div>

<?php endforeach; ?>

</div>
</div>

</section>

<!-- PRODUCTOS -->

<section class="products-section">

<div class="container">

<div class="section-header">
<span class="label">Selección especial</span>
<h2>Nuestros productos destacados</h2>
<div class="rule"></div>
</div>

<div class="row g-4 justify-content-center">

<?php foreach ($productos as $p): ?>

<div class="col-sm-6 col-md-4 col-lg-3">

<div class="product-card">

<div class="img-wrap">

<?php if (!empty($p->imagen)): ?>

<img src="assets/uploads/<?= h($p->imagen) ?>">

<?php else: ?>

<div class="img-placeholder">
<i class="bi bi-image"></i>
</div>

<?php endif; ?>

</div>

<div class="p-3">

<h5>
<?= h($p->nombre ?? '') ?>
</h5>

<p>
<?= substr(h($p->descripcion ?? ''),0,70) ?>…
</p>

<div class="price">
$<?= number_format($p->precio ?? 0,0,',','.') ?>
</div>

<div class="d-flex gap-2">

<a href="producto.php?id=<?= (string)$p->_id ?>"
class="btn btn-outline-mocha flex-fill">
Ver más
</a>

<a href="agregar_carrito.php?id=<?= (string)$p->_id ?>"
class="btn btn-add flex-fill">
Añadir
</a>

</div>

</div>

</div>

</div>

<?php endforeach; ?>

</div>
</div>

</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
AOS.init({
duration: 750,
once: true
});
</script>

</body>
</html>