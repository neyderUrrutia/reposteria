<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit;
}

require_once __DIR__ . '/../includes/db.php';

// =============================================
// 📊 ESTADÍSTICAS - MongoDB
// =============================================
$total_productos  = $db->productos->countDocuments();
$productos_activos = $db->productos->countDocuments(['disponible' => 1]);
$total_pedidos    = $db->pedidos->countDocuments();
$pendientes       = $db->pedidos->countDocuments(['estado' => 'pendiente']);
$entregados       = $db->pedidos->countDocuments(['estado' => 'entregado']);
$en_proceso       = $db->pedidos->countDocuments(['estado' => 'en proceso']);

// =============================================
// 💰 INGRESOS DEL MES
// =============================================
$inicio_mes = new MongoDB\BSON\UTCDateTime(strtotime(date('Y-m-01')) * 1000);
$fin_mes    = new MongoDB\BSON\UTCDateTime(strtotime(date('Y-m-t 23:59:59')) * 1000);

$pipeline_ingresos = [
  ['$match' => ['estado' => 'entregado', 'created_at' => ['$gte' => $inicio_mes, '$lte' => $fin_mes]]],
  ['$group' => ['_id' => null, 'total' => ['$sum' => '$total']]]
];
$res_ingresos = $db->pedidos->aggregate($pipeline_ingresos)->toArray();
$ingresos_mes = !empty($res_ingresos) ? $res_ingresos[0]['total'] : 0;

// =============================================
// 📅 VENTAS POR DÍA (últimos 7 días)
// =============================================
$hace7 = new MongoDB\BSON\UTCDateTime(strtotime('-6 days midnight') * 1000);
$pipeline_dias = [
  ['$match' => ['created_at' => ['$gte' => $hace7]]],
  ['$group' => [
    '_id'      => ['$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$created_at']],
    'pedidos'  => ['$sum' => 1],
    'ingresos' => ['$sum' => '$total']
  ]],
  ['$sort' => ['_id' => 1]]
];
$ventas_dias_raw = $db->pedidos->aggregate($pipeline_dias)->toArray();

$ventas_labels = $ventas_pedidos = $ventas_ingresos = [];
$dias_map = [];
foreach ($ventas_dias_raw as $d) $dias_map[(string)$d['_id']] = $d;
for ($i = 6; $i >= 0; $i--) {
  $fecha = date('Y-m-d', strtotime("-$i days"));
  $label = date('D', strtotime($fecha));
  $labels_es = ['Mon'=>'Lun','Tue'=>'Mar','Wed'=>'Mié','Thu'=>'Jue','Fri'=>'Vie','Sat'=>'Sáb','Sun'=>'Dom'];
  $ventas_labels[]   = $labels_es[$label] ?? $label;
  $ventas_pedidos[]  = isset($dias_map[$fecha]) ? (int)$dias_map[$fecha]['pedidos']   : 0;
  $ventas_ingresos[] = isset($dias_map[$fecha]) ? (float)$dias_map[$fecha]['ingresos'] : 0;
}

// =============================================
// 🎂 PEDIDOS POR CATEGORÍA
// =============================================
$pipeline_cat = [
  ['$lookup'  => ['from' => 'productos', 'localField' => 'producto_id', 'foreignField' => '_id', 'as' => 'producto']],
  ['$unwind'  => '$producto'],
  ['$group'   => ['_id' => '$producto.categoria', 'total' => ['$sum' => 1]]],
  ['$sort'    => ['total' => -1]],
  ['$limit'   => 5]
];
$categorias_raw = $db->pedidos->aggregate($pipeline_cat)->toArray();
$cat_labels = $cat_data = [];
foreach ($categorias_raw as $c) { $cat_labels[] = (string)$c['_id']; $cat_data[] = (int)$c['total']; }
if (empty($cat_labels)) { $cat_labels = ['Tortas','Postres','Panes','Bebidas']; $cat_data = [0,0,0,0]; }

// =============================================
// 🏆 TOP 5 PRODUCTOS
// =============================================
$pipeline_top = [
  ['$group'  => ['_id' => '$producto_id', 'total' => ['$sum' => 1]]],
  ['$sort'   => ['total' => -1]],
  ['$limit'  => 5],
  ['$lookup' => ['from' => 'productos', 'localField' => '_id', 'foreignField' => '_id', 'as' => 'producto']],
  ['$unwind' => '$producto']
];
$top_productos = $db->pedidos->aggregate($pipeline_top)->toArray();
$max_top = !empty($top_productos) ? (int)$top_productos[0]['total'] : 1;

