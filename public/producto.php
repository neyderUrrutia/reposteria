<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

use MongoDB\BSON\ObjectId;

$id = $_GET['id'] ?? null;
if (!$id) die("Producto no especificado.");

try {
    $producto = $db->productos->findOne(['_id' => new ObjectId($id)]);
} catch (Exception $e) {
    die("ID inválido.");
}

if (!$producto) die("Producto no encontrado.");
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($producto->nombre ?? '') ?> | Atrato Dulce</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<link href="assets/style.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>

<div class="stripe"></div>
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- BANNER PRODUCTO -->
<section class="product-detail-banner"
  style="background: linear-gradient(rgba(59,35,20,.65), rgba(59,35,20,.65)),
         url('assets/img/producto-bg.jpg') center/cover no-repeat; margin-top:76px;">
  <div class="text-center" data-aos="fade-up">
    <p style="font-size:.72rem; letter-spacing:3px; text-transform:uppercase;
              color:var(--gold,#c9a84c); font-weight:600; margin-bottom:.5rem;">
      <?= h($producto->categoria ?? '') ?>
    </p>
    <h1 style="font-family:'Cormorant Garamond',serif; font-size:clamp(2rem,5vw,3rem); color:#fff;">
      <?= h($producto->nombre ?? '') ?>
    </h1>
    <p style="color:rgba(255,255,255,.75);">Endúlzate con Atrato Dulce 💕</p>
  </div>
</section>


<!-- DETALLE -->
<div class="container my-5" data-aos="fade-up" style="max-width:960px;">
  <div class="row align-items-center g-5">

    <!-- Imagen -->
    <div class="col-md-6 text-center">
      <div style="border-radius:var(--radius,18px); overflow:hidden;
                  box-shadow:0 12px 48px rgba(59,35,20,.15); background:var(--warm,#f5e6d0);">
        <?php if (!empty($producto->imagen)): ?>
          <img src="assets/uploads/<?= h($producto->imagen) ?>"
               style="width:100%; max-height:450px; object-fit:cover; display:block;">
        <?php else: ?>
          <div style="height:350px; display:flex; align-items:center; justify-content:center;
                      font-size:5rem; color:var(--caramel,#c0703a); opacity:.3;">
            <i class="bi bi-image"></i>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Info -->
    <div class="col-md-6">
      <p style="font-size:.7rem; letter-spacing:3px; text-transform:uppercase;
                color:var(--caramel,#c0703a); font-weight:600; margin-bottom:.5rem;">
        <?= h($producto->categoria ?? '') ?>
      </p>

      <h2 style="font-family:'Cormorant Garamond',serif; font-size:2.4rem;
                 color:var(--mocha,#3b2314); margin-bottom:1rem;">
        <?= h($producto->nombre ?? '') ?>
      </h2>

      <p style="color:var(--muted,#8a6f5e); line-height:1.7; font-size:.97rem;">
        <?= nl2br(h($producto->descripcion ?? '')) ?>
      </p>

      <div style="font-family:'Cormorant Garamond',serif; font-size:2.6rem;
                  font-weight:600; color:var(--caramel,#c0703a); margin:1.2rem 0;">
        $<?= number_format($producto->precio ?? 0, 0, ',', '.') ?>
      </div>

      <hr style="border-color:var(--warm,#f5e6d0); margin:1.2rem 0;">

      <div class="d-flex flex-wrap gap-3">
        <a href="https://wa.link/37wo38?text=Hola! 👋 Estoy interesado en <?= urlencode($producto->nombre ?? '') ?>"
           target="_blank"
           class="btn btn-success px-4">
          <i class="bi bi-whatsapp me-1"></i> Pedir por WhatsApp
        </a>
        <a href="catalogo.php" class="btn btn-outline-mocha px-4">
          <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
      </div>
    </div>

  </div>
</div>


<!-- RELACIONADOS -->
<?php
$relacionados = $db->productos->find([
    'categoria' => $producto->categoria,
    '_id' => ['$ne' => $producto->_id]
], ['limit' => 4]);
$relArray = iterator_to_array($relacionados);
?>

<?php if (!empty($relArray)): ?>
<section class="related-section">
  <div class="container" data-aos="fade-up">

    <div class="section-header">
      <span class="label">Puede que también te guste</span>
      <h2>Productos relacionados</h2>
      <div class="rule"></div>
    </div>

    <div class="row g-4 justify-content-center">
      <?php foreach ($relArray as $r): ?>
      <div class="col-sm-6 col-md-3">
        <div class="product-card">
          <div class="img-wrap">
            <?php if (!empty($r->imagen)): ?>
              <img src="assets/uploads/<?= h($r->imagen ?? '') ?>"
                   alt="<?= h($r->nombre ?? '') ?>">
            <?php else: ?>
              <div class="img-placeholder"><i class="bi bi-image"></i></div>
            <?php endif; ?>
          </div>
          <div class="card-body">
            <h5 class="card-title"><?= h($r->nombre ?? '') ?></h5>
            <p class="card-text"><?= substr(h($r->descripcion ?? ''), 0, 55) ?>…</p>
            <div class="price">$<?= number_format($r->precio ?? 0, 0, ',', '.') ?></div>
            <a href="producto.php?id=<?= (string)$r->_id ?>"
               class="btn btn-outline-mocha w-100">Ver más</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>
<?php endif; ?>


<?php include __DIR__ . '/../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({ duration: 800, once: true });</script>

</body>
</html>