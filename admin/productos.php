<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db.php';

use MongoDB\BSON\ObjectId;

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); exit;
}

$collection = $db->productos;

// ELIMINAR
if (isset($_GET['eliminar'])) {
    try {
        $collection->deleteOne(['_id' => new ObjectId($_GET['eliminar'])]);
    } catch (Exception $e) {}
    header("Location: productos.php"); exit;
}

// TOGGLE DISPONIBLE
if (isset($_GET['toggle'])) {
    try {
        $prod = $collection->findOne(['_id' => new ObjectId($_GET['toggle'])]);
        if ($prod) {
            $nuevo = !($prod['disponible'] ?? true);
            $collection->updateOne(['_id' => new ObjectId($_GET['toggle'])], ['$set' => ['disponible' => $nuevo]]);
        }
    } catch (Exception $e) {}
    header("Location: productos.php"); exit;
}

// FILTRO
$filtro_cat = $_GET['cat'] ?? 'todas';
$filtro = [];
if ($filtro_cat !== 'todas') $filtro['categoria'] = $filtro_cat;

$productos = $collection->find($filtro, ['sort' => ['creado_en' => -1]])->toArray();

$total_productos  = $collection->countDocuments();
$disponibles      = $collection->countDocuments(['disponible' => true]);
$no_disponibles   = $total_productos - $disponibles;