// =============================================
// 🕐 PEDIDOS RECIENTES
// =============================================
$pedidos_recientes = $db->pedidos->find([], ['sort' => ['created_at' => -1], 'limit' => 6])->toArray();

$js_labels   = json_encode($ventas_labels,  JSON_UNESCAPED_UNICODE);
$js_pedidos  = json_encode($ventas_pedidos);
$js_ingresos = json_encode($ventas_ingresos);
$js_cat_lbl  = json_encode($cat_labels,     JSON_UNESCAPED_UNICODE);
$js_cat_data = json_encode($cat_data);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard | Atrato Dulce</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../public/css/styles.css">

  <style>
    :root {
      --cream:    #fdf6ee;
      --warm:     #f5e6d0;
      --mocha:    #3b2314;
      --caramel:  #c0703a;
      --gold:     #c9a84c;
      --rose:     #d4737a;

      /* semánticos para métricas */
      --mocha-light:   #f3ebe3;
      --caramel-light: #faeada;
      --gold-light:    #faf3e0;
      --rose-light:    #fdeeed;
      --green:         #4a7c59;
      --green-light:   #e8f4ed;

      --texto:       #1e0e05;
      --texto-muted: #8a6f5e;
    }

    * { box-sizing: border-box; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--cream);
      color: var(--texto);
      min-height: 100vh;
    }

    /* ─── NAVBAR ─── */
    .navbar-ad {
      background: var(--mocha);
      padding: 0.85rem 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 100;
      border-bottom: 3px solid var(--caramel);
    }
    .navbar-brand-ad {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
    }
    .navbar-logo {
      width: 38px; height: 38px;
      background: var(--caramel);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
    }
    .navbar-title {
      font-family: 'Cormorant Garamond', serif;
      color: var(--warm);
      font-size: 1.2rem;
      font-weight: 700;
      letter-spacing: 0.02em;
    }
    .navbar-links { display: flex; align-items: center; gap: 8px; }
    .nav-link-ad {
      color: #e8d5c4;
      text-decoration: none;
      font-size: 13px;
      font-weight: 500;
      padding: 6px 14px;
      border-radius: 6px;
      border: 1px solid rgba(232,213,196,0.3);
      transition: all 0.2s;
    }
    .nav-link-ad:hover { background: rgba(232,213,196,0.15); color: #fff; }
    .nav-user { color: #e8d5c4; font-size: 13px; margin-right: 4px; }
    .btn-logout-ad {
      background: var(--caramel);
      color: #fff;
      border: none;
      padding: 6px 16px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 500;
      text-decoration: none;
      transition: opacity 0.2s;
    }
    .btn-logout-ad:hover { background: #a05a2a; color: #fff; }

    /* ─── LAYOUT ─── */
    .dash-container { max-width: 1280px; margin: 0 auto; padding: 2rem 1.5rem; }

    .dash-header { margin-bottom: 2rem; }
    .dash-header h2 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--mocha);
      margin-bottom: 2px;
    }
    .dash-header p { font-size: 13px; color: var(--texto-muted); margin: 0; }

    .section-label {
      font-size: 11px;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--caramel);
      margin-bottom: 0.75rem;
    }

    /* ─── METRIC CARDS ─── */
    .metrics-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 14px;
      margin-bottom: 1.75rem;
    }
    .metric-card {
      background: #fff;
      border-radius: 14px;
      padding: 1.25rem;
      border: 1px solid rgba(192,112,58,0.15);
      position: relative;
      overflow: hidden;
    }
    .metric-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
    }
    .mc-mocha::before   { background: var(--mocha); }
    .mc-caramel::before { background: var(--caramel); }
    .mc-gold::before    { background: var(--gold); }
    .mc-rose::before    { background: var(--rose); }

    .metric-icon {
      width: 40px; height: 40px;
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
      margin-bottom: 12px;
    }
    .ic-mocha   { background: var(--mocha-light);   color: var(--mocha); }
    .ic-caramel { background: var(--caramel-light);  color: var(--caramel); }
    .ic-gold    { background: var(--gold-light);     color: #8a6510; }
    .ic-rose    { background: var(--rose-light);     color: #b04a52; }

    .metric-label { font-size: 12px; color: var(--texto-muted); margin-bottom: 4px; font-weight: 500; }
    .metric-value { font-size: 26px; font-weight: 700; color: var(--texto); line-height: 1; margin-bottom: 6px; }
    .metric-sub   { font-size: 11px; color: var(--texto-muted); }
    .metric-sub.up   { color: var(--green); }
    .metric-sub.down { color: var(--rose); }

    /* ─── CHARTS ROW ─── */
    .charts-row {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 14px;
      margin-bottom: 1.75rem;
    }
    .card-ad {
      background: #fff;
      border-radius: 14px;
      padding: 1.25rem 1.5rem;
      border: 1px solid rgba(192,112,58,0.15);
    }
    .card-title-ad {
      font-size: 14px;
      font-weight: 500;
      color: var(--mocha);
      margin-bottom: 0.75rem;
      font-family: 'Cormorant Garamond', serif;
      font-size: 1rem;
    }
    .legend-row {
      display: flex; flex-wrap: wrap; gap: 12px;
      margin-bottom: 10px; font-size: 11px; color: var(--texto-muted);
    }
    .legend-row span { display: flex; align-items: center; gap: 5px; }
    .ldot { width: 10px; height: 10px; border-radius: 3px; }

    /* ─── BOTTOM ROW ─── */
    .bottom-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

    /* ─── TOP PRODUCTOS ─── */
    .prod-item {
      display: flex; align-items: center; gap: 10px;
      padding: 9px 0;
      border-bottom: 1px solid #f1e4d8;
      font-size: 13px;
    }
    .prod-item:last-child { border: none; }
    .prod-name { flex: 0 0 160px; color: var(--texto); font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .prod-bar-wrap { flex: 1; height: 7px; background: var(--warm); border-radius: 4px; }
    .prod-bar  { height: 7px; border-radius: 4px; }
    .prod-qty  { flex: 0 0 30px; text-align: right; color: var(--texto-muted); font-size: 12px; }

    /* ─── PEDIDOS TABLE ─── */
    .orders-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    .orders-table th {
      text-align: left; color: var(--texto-muted);
      font-weight: 500; font-size: 11px;
      text-transform: uppercase; letter-spacing: 0.05em;
      padding: 0 0 10px; border-bottom: 1px solid #f1e4d8;
    }
    .orders-table td {
      padding: 9px 0; color: var(--texto);
      border-bottom: 1px solid #f1e4d8; vertical-align: middle;
    }
    .orders-table tr:last-child td { border: none; }

    .badge-estado {
      display: inline-block; font-size: 10px; font-weight: 500;
      padding: 3px 10px; border-radius: 20px;
    }
    .bs-pendiente { background: var(--gold-light);    color: #8a6510; }
    .bs-entregado { background: var(--green-light);   color: var(--green); }
    .bs-proceso   { background: var(--caramel-light); color: var(--caramel); }
    .bs-cancelado { background: var(--rose-light);    color: #b04a52; }

    /* ─── ACCIONES RÁPIDAS ─── */
    .quick-actions { display: flex; gap: 10px; margin-top: 1.5rem; flex-wrap: wrap; }
    .btn-action {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 10px 20px; border-radius: 10px;
      font-size: 13px; font-weight: 500;
      text-decoration: none; transition: all 0.2s;
      border: none; cursor: pointer;
    }
    .btn-primary-ad   { background: var(--mocha);   color: #fff; }
    .btn-primary-ad:hover { background: #5a3520; color: #fff; }
    .btn-gold-ad      { background: var(--caramel); color: #fff; }
    .btn-gold-ad:hover { background: var(--mocha); color: #fff; }
    .btn-outline-ad   { background: #fff; color: var(--mocha); border: 1.5px solid #d4b896; }
    .btn-outline-ad:hover { background: var(--warm); }

    @media (max-width: 992px) {
      .metrics-grid { grid-template-columns: repeat(2, 1fr); }
      .charts-row   { grid-template-columns: 1fr; }
      .bottom-row   { grid-template-columns: 1fr; }
    }
    @media (max-width: 576px) {
      .metrics-grid { grid-template-columns: 1fr; }
      .navbar-links .nav-link-ad { display: none; }
    }
  </style>
</head>
<body>

<!-- ═══════════════════ NAVBAR ═══════════════════ -->
<nav class="navbar-ad">
  <a href="index.php" class="navbar-brand-ad">
    <div class="navbar-logo">🍰</div>
    <span class="navbar-title">Atrato Dulce</span>
  </a>
  <div class="navbar-links">
    <a href="productos.php"  class="nav-link-ad"><i class="bi bi-box-seam me-1"></i>Productos</a>
    <a href="categorias.php" class="nav-link-ad"><i class="bi bi-tags me-1"></i>Categorías</a>
    <a href="pedidos.php"    class="nav-link-ad"><i class="bi bi-cart3 me-1"></i>Pedidos</a>
    <span class="nav-user"><i class="bi bi-person-circle me-1"></i>Administrador</span>
    <a href="logout.php" class="btn-logout-ad">Cerrar sesión</a>
  </div>
</nav>

<!-- ═══════════════════ CONTENIDO ═══════════════════ -->
<div class="dash-container">

  <!-- Encabezado -->
  <div class="dash-header">
    <h2>Panel de Administración</h2>
    <p><?= date('l, d \d\e F \d\e Y') ?> &mdash; Bienvenido de nuevo, Administrador</p>
  </div>

  <!-- ─── MÉTRICAS ─── -->
  <div class="section-label">Resumen general</div>
  <div class="metrics-grid">

    <div class="metric-card mc-mocha">
      <div class="metric-icon ic-mocha"><i class="bi bi-currency-dollar"></i></div>
      <div class="metric-label">Ingresos del mes</div>
      <div class="metric-value">$<?= number_format($ingresos_mes, 0, ',', '.') ?></div>
      <div class="metric-sub">Pedidos entregados este mes</div>
    </div>

    <div class="metric-card mc-caramel">
      <div class="metric-icon ic-caramel"><i class="bi bi-cart3"></i></div>
      <div class="metric-label">Pedidos totales</div>
      <div class="metric-value"><?= $total_pedidos ?></div>
      <div class="metric-sub"><?= $en_proceso ?> en proceso ahora</div>
    </div>

    <div class="metric-card mc-gold">
      <div class="metric-icon ic-gold"><i class="bi bi-box-seam"></i></div>
      <div class="metric-label">Total productos</div>
      <div class="metric-value"><?= $total_productos ?></div>
      <div class="metric-sub up"><i class="bi bi-check-circle"></i> <?= $productos_activos ?> disponibles</div>
    </div>

    <div class="metric-card mc-rose">
      <div class="metric-icon ic-rose"><i class="bi bi-hourglass-split"></i></div>
      <div class="metric-label">Pendientes</div>
      <div class="metric-value"><?= $pendientes ?></div>
      <div class="metric-sub down">Requieren atención</div>
    </div>

  </div>

  <!-- ─── GRÁFICAS ─── -->
  <div class="section-label">Estadísticas</div>
  <div class="charts-row">

    <div class="card-ad">
      <div class="card-title-ad">Ventas últimos 7 días</div>
      <div class="legend-row">
        <span><span class="ldot" style="background:var(--mocha);"></span>Ingresos ($)</span>
        <span><span class="ldot" style="background:var(--caramel);"></span>Pedidos</span>
      </div>
      <div style="position:relative;width:100%;height:220px;">
        <canvas id="chartVentas" role="img" aria-label="Gráfica de ventas por día">Sin datos disponibles.</canvas>
      </div>
    </div>

    <div class="card-ad">
      <div class="card-title-ad">Pedidos por categoría</div>
      <div class="legend-row" id="legendCat"></div>
      <div style="position:relative;width:100%;height:220px;">
        <canvas id="chartCat" role="img" aria-label="Gráfica de dona por categoría">Sin datos disponibles.</canvas>
      </div>
    </div>

  </div>

  <!-- ─── FILA INFERIOR ─── -->
  <div class="bottom-row">

    <div class="card-ad">
      <div class="card-title-ad">Productos más vendidos</div>
      <?php
      $bar_colors = ['#3b2314','#c0703a','#c9a84c','#d4737a','#4a7c59'];
      if (!empty($top_productos)):
        foreach ($top_productos as $i => $prod):
          $pct    = $max_top > 0 ? round(($prod['total'] / $max_top) * 100) : 0;
          $nombre = $prod['producto']['nombre'] ?? 'Sin nombre';
          $color  = $bar_colors[$i % count($bar_colors)];
      ?>
      <div class="prod-item">
        <div class="prod-name" title="<?= htmlspecialchars($nombre) ?>"><?= htmlspecialchars($nombre) ?></div>
        <div class="prod-bar-wrap">
          <div class="prod-bar" style="width:<?= $pct ?>%;background:<?= $color ?>;"></div>
        </div>
        <div class="prod-qty"><?= $prod['total'] ?></div>
      </div>
      <?php endforeach; else: ?>
      <p style="color:var(--texto-muted);font-size:13px;margin-top:1rem;">Sin datos de ventas aún.</p>
      <?php endif; ?>
    </div>

    <div class="card-ad">
      <div class="card-title-ad">Pedidos recientes</div>
      <?php if (!empty($pedidos_recientes)): ?>
      <table class="orders-table">
        <thead>
          <tr><th>Cliente</th><th>Total</th><th>Estado</th></tr>
        </thead>
        <tbody>
          <?php foreach ($pedidos_recientes as $pedido):
            $estado = strtolower($pedido['estado'] ?? 'pendiente');
            $clase  = match($estado) {
              'entregado'  => 'bs-entregado',
              'en proceso' => 'bs-proceso',
              'cancelado'  => 'bs-cancelado',
              default      => 'bs-pendiente'
            };
            $cliente = $pedido['nombre_cliente'] ?? ($pedido['cliente'] ?? 'Cliente');
            $total_p = number_format($pedido['total'] ?? 0, 0, ',', '.');
          ?>
          <tr>
            <td><?= htmlspecialchars($cliente) ?></td>
            <td>$<?= $total_p ?></td>
            <td><span class="badge-estado <?= $clase ?>"><?= ucfirst($estado) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <p style="color:var(--texto-muted);font-size:13px;margin-top:1rem;">No hay pedidos recientes.</p>
      <?php endif; ?>
    </div>

  </div>

  <!-- ─── ACCIONES RÁPIDAS ─── -->
  <div class="quick-actions">
    <a href="productos.php"  class="btn-action btn-primary-ad"><i class="bi bi-plus-circle"></i> Nuevo producto</a>
    <a href="pedidos.php"    class="btn-action btn-gold-ad"><i class="bi bi-list-check"></i> Ver todos los pedidos</a>
    <a href="categorias.php" class="btn-action btn-outline-ad"><i class="bi bi-tags"></i> Gestionar categorías</a>
  </div>

</div>

<!-- ═══════════════════ SCRIPTS ═══════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  const labels   = <?= $js_labels ?>;
  const pedidos  = <?= $js_pedidos ?>;
  const ingresos = <?= $js_ingresos ?>;
  const catLbls  = <?= $js_cat_lbl ?>;
  const catData  = <?= $js_cat_data ?>;

  const CAT_COLORS = ['#3b2314','#c0703a','#c9a84c','#d4737a','#4a7c59'];

  // ── Gráfica de barras ──
  new Chart(document.getElementById('chartVentas'), {
    type: 'bar',
    data: {
      labels,
      datasets: [
        { label: 'Ingresos ($)', data: ingresos, backgroundColor: '#3b2314', borderRadius: 6, yAxisID: 'y' },
        { label: 'Pedidos',      data: pedidos,  backgroundColor: '#c0703a', borderRadius: 6, yAxisID: 'y1' }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x:  { grid: { display: false }, ticks: { font: { size: 11 } } },
        y:  { position: 'left',  grid: { color: '#f1e4d8' }, ticks: { font: { size: 11 } } },
        y1: { position: 'right', grid: { display: false },   ticks: { font: { size: 11 } } }
      }
    }
  });

  // ── Leyenda categorías ──
  const legendCat = document.getElementById('legendCat');
  catLbls.forEach((lbl, i) => {
    const span = document.createElement('span');
    span.innerHTML = `<span style="width:10px;height:10px;border-radius:3px;background:${CAT_COLORS[i%CAT_COLORS.length]};display:inline-block;"></span> ${lbl}`;
    span.style.cssText = 'display:flex;align-items:center;gap:5px;font-size:11px;color:#8a6f5e;';
    legendCat.appendChild(span);
  });

  // ── Gráfica de dona ──
  new Chart(document.getElementById('chartCat'), {
    type: 'doughnut',
    data: {
      labels: catLbls,
      datasets: [{ data: catData, backgroundColor: CAT_COLORS, borderWidth: 0, hoverOffset: 8 }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '65%',
      plugins: { legend: { display: false } }
    }
  });
</script>

</body>
</html>