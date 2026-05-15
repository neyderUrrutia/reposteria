dos · PHP
Copiar

<?php
if (session_status() === PHP_SESSION_NONE) session_start();
 
use MongoDB\BSON\ObjectId;
 
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); exit;
}
 
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
 
// CAMBIAR ESTADO
if (isset($_POST['pedido_id'], $_POST['estado'])) {
    try {
        $db->pedidos->updateOne(
            ['_id' => new ObjectId($_POST['pedido_id'])],
            ['$set' => ['estado' => $_POST['estado']]]
        );
    } catch (Exception $e) {}
    header("Location: pedidos.php"); exit;
}
 
// ELIMINAR
if (isset($_GET['eliminar'])) {
    try {
        $db->pedidos->deleteOne(['_id' => new ObjectId($_GET['eliminar'])]);
    } catch (Exception $e) {}
    header("Location: pedidos.php"); exit;
}
 
// FILTRO POR ESTADO
$filtro_estado = $_GET['estado'] ?? 'todos';
$filtro = [];
if ($filtro_estado !== 'todos') $filtro['estado'] = $filtro_estado;
 
$pedidos = $db->pedidos->find($filtro, ['sort' => ['fecha' => -1]])->toArray();
 
// Conteos para las pestañas
$todos       = $db->pedidos->countDocuments();
$pendientes  = $db->pedidos->countDocuments(['estado' => 'Pendiente']);
$preparacion = $db->pedidos->countDocuments(['estado' => 'En preparación']);
$listos      = $db->pedidos->countDocuments(['estado' => 'Listo']);
$entregados  = $db->pedidos->countDocuments(['estado' => 'Entregado']);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pedidos | Atrato Dulce Admin</title>
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
    .navbar-ad {
      background: var(--mocha); padding: 0.85rem 2rem;
      display: flex; align-items: center; justify-content: space-between;
      position: sticky; top: 0; z-index: 100;
      border-bottom: 3px solid var(--gold);
    }
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
    .page-header { margin-bottom: 1.75rem; }
    .page-header h2 { font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; font-weight: 700; color: var(--mocha); margin: 0; }
    .page-header p { font-size: 13px; color: var(--muted); margin: 2px 0 0; }
 
    /* FILTROS */
    .filtros { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .filtro-btn {
      padding: 7px 16px; border-radius: 50px; font-size: 12.5px; font-weight: 500;
      text-decoration: none; border: 1.5px solid rgba(59,35,20,.15);
      color: var(--muted); background: #fff; transition: all .2s;
      display: flex; align-items: center; gap: 6px;
    }
    .filtro-btn:hover { border-color: var(--caramel); color: var(--caramel); }
    .filtro-btn.activo { background: var(--mocha); color: #fff; border-color: var(--mocha); }
    .filtro-btn .badge-count {
      background: rgba(255,255,255,.25); color: inherit;
      border-radius: 20px; padding: 1px 7px; font-size: 11px;
    }
    .filtro-btn.activo .badge-count { background: rgba(255,255,255,.2); }
 
    /* TABLA */
    .table-card { background: #fff; border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
    .table-admin { width: 100%; border-collapse: collapse; }
    .table-admin thead { background: var(--warm); }
    .table-admin th {
      padding: 0.9rem 1.1rem; font-size: 11px; font-weight: 500;
      text-transform: uppercase; letter-spacing: .06em; color: var(--muted); border: none;
    }
    .table-admin td {
      padding: 0.9rem 1.1rem; border-bottom: 1px solid #f5ede3;
      vertical-align: middle; font-size: 13.5px; color: var(--text);
    }
    .table-admin tr:last-child td { border: none; }
    .table-admin tbody tr:hover { background: #fffaf5; }
 
    /* ID PEDIDO */
    .pedido-id { font-family: monospace; font-size: 11px; color: var(--muted); }
 
    /* CLIENTE */
    .cliente-info { font-weight: 500; color: var(--mocha); font-size: 13px; }
    .cliente-tel  { font-size: 11.5px; color: var(--muted); }
 
    /* BADGES */
    .badge-estado {
      display: inline-flex; align-items: center; gap: 5px;
      font-size: 11px; font-weight: 500; padding: 4px 12px;
      border-radius: 20px;
    }
    .bs-pendiente  { background: #FAEEDA; color: #854F0B; }
    .bs-preparacion{ background: #EEEDFE; color: #534AB7; }
    .bs-listo      { background: #FFF3CD; color: #6D4C41; }
    .bs-entregado  { background: #EAF3DE; color: #3B6D11; }
    .bs-cancelado  { background: #FCEBEB; color: #A32D2D; }
 
    /* SELECT ESTADO */
    .select-estado {
      border: 1.5px solid rgba(192,112,58,.25); border-radius: 8px;
      padding: 5px 10px; font-size: 12.5px; color: var(--text);
      background: var(--cream); outline: none;
      transition: border-color .2s;
    }
    .select-estado:focus { border-color: var(--caramel); }
 
    /* BOTONES ACCIÓN */
    .btn-guardar {
      background: var(--caramel); color: #fff; border: none;
      border-radius: 8px; padding: 5px 14px; font-size: 12.5px;
      cursor: pointer; transition: background .2s;
    }
    .btn-guardar:hover { background: var(--mocha); }
    .btn-ver {
      background: var(--warm); color: var(--mocha); border: none;
      border-radius: 8px; padding: 5px 12px; font-size: 12.5px;
      text-decoration: none; transition: background .2s;
    }
    .btn-ver:hover { background: var(--gold); }
    .btn-eliminar {
      background: #fdecea; color: #c0392b; border: none;
      border-radius: 8px; padding: 5px 10px; font-size: 13px;
      cursor: pointer; transition: background .2s;
    }
    .btn-eliminar:hover { background: #c0392b; color: #fff; }
 
    /* VACÍO */
    .empty-row td {
      text-align: center; padding: 3rem;
      color: var(--muted); font-size: 14px;
    }
 
    /* PRECIO */
    .precio-cell {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.1rem; color: var(--caramel); font-weight: 600;
    }
  </style>
</head>
<body>
 
<!-- NAVBAR -->
<nav class="navbar-ad">
  <a href="index.php" class="navbar-brand-ad">
    <div class="navbar-logo">🍰</div>
    <span class="navbar-title">Atrato Dulce</span>
  </a>
  <div class="navbar-links">
    <a href="productos.php" class="nav-link-ad"><i class="bi bi-box-seam me-1"></i>Productos</a>
    <a href="index.php" class="btn-volver"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
  </div>
</nav>
 
<div class="dash-container">
 
  <div class="page-header">
    <h2>Gestión de Pedidos</h2>
    <p><?= count($pedidos) ?> pedido<?= count($pedidos) !== 1 ? 's' : '' ?> encontrado<?= count($pedidos) !== 1 ? 's' : '' ?></p>
  </div>
 
  <!-- FILTROS -->
  <div class="filtros">
    <a href="?estado=todos" class="filtro-btn <?= $filtro_estado==='todos' ? 'activo' : '' ?>">
      Todos <span class="badge-count"><?= $todos ?></span>
    </a>
    <a href="?estado=Pendiente" class="filtro-btn <?= $filtro_estado==='Pendiente' ? 'activo' : '' ?>">
      <i class="bi bi-clock"></i> Pendientes <span class="badge-count"><?= $pendientes ?></span>
    </a>
    <a href="?estado=En+preparación" class="filtro-btn <?= $filtro_estado==='En preparación' ? 'activo' : '' ?>">
      <i class="bi bi-fire"></i> En preparación <span class="badge-count"><?= $preparacion ?></span>
    </a>
    <a href="?estado=Listo" class="filtro-btn <?= $filtro_estado==='Listo' ? 'activo' : '' ?>">
      <i class="bi bi-check-circle"></i> Listos <span class="badge-count"><?= $listos ?></span>
    </a>
    <a href="?estado=Entregado" class="filtro-btn <?= $filtro_estado==='Entregado' ? 'activo' : '' ?>">
      <i class="bi bi-bag-check"></i> Entregados <span class="badge-count"><?= $entregados ?></span>
    </a>
  </div>
 
  <!-- TABLA -->
  <div class="table-card">
    <div class="table-responsive">
      <table class="table-admin">
        <thead>
          <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Estado actual</th>
            <th>Cambiar estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pedidos)): ?>
          <tr class="empty-row">
            <td colspan="7">
              <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
              No hay pedidos en esta categoría.
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($pedidos as $p):
            $estado = $p['estado'] ?? 'Pendiente';
            $bclass = match(strtolower($estado)) {
              'pendiente'       => 'bs-pendiente',
              'en preparación'  => 'bs-preparacion',
              'listo'           => 'bs-listo',
              'entregado'       => 'bs-entregado',
              'cancelado'       => 'bs-cancelado',
              default           => 'bs-pendiente'
            };
            $cliente = $p['nombre_cliente'] ?? $p['telefono'] ?? 'Sin nombre';
            $tel     = $p['telefono'] ?? '';
          ?>
          <tr>
            <td>
              <span class="pedido-id"><?= substr((string)$p['_id'], -8) ?></span>
            </td>
            <td>
              <div class="cliente-info"><?= htmlspecialchars($cliente) ?></div>
              <?php if ($tel): ?>
              <div class="cliente-tel"><i class="bi bi-telephone"></i> <?= htmlspecialchars($tel) ?></div>
              <?php endif; ?>
            </td>
            <td>
              <?php
              if (isset($p['fecha'])) {
                try { echo $p['fecha']->toDateTime()->format('d/m/Y H:i'); }
                catch (Exception $e) { echo '—'; }
              } else { echo '—'; }
              ?>
            </td>
            <td>
              <span class="precio-cell">$<?= number_format($p['total'] ?? 0, 0, ',', '.') ?></span>
            </td>
            <td>
              <span class="badge-estado <?= $bclass ?>"><?= htmlspecialchars($estado) ?></span>
            </td>
            <td>
              <form method="POST" class="d-flex gap-2 align-items-center">
                <input type="hidden" name="pedido_id" value="<?= (string)$p['_id'] ?>">
                <select name="estado" class="select-estado">
                  <option value="Pendiente"      <?= $estado==='Pendiente'      ?'selected':'' ?>>Pendiente</option>
                  <option value="En preparación" <?= $estado==='En preparación' ?'selected':'' ?>>En preparación</option>
                  <option value="Listo"          <?= $estado==='Listo'          ?'selected':'' ?>>Listo</option>
                  <option value="Entregado"      <?= $estado==='Entregado'      ?'selected':'' ?>>Entregado</option>
                  <option value="Cancelado"      <?= $estado==='Cancelado'      ?'selected':'' ?>>Cancelado</option>
                </select>
                <button type="submit" class="btn-guardar">
                  <i class="bi bi-check2"></i>
                </button>
              </form>
            </td>
            <td>
              <div class="d-flex gap-2">
                <a href="detalle_pedido.php?id=<?= (string)$p['_id'] ?>" class="btn-ver">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="?eliminar=<?= (string)$p['_id'] ?>"
                   class="btn-eliminar"
                   onclick="return confirm('¿Eliminar este pedido?')">
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