$categorias_db = $collection->distinct('categoria', []);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Productos | Atrato Dulce Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    :root {
      --cream:   #fdf6ee;
      --warm:    #f5e6d0;
      --mocha:   #3b2314;
      --caramel: #c0703a;
      --rose:    #d4737a;
      --gold:    #c9a84c;
      --text:    #2c1a0e;
      --muted:   #8a6f5e;
      --radius:  14px;
      --shadow:  0 4px 24px rgba(59,35,20,.09);
    }
    * { box-sizing: border-box; }
    body { background: var(--cream); font-family: 'DM Sans', sans-serif; color: var(--text); margin: 0; }

    /* NAVBAR */
    .navbar-ad { background: var(--mocha); padding: 0.85rem 2rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; border-bottom: 3px solid var(--gold); }
    .navbar-brand-ad { display: flex; align-items: center; gap: 10px; text-decoration: none; }
    .navbar-logo { width: 36px; height: 36px; background: var(--gold); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; }
    .navbar-title { font-family: 'Cormorant Garamond', serif; color: var(--gold); font-size: 1.15rem; font-weight: 700; }
    .navbar-links { display: flex; align-items: center; gap: 8px; }
    .nav-link-ad { color: #CECBF6; text-decoration: none; font-size: 13px; padding: 6px 14px; border-radius: 6px; border: 1px solid rgba(206,203,246,0.3); }
    .nav-link-ad:hover { background: rgba(206,203,246,.12); color: #fff; }
    .btn-volver { background: var(--gold); color: var(--mocha); border: none; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 500; text-decoration: none; }
    .btn-volver:hover { opacity: .85; color: var(--mocha); }

    /* LAYOUT */
    .dash-container { max-width: 1280px; margin: 0 auto; padding: 2rem 1.5rem; }

    /* MÉTRICAS RÁPIDAS */
    .mini-metrics { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 1.75rem; }
    .mini-card { background: #fff; border-radius: var(--radius); padding: 1rem 1.25rem; box-shadow: var(--shadow); display: flex; align-items: center; gap: 12px; }
    .mini-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
    .ic-total { background: #EEEDFE; color: #534AB7; }
    .ic-disp  { background: #EAF3DE; color: #3B6D11; }
    .ic-nodisp{ background: #FCEBEB; color: #A32D2D; }
    .mini-label { font-size: 11px; color: var(--muted); margin-bottom: 2px; }
    .mini-val   { font-size: 20px; font-weight: 700; color: var(--mocha); }

    /* HEADER + ACCIONES */
    .page-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 1.25rem; }
    .page-header h2 { font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; font-weight: 700; color: var(--mocha); margin: 0; }
    .btn-nuevo {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--caramel); color: #fff; border: none;
      border-radius: 50px; padding: 8px 20px; font-size: 13.5px;
      font-weight: 500; text-decoration: none; transition: background .2s;
    }
    .btn-nuevo:hover { background: var(--mocha); color: #fff; }

    /* FILTROS */
    .filtros { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .filtro-btn { padding: 6px 14px; border-radius: 50px; font-size: 12.5px; font-weight: 500; text-decoration: none; border: 1.5px solid rgba(59,35,20,.15); color: var(--muted); background: #fff; transition: all .2s; }
    .filtro-btn:hover { border-color: var(--caramel); color: var(--caramel); }
    .filtro-btn.activo { background: var(--mocha); color: #fff; border-color: var(--mocha); }

    /* TABLA */
    .table-card { background: #fff; border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
    .table-admin { width: 100%; border-collapse: collapse; }
    .table-admin thead { background: var(--warm); }
    .table-admin th { padding: 0.9rem 1.1rem; font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); border: none; }
    .table-admin td { padding: 0.85rem 1.1rem; border-bottom: 1px solid #f5ede3; vertical-align: middle; }
    .table-admin tr:last-child td { border: none; }
    .table-admin tbody tr:hover { background: #fffaf5; }

    .prod-img { width: 52px; height: 52px; border-radius: 10px; object-fit: cover; }
    .prod-placeholder { width: 52px; height: 52px; border-radius: 10px; background: var(--warm); display: flex; align-items: center; justify-content: center; color: var(--caramel); font-size: 1.3rem; }
    .prod-nombre { font-weight: 500; color: var(--mocha); font-size: 13.5px; }
    .prod-cat { font-size: 11px; color: var(--muted); margin-top: 2px; }
    .prod-precio { font-family: 'Cormorant Garamond', serif; font-size: 1.1rem; color: var(--caramel); font-weight: 600; }

    .badge-disp { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 10px; border-radius: 20px; }
    .disp-si { background: #EAF3DE; color: #3B6D11; }
    .disp-no { background: #FCEBEB; color: #A32D2D; }

    .btn-editar { background: var(--warm); color: var(--mocha); border: none; border-radius: 8px; padding: 5px 12px; font-size: 12.5px; text-decoration: none; transition: background .2s; }
    .btn-editar:hover { background: var(--gold); }
    .btn-toggle { background: #EEEDFE; color: #534AB7; border: none; border-radius: 8px; padding: 5px 12px; font-size: 12.5px; text-decoration: none; transition: background .2s; }
    .btn-toggle:hover { background: #534AB7; color: #fff; }
    .btn-eliminar { background: #fdecea; color: #c0392b; border: none; border-radius: 8px; padding: 5px 10px; font-size: 13px; cursor: pointer; transition: background .2s; text-decoration: none; }
    .btn-eliminar:hover { background: #c0392b; color: #fff; }

    .empty-row td { text-align: center; padding: 3rem; color: var(--muted); font-size: 14px; }

    @media (max-width: 768px) {
      .mini-metrics { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<nav class="navbar-ad">
  <a href="index.php" class="navbar-brand-ad">
    <div class="navbar-logo"></div>
    <span class="navbar-title">Atrato Dulce</span>
  </a>
  <div class="navbar-links">
    <a href="pedidos.php" class="nav-link-ad"><i class="bi bi-cart3 me-1"></i>Pedidos</a>
    <a href="index.php" class="btn-volver"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
  </div>
</nav>

<div class="dash-container">

  <!-- MÉTRICAS RÁPIDAS -->
  <div class="mini-metrics">
    <div class="mini-card">
      <div class="mini-icon ic-total"><i class="bi bi-box-seam"></i></div>
      <div><div class="mini-label">Total productos</div><div class="mini-val"><?= $total_productos ?></div></div>
    </div>
    <div class="mini-card">
      <div class="mini-icon ic-disp"><i class="bi bi-check-circle"></i></div>
      <div><div class="mini-label">Disponibles</div><div class="mini-val"><?= $disponibles ?></div></div>
    </div>
    <div class="mini-card">
      <div class="mini-icon ic-nodisp"><i class="bi bi-x-circle"></i></div>
      <div><div class="mini-label">No disponibles</div><div class="mini-val"><?= $no_disponibles ?></div></div>
    </div>
  </div>

  <!-- HEADER -->
  <div class="page-header">
    <h2>Gestión de Productos</h2>
    <a href="crear_producto.php" class="btn-nuevo"><i class="bi bi-plus-circle"></i> Nuevo producto</a>
  </div>

  <!-- FILTROS POR CATEGORÍA -->
  <div class="filtros">
    <a href="?cat=todas" class="filtro-btn <?= $filtro_cat==='todas' ? 'activo' : '' ?>">Todas</a>
    <?php foreach ($categorias_db as $cat): ?>
    <a href="?cat=<?= urlencode($cat) ?>" class="filtro-btn <?= $filtro_cat===$cat ? 'activo' : '' ?>">
      <?= htmlspecialchars($cat) ?>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- TABLA -->
  <div class="table-card">
    <div class="table-responsive">
      <table class="table-admin">
        <thead>
          <tr>
            <th>Imagen</th>
            <th>Producto</th>
            <th>Precio</th>
            <th>Categoría</th>
            <th>Disponible</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($productos)): ?>
          <tr class="empty-row">
            <td colspan="6">
              <i class="bi bi-box" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
              No hay productos en esta categoría.
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($productos as $p):
            $disponible = $p['disponible'] ?? true;
          ?>
          <tr>
            <td>
              <?php if (!empty($p['imagen'])): ?>
                <img src="../public/assets/uploads/<?= htmlspecialchars($p['imagen']) ?>" class="prod-img">
              <?php else: ?>
                <div class="prod-placeholder"><i class="bi bi-image"></i></div>
              <?php endif; ?>
            </td>
            <td>
              <div class="prod-nombre"><?= htmlspecialchars($p['nombre'] ?? '') ?></div>
              <div class="prod-cat"><?= htmlspecialchars(substr($p['descripcion'] ?? '', 0, 40)) ?>...</div>
            </td>
            <td>
              <span class="prod-precio">$<?= number_format($p['precio'] ?? 0, 0, ',', '.') ?></span>
            </td>
            <td style="font-size:13px;color:var(--muted);"><?= htmlspecialchars($p['categoria'] ?? '—') ?></td>
            <td>
              <span class="badge-disp <?= $disponible ? 'disp-si' : 'disp-no' ?>">
                <i class="bi bi-<?= $disponible ? 'check-circle-fill' : 'x-circle-fill' ?>"></i>
                <?= $disponible ? 'Sí' : 'No' ?>
              </span>
            </td>
            <td>
              <div class="d-flex gap-2 flex-wrap">
                <a href="editar_producto.php?id=<?= (string)$p['_id'] ?>" class="btn-editar">
                  <i class="bi bi-pencil"></i> Editar
                </a>
                <a href="?toggle=<?= (string)$p['_id'] ?>" class="btn-toggle"
                   onclick="return confirm('¿Cambiar disponibilidad?')">
                  <i class="bi bi-toggle-<?= $disponible ? 'on' : 'off' ?>"></i>
                </a>
                <a href="?eliminar=<?= (string)$p['_id'] ?>" class="btn-eliminar"
                   onclick="return confirm('¿Eliminar este producto?')">
                  <i class="bi bi-trash3"></i>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>