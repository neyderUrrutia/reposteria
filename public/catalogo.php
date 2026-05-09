<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';


if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$categoria = $_GET['cat'] ?? 'Todas';
$busqueda  = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

$collection = $db->productos;

$filtro = ['disponible' => ['$in' => [true, 1, "true"]]];

if ($busqueda) {
    $filtro['$or'] = [
        ['nombre'      => ['$regex' => $busqueda, '$options' => 'i']],
        ['descripcion' => ['$regex' => $busqueda, '$options' => 'i']]
    ];
}

if ($categoria !== 'Todas') {
    $filtro['categoria'] = $categoria;
}

$productos = $collection->find($filtro, ['sort' => ['creado_en' => -1]]);

$categorias = [
    ['label' => 'Todas',     'icon' => 'bi-grid-fill'],
    ['label' => 'Tortas',    'icon' => 'bi-cake2'],
    ['label' => 'Postres',   'icon' => 'bi-cup-straw'],
    ['label' => 'Panadería', 'icon' => 'bi-bag-heart'],
    ['label' => 'Galletería','icon' => 'bi-cookie'],
    ['label' => 'Bebidas',   'icon' => 'bi-cup-hot'],
];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Catálogo | Atrato Dulce</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

  <style>
    /* ── VARIABLES (igual que index) ── */
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

    /* ── STRIPE (igual que index) ── */
    .stripe {
      height: 4px;
      background: linear-gradient(90deg, var(--caramel), var(--rose), var(--gold));
    }

    /* ── PROMO BAND (igual que index) ── */
    .promo-band {
      background: var(--mocha);
      color: #fff;
      text-align: center;
      padding: 1.1rem;
      font-size: 14px;
    }

    /* ── HERO DEL CATÁLOGO ── */
    .catalogo-hero {
      background: var(--warm);
      padding: 3.5rem 0 2.5rem;
      text-align: center;
      border-bottom: 1px solid rgba(192,112,58,.15);
    }
    .catalogo-hero .label {
      font-size: .72rem;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: var(--caramel);
      display: block;
      margin-bottom: 0.5rem;
    }
    .catalogo-hero h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: clamp(2rem, 5vw, 3rem);
      font-weight: 600;
      color: var(--mocha);
      margin-bottom: 0.5rem;
    }
    .rule {
      width: 48px;
      height: 2px;
      background: linear-gradient(90deg, var(--caramel), var(--rose));
      margin: 0.75rem auto 0;
    }

    /* ── BUSCADOR ── */
    .search-wrap {
      max-width: 520px;
      margin: 2rem auto 0;
    }
    .search-wrap .form-control {
      border-radius: 50px 0 0 50px;
      border: 1.5px solid rgba(192,112,58,.35);
      background: #fff;
      padding: 0.65rem 1.25rem;
      font-size: 14px;
      color: var(--text);
    }
    .search-wrap .form-control:focus {
      border-color: var(--caramel);
      box-shadow: 0 0 0 3px rgba(192,112,58,.12);
    }
    .search-wrap .btn-buscar {
      border-radius: 0 50px 50px 0;
      background: var(--caramel);
      color: #fff;
      border: none;
      padding: 0.65rem 1.5rem;
      font-size: 14px;
      transition: background .2s;
    }
    .search-wrap .btn-buscar:hover { background: var(--mocha); }

    /* ── LAYOUT PRINCIPAL ── */
    .catalogo-body {
      padding: 3rem 0 5rem;
    }

    /* ── SIDEBAR CATEGORÍAS ── */
    .sidebar {
      background: #fff;
      border-radius: var(--radius);
      padding: 1.5rem;
      box-shadow: var(--shadow);
      position: sticky;
      top: 80px;
    }
    .sidebar-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.15rem;
      font-weight: 600;
      color: var(--mocha);
      margin-bottom: 1rem;
      padding-bottom: 0.5rem;
      border-bottom: 2px solid var(--warm);
    }
    .cat-link {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 14px;
      border-radius: 50px;
      text-decoration: none;
      color: var(--text);
      font-size: 13.5px;
      margin-bottom: 6px;
      transition: all .2s;
    }
    .cat-link i { color: var(--caramel); font-size: 15px; }
    .cat-link:hover { background: var(--warm); color: var(--mocha); }
    .cat-link.activa {
      background: var(--mocha);
      color: #fff !important;
    }
    .cat-link.activa i { color: var(--gold); }

    /* ── TARJETAS PRODUCTO (igual que index) ── */
    .product-card {
      background: #fff;
      border-radius: var(--radius);
      overflow: hidden;
      transition: .3s;
      height: 100%;
      box-shadow: 0 2px 16px rgba(59,35,20,.07);
    }
    .product-card:hover {
      transform: translateY(-8px);
      box-shadow: var(--shadow);
    }
    .img-wrap {
      height: 200px;
      overflow: hidden;
    }
    .img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform .4s;
    }
    .product-card:hover .img-wrap img { transform: scale(1.05); }
    .img-placeholder {
      width: 100%; height: 100%;
      background: var(--warm);
      display: flex; align-items: center; justify-content: center;
      font-size: 2.5rem; color: var(--muted);
    }
    .card-body-ad { padding: 1rem 1.1rem 1.2rem; }
    .card-body-ad h6 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.05rem;
      font-weight: 600;
      color: var(--mocha);
      margin-bottom: 4px;
    }
    .card-body-ad p {
      font-size: 12.5px;
      color: var(--muted);
      margin-bottom: 8px;
      line-height: 1.4;
    }
    .price {
      font-size: 1.3rem;
      color: var(--caramel);
      font-weight: 500;
      margin-bottom: 10px;
    }
    .btn-add {
      background: var(--caramel);
      color: #fff;
      border-radius: 50px;
      font-size: 13px;
      border: none;
      transition: background .2s;
    }
    .btn-add:hover { background: var(--mocha); color: #fff; }
    .btn-outline-mocha {
      border: 1.5px solid var(--mocha);
      color: var(--mocha);
      border-radius: 50px;
      font-size: 13px;
      background: transparent;
      transition: all .2s;
    }
    .btn-outline-mocha:hover { background: var(--mocha); color: #fff; }

    /* ── BADGE CATEGORÍA ── */
    .cat-badge {
      display: inline-block;
      font-size: 10px;
      padding: 2px 10px;
      border-radius: 20px;
      background: var(--warm);
      color: var(--caramel);
      font-weight: 500;
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: .05em;
    }

    /* ── VACÍO ── */
    .empty-state {
      text-align: center;
      padding: 4rem 1rem;
      color: var(--muted);
    }
    .empty-state i { font-size: 3rem; margin-bottom: 1rem; color: var(--warm); }

    /* ── CONTEO ── */
    .result-count {
      font-size: 13px;
      color: var(--muted);
      margin-bottom: 1.25rem;
    }
    .result-count strong { color: var(--caramel); }

    footer { background: #1e1009; color: rgba(255,255,255,.55); padding: 2.5rem; text-align: center; }

    @media (max-width: 767px) {
      .sidebar { position: static; margin-bottom: 1.5rem; }
      .catalogo-hero { padding: 2rem 0 1.5rem; }
    }
  </style>
</head>
<body>

<div class="stripe"></div>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- PROMO -->
 <div class="promo-band">
🚚 Envío gratis en pedidos mayores a <span>$20.000</span> · Pago seguro · Hecho con amor en quibdo chocò
</div>


<!-- HERO -->
<div class="catalogo-hero">
  <span class="label">Encuentra lo que buscas</span>
  <h1>Nuestro Catálogo</h1>
  <div class="rule"></div>

  <!-- BUSCADOR -->
  <form class="search-wrap d-flex" method="GET">
    <?php if ($categoria !== 'Todas'): ?>
      <input type="hidden" name="cat" value="<?= h($categoria) ?>">
    <?php endif; ?>
    <input
      class="form-control"
      type="search"
      name="buscar"
      placeholder="Buscar productos..."
      value="<?= h($busqueda) ?>"
    >
    <button class="btn-buscar" type="submit">
      <i class="bi bi-search"></i> Buscar
    </button>
  </form>
</div>

<!-- CUERPO -->
<section class="catalogo-body">
  <div class="container">
    <div class="row g-4">

      <!-- SIDEBAR -->
      <aside class="col-md-3" data-aos="fade-right">
        <div class="sidebar">
          <div class="sidebar-title">Categorías</div>
          <?php foreach ($categorias as $cat): ?>
            <a
              href="?cat=<?= urlencode($cat['label']) ?><?= $busqueda ? '&buscar='.urlencode($busqueda) : '' ?>"
              class="cat-link <?= ($cat['label'] === $categoria) ? 'activa' : '' ?>"
            >
              <i class="bi <?= $cat['icon'] ?>"></i>
              <?= h($cat['label']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </aside>

      <!-- PRODUCTOS -->
      <div class="col-md-9">

        <?php
        $lista = iterator_to_array($productos);
        $total = count($lista);
        ?>

        <div class="result-count">
          <?php if ($busqueda): ?>
            Resultados para <strong>"<?= h($busqueda) ?>"</strong>:
          <?php endif; ?>
          <strong><?= $total ?></strong> producto<?= $total !== 1 ? 's' : '' ?>
          <?= $categoria !== 'Todas' ? 'en <strong>'.h($categoria).'</strong>' : '' ?>
        </div>

        <?php if ($total > 0): ?>
        <div class="row g-4">
          <?php foreach ($lista as $p): ?>
          <?php
            $id     = (string)$p['_id'];
            $imagen = !empty($p['imagen']) ? $p['imagen'] : null;
          ?>
          <div class="col-sm-6 col-lg-4" data-aos="fade-up">
            <div class="product-card">

              <div class="img-wrap">
                <?php if ($imagen): ?>
                  <img src="assets/uploads/<?= h($imagen) ?>" alt="<?= h($p['nombre'] ?? '') ?>">
                <?php else: ?>
                  <div class="img-placeholder"><i class="bi bi-image"></i></div>
                <?php endif; ?>
              </div>

              <div class="card-body-ad">
                <?php if (!empty($p['categoria'])): ?>
                  <span class="cat-badge"><?= h($p['categoria']) ?></span>
                <?php endif; ?>

                <h6><?= h($p['nombre'] ?? 'Sin nombre') ?></h6>

                <p><?= substr(h($p['descripcion'] ?? ''), 0, 65) ?>…</p>

                <div class="price">$<?= number_format($p['precio'] ?? 0, 0, ',', '.') ?></div>

                <div class="d-flex gap-2">
                  <a href="producto.php?id=<?= $id ?>" class="btn btn-outline-mocha flex-fill btn-sm">
                    Ver más
                  </a>
                  <form action="carrito.php" method="GET" class="flex-fill">
                    <input type="hidden" name="add" value="<?= $id ?>">
                    <button type="submit" class="btn btn-add w-100 btn-sm">Añadir</button>
                  </form>
                </div>
              </div>

            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <?php else: ?>
        <div class="empty-state" data-aos="fade-up">
          <i class="bi bi-basket"></i>
          <h5>No encontramos productos</h5>
          <p>Intenta con otra categoría o un término de búsqueda diferente.</p>
          <a href="catalogo.php" class="btn btn-outline-mocha mt-2">Ver todo el catálogo</a>
        </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({ duration: 750, once: true });</script>

</body>
</html>