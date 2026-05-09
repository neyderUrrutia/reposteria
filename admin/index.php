<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit;
}

require_once __DIR__ . '/../includes/db.php';

$total_productos   = $db->productos->countDocuments();
$productos_activos = $db->productos->countDocuments(['disponible' => true]);
$total_pedidos     = $db->pedidos->countDocuments();
$pendientes        = $db->pedidos->countDocuments(['estado' => 'Pendiente']);
$entregados        = $db->pedidos->countDocuments(['estado' => 'Entregado']);
$en_proceso        = $db->pedidos->countDocuments(['estado' => 'En proceso']);

$inicio_mes = new MongoDB\BSON\UTCDateTime(strtotime(date('Y-m-01')) * 1000);
$fin_mes    = new MongoDB\BSON\UTCDateTime(strtotime(date('Y-m-t 23:59:59')) * 1000);

$res_ingresos = $db->pedidos->aggregate([
    ['$match' => ['fecha' => ['$gte' => $inicio_mes, '$lte' => $fin_mes]]],
    ['$group' => ['_id' => null, 'total' => ['$sum' => '$total']]]
])->toArray();
$ingresos_mes = !empty($res_ingresos) ? $res_ingresos[0]['total'] : 0;

$hace7 = new MongoDB\BSON\UTCDateTime(strtotime('-6 days midnight') * 1000);
$ventas_dias_raw = $db->pedidos->aggregate([
    ['$match' => ['fecha' => ['$gte' => $hace7]]],
    ['$group' => [
        '_id'      => ['$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$fecha']],
        'pedidos'  => ['$sum' => 1],
        'ingresos' => ['$sum' => '$total']
    ]],
    ['$sort' => ['_id' => 1]]
])->toArray();

$dias_map = [];
foreach ($ventas_dias_raw as $d) { $dias_map[(string)$d['_id']] = $d; }
$ventas_labels = $ventas_pedidos = $ventas_ingresos = [];
$labels_es = ['Mon'=>'Lun','Tue'=>'Mar','Wed'=>'Mié','Thu'=>'Jue','Fri'=>'Vie','Sat'=>'Sáb','Sun'=>'Dom'];
for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $ventas_labels[]   = $labels_es[date('D', strtotime($fecha))] ?? date('D', strtotime($fecha));
    $ventas_pedidos[]  = isset($dias_map[$fecha]) ? (int)$dias_map[$fecha]['pedidos'] : 0;
    $ventas_ingresos[] = isset($dias_map[$fecha]) ? round((float)$dias_map[$fecha]['ingresos'], 0) : 0;
}

$top_productos = $db->pedidos->aggregate([
    ['$unwind' => '$productos'],
    ['$group'  => ['_id' => '$productos.nombre', 'total' => ['$sum' => ['$ifNull' => ['$productos.cantidad', 1]]]]],
    ['$sort'   => ['total' => -1]],
    ['$limit'  => 5]
])->toArray();
$max_top = !empty($top_productos) ? (int)$top_productos[0]['total'] : 1;

$pedidos_recientes = $db->pedidos->find([], ['sort'=>['fecha'=>-1],'limit'=>6])->toArray();

$js_labels   = json_encode($ventas_labels,  JSON_UNESCAPED_UNICODE);
$js_pedidos  = json_encode($ventas_pedidos);
$js_ingresos = json_encode($ventas_ingresos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel Admin | Atrato Dulce</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    :root {
      --cream:    #fdf6ee;
      --warm:     #f5e6d0;
      --mocha:    #3b2314;
      --mocha2:   #5c3520;
      --caramel:  #c0703a;
      --caramel2: #e8915a;
      --gold:     #c9a84c;
      --rose:     #d4737a;
      --green:    #4a7c59;
      --texto:    #1e0e05;
      --muted:    #9a7b6a;
      --border:   rgba(192,112,58,0.18);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--cream);
      color: var(--texto);
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* ══════════════════════════════
       SIDEBAR
    ══════════════════════════════ */
    .sidebar {
      position: fixed;
      top: 0; left: 0;
      width: 240px;
      height: 100vh;
      background: var(--mocha);
      display: flex;
      flex-direction: column;
      z-index: 200;
      overflow: hidden;
    }

    /* Textura sutil en el sidebar */
    .sidebar::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image: radial-gradient(circle at 20% 20%, rgba(192,112,58,0.12) 0%, transparent 60%),
                        radial-gradient(circle at 80% 80%, rgba(201,168,76,0.08) 0%, transparent 50%);
      pointer-events: none;
    }

    .sidebar-logo {
      padding: 2rem 1.5rem 1.5rem;
      border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    .sidebar-logo-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--warm);
      letter-spacing: 0.01em;
      line-height: 1.1;
    }

    .sidebar-logo-sub {
      font-size: 10px;
      color: rgba(245,230,208,0.5);
      letter-spacing: 0.15em;
      text-transform: uppercase;
      margin-top: 3px;
    }

    .sidebar-logo-line {
      width: 30px;
      height: 2px;
      background: var(--caramel);
      border-radius: 2px;
      margin-top: 10px;
    }

    .sidebar-nav {
      flex: 1;
      padding: 1.5rem 0;
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .sidebar-label {
      font-size: 9px;
      font-weight: 500;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: rgba(245,230,208,0.35);
      padding: 0.75rem 1.5rem 0.3rem;
    }

    .sidebar-link {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 0.7rem 1.5rem;
      color: rgba(245,230,208,0.7);
      text-decoration: none;
      font-size: 13.5px;
      font-weight: 400;
      transition: all 0.2s;
      position: relative;
      margin: 0 0.75rem;
      border-radius: 10px;
    }

    .sidebar-link:hover {
      background: rgba(255,255,255,0.06);
      color: var(--warm);
    }

    .sidebar-link.active {
      background: rgba(192,112,58,0.2);
      color: var(--caramel2);
    }

    .sidebar-link.active::before {
      content: '';
      position: absolute;
      left: 0; top: 50%;
      transform: translateY(-50%);
      width: 3px; height: 20px;
      background: var(--caramel);
      border-radius: 0 3px 3px 0;
    }

    .sidebar-link i { font-size: 15px; width: 18px; text-align: center; }

    .sidebar-footer {
      padding: 1.25rem;
      border-top: 1px solid rgba(255,255,255,0.08);
    }

    .sidebar-user {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 12px;
    }

    .sidebar-avatar {
      width: 36px; height: 36px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--caramel), var(--gold));
      display: flex; align-items: center; justify-content: center;
      font-size: 14px;
      color: white;
      font-weight: 600;
      flex-shrink: 0;
    }

    .sidebar-user-name {
      font-size: 13px;
      font-weight: 500;
      color: var(--warm);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .sidebar-user-role {
      font-size: 10px;
      color: rgba(245,230,208,0.45);
      letter-spacing: 0.05em;
    }

    .btn-logout {
      display: flex;
      align-items: center;
      gap: 8px;
      width: 100%;
      padding: 8px 14px;
      background: rgba(192,112,58,0.15);
      border: 1px solid rgba(192,112,58,0.3);
      border-radius: 8px;
      color: var(--caramel2);
      font-size: 12.5px;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.2s;
      cursor: pointer;
    }

    .btn-logout:hover {
      background: rgba(192,112,58,0.28);
      color: #fff;
    }

    /* ══════════════════════════════
       MAIN CONTENT
    ══════════════════════════════ */
    .main {
      margin-left: 240px;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* Topbar */
    .topbar {
      background: rgba(253,246,238,0.92);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border);
      padding: 1rem 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .topbar-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.4rem;
      font-weight: 600;
      color: var(--mocha);
      letter-spacing: -0.01em;
    }

    .topbar-date {
      font-size: 12px;
      color: var(--muted);
      margin-top: 1px;
    }

    .topbar-right {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .topbar-badge {
      display: flex;
      align-items: center;
      gap: 6px;
      background: white;
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 5px 14px;
      font-size: 12px;
      color: var(--muted);
    }

    .topbar-badge .dot {
      width: 7px; height: 7px;
      background: var(--green);
      border-radius: 50%;
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.6; transform: scale(0.85); }
    }

    /* ══════════════════════════════
       PÁGINA
    ══════════════════════════════ */
    .page { padding: 2rem; }

    /* ── MÉTRICAS ── */
    .metrics-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 2rem;
    }

    .metric-card {
      background: white;
      border-radius: 16px;
      padding: 1.4rem;
      border: 1px solid var(--border);
      position: relative;
      overflow: hidden;
      transition: transform 0.25s, box-shadow 0.25s;
      animation: fadeUp 0.5s ease both;
    }

    .metric-card:nth-child(1) { animation-delay: 0.05s; }
    .metric-card:nth-child(2) { animation-delay: 0.1s; }
    .metric-card:nth-child(3) { animation-delay: 0.15s; }
    .metric-card:nth-child(4) { animation-delay: 0.2s; }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(18px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .metric-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 30px rgba(59,35,20,0.1);
    }

    /* Decoración esquina */
    .metric-card::after {
      content: '';
      position: absolute;
      bottom: -20px; right: -20px;
      width: 80px; height: 80px;
      border-radius: 50%;
      opacity: 0.06;
    }

    .mc-1::after { background: var(--mocha); }
    .mc-2::after { background: var(--caramel); }
    .mc-3::after { background: var(--gold); }
    .mc-4::after { background: var(--rose); }

    /* Línea superior coloreada */
    .metric-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      border-radius: 16px 16px 0 0;
    }
    .mc-1::before { background: linear-gradient(90deg, var(--mocha), var(--mocha2)); }
    .mc-2::before { background: linear-gradient(90deg, var(--caramel), var(--caramel2)); }
    .mc-3::before { background: linear-gradient(90deg, var(--gold), #e8c96a); }
    .mc-4::before { background: linear-gradient(90deg, var(--rose), #e89198); }

    .metric-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      margin-bottom: 1rem;
    }

    .metric-icon-wrap {
      width: 42px; height: 42px;
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 17px;
    }

    .mi-1 { background: #f0e8e0; color: var(--mocha); }
    .mi-2 { background: #faeada; color: var(--caramel); }
    .mi-3 { background: #faf3e0; color: #8a6510; }
    .mi-4 { background: #fdeeed; color: #b04a52; }

    .metric-trend {
      font-size: 10px;
      font-weight: 500;
      padding: 3px 8px;
      border-radius: 20px;
    }

    .trend-up   { background: #e8f4ed; color: var(--green); }
    .trend-warn { background: #fdeeed; color: #b04a52; }
    .trend-neu  { background: #f0e8e0; color: var(--muted); }

    .metric-value {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2.2rem;
      font-weight: 700;
      color: var(--texto);
      line-height: 1;
      margin-bottom: 4px;
    }

    .metric-label {
      font-size: 12px;
      color: var(--muted);
      font-weight: 400;
    }

    .metric-sub {
      font-size: 11px;
      color: var(--muted);
      margin-top: 8px;
      padding-top: 8px;
      border-top: 1px solid #f5ede3;
    }

    /* ── SECCIÓN ── */
    .section-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1rem;
    }

    .section-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.15rem;
      font-weight: 600;
      color: var(--mocha);
    }

    .section-tag {
      font-size: 10px;
      color: var(--muted);
      letter-spacing: 0.1em;
      text-transform: uppercase;
    }

    /* ── GRÁFICA ── */
    .chart-card {
      background: white;
      border-radius: 16px;
      padding: 1.5rem;
      border: 1px solid var(--border);
      margin-bottom: 2rem;
      animation: fadeUp 0.5s ease 0.25s both;
    }

    .chart-legend {
      display: flex;
      gap: 16px;
      margin-bottom: 1.25rem;
    }

    .legend-item {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 11.5px;
      color: var(--muted);
    }

    .legend-dot {
      width: 10px; height: 10px;
      border-radius: 3px;
      flex-shrink: 0;
    }

    /* ── FILA INFERIOR ── */
    .bottom-grid {
      display: grid;
      grid-template-columns: 1fr 1.4fr;
      gap: 16px;
      margin-bottom: 2rem;
    }

    .data-card {
      background: white;
      border-radius: 16px;
      padding: 1.5rem;
      border: 1px solid var(--border);
      animation: fadeUp 0.5s ease 0.3s both;
    }

    /* Top productos */
    .prod-row {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 0;
      border-bottom: 1px solid #f5ede3;
    }
    .prod-row:last-child { border: none; padding-bottom: 0; }

    .prod-rank {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--border);
      width: 20px;
      text-align: center;
      flex-shrink: 0;
    }

    .prod-info { flex: 1; min-width: 0; }

    .prod-name {
      font-size: 13px;
      font-weight: 500;
      color: var(--texto);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      margin-bottom: 4px;
    }

    .prod-bar-bg {
      height: 5px;
      background: var(--warm);
      border-radius: 10px;
      overflow: hidden;
    }

    .prod-bar-fill {
      height: 5px;
      border-radius: 10px;
      transition: width 1s ease;
    }

    .prod-qty {
      font-size: 12px;
      font-weight: 600;
      color: var(--caramel);
      flex-shrink: 0;
      min-width: 28px;
      text-align: right;
    }

    /* Tabla pedidos recientes */
    .pedidos-table { width: 100%; border-collapse: collapse; }

    .pedidos-table th {
      font-size: 10px;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: var(--muted);
      padding: 0 12px 10px 0;
      border-bottom: 1px solid #f5ede3;
      text-align: left;
    }

    .pedidos-table td {
      padding: 11px 12px 11px 0;
      font-size: 13px;
      color: var(--texto);
      border-bottom: 1px solid #f5ede3;
      vertical-align: middle;
    }

    .pedidos-table tr:last-child td { border: none; }

    .cliente-cell {
      display: flex;
      align-items: center;
      gap: 9px;
    }

    .cliente-avatar {
      width: 30px; height: 30px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--warm), #e8d0b0);
      display: flex; align-items: center; justify-content: center;
      font-size: 11px;
      font-weight: 600;
      color: var(--caramel);
      flex-shrink: 0;
    }

    .badge-estado {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 10.5px;
      font-weight: 500;
      padding: 3px 10px;
      border-radius: 20px;
      white-space: nowrap;
    }

    .badge-estado::before {
      content: '';
      width: 5px; height: 5px;
      border-radius: 50%;
    }

    .bs-pendiente  { background: #faf3e0; color: #8a6510; }
    .bs-pendiente::before  { background: #c9a84c; }
    .bs-entregado  { background: #e8f4ed; color: var(--green); }
    .bs-entregado::before  { background: var(--green); }
    .bs-proceso    { background: #faeada; color: var(--caramel); }
    .bs-proceso::before    { background: var(--caramel); }
    .bs-cancelado  { background: #fdeeed; color: #b04a52; }
    .bs-cancelado::before  { background: #b04a52; }

    /* ── ACCIONES RÁPIDAS ── */
    .actions-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
    }

    .action-btn {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 1.1rem 1.4rem;
      background: white;
      border: 1px solid var(--border);
      border-radius: 14px;
      text-decoration: none;
      color: var(--texto);
      transition: all 0.25s;
      animation: fadeUp 0.5s ease 0.35s both;
    }

    .action-btn:hover {
      border-color: var(--caramel);
      background: #fffaf5;
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(192,112,58,0.1);
      color: var(--texto);
    }

    .action-icon {
      width: 44px; height: 44px;
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
      flex-shrink: 0;
    }

    .ai-mocha   { background: #f0e8e0; color: var(--mocha); }
    .ai-caramel { background: #faeada; color: var(--caramel); }

    .action-text strong {
      display: block;
      font-size: 13.5px;
      font-weight: 500;
      color: var(--texto);
    }

    .action-text span {
      font-size: 11.5px;
      color: var(--muted);
    }

    .action-arrow {
      margin-left: auto;
      color: var(--muted);
      font-size: 14px;
      transition: transform 0.2s;
    }

    .action-btn:hover .action-arrow { transform: translateX(3px); color: var(--caramel); }

    /* ══════════════════════════════
       RESPONSIVE
    ══════════════════════════════ */
    @media (max-width: 1100px) {
      .bottom-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 900px) {
      .metrics-grid { grid-template-columns: repeat(2,1fr); }
    }
    @media (max-width: 768px) {
      .sidebar { transform: translateX(-100%); }
      .main { margin-left: 0; }
      .metrics-grid { grid-template-columns: 1fr 1fr; }
      .actions-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 480px) {
      .metrics-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<!-- ══════════ SIDEBAR ══════════ -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="sidebar-logo-title">Atrato Dulce</div>
    <div class="sidebar-logo-sub">Panel de administración</div>
    <div class="sidebar-logo-line"></div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-label">Principal</div>
    <a href="index.php" class="sidebar-link active">
      <i class="bi bi-grid-1x2"></i> Dashboard
    </a>

    <div class="sidebar-label">Gestión</div>
    <a href="productos.php" class="sidebar-link">
      <i class="bi bi-box-seam"></i> Productos
    </a>
    <a href="pedidos.php" class="sidebar-link">
      <i class="bi bi-bag-check"></i> Pedidos
      <?php if ($pendientes > 0): ?>
      <span style="margin-left:auto;background:var(--caramel);color:white;font-size:10px;padding:1px 7px;border-radius:10px;"><?= $pendientes ?></span>
      <?php endif; ?>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-avatar">
        <?= strtoupper(substr($_SESSION['admin_nombre'] ?? 'A', 0, 1)) ?>
      </div>
      <div>
        <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['admin_nombre'] ?? 'Administrador') ?></div>
        <div class="sidebar-user-role">Administrador</div>
      </div>
    </div>
    <a href="logout.php" class="btn-logout">
      <i class="bi bi-box-arrow-left"></i> Cerrar sesión
    </a>
  </div>
</aside>

<!-- ══════════ MAIN ══════════ -->
<div class="main">

  <!-- Topbar -->
  <div class="topbar">
    <div>
      <div class="topbar-title">Panel de Administración</div>
      <div class="topbar-date"><?= date('l, d \d\e F \d\e Y') ?></div>
    </div>
    <div class="topbar-right">
      <div class="topbar-badge">
        <span class="dot"></span>
        Sistema activo
      </div>
    </div>
  </div>

  <!-- Página -->
  <div class="page">

    <!-- ── MÉTRICAS ── -->
    <div class="metrics-grid">

      <div class="metric-card mc-1">
        <div class="metric-header">
          <div class="metric-icon-wrap mi-1"><i class="bi bi-cash-stack"></i></div>
          <span class="metric-trend trend-neu">Este mes</span>
        </div>
        <div class="metric-value">$<?= number_format($ingresos_mes, 0, ',', '.') ?></div>
        <div class="metric-label">Ingresos del mes</div>
        <div class="metric-sub">Pedidos entregados acumulados</div>
      </div>

      <div class="metric-card mc-2">
        <div class="metric-header">
          <div class="metric-icon-wrap mi-2"><i class="bi bi-bag-check"></i></div>
          <span class="metric-trend trend-up"><?= $entregados ?> entregados</span>
        </div>
        <div class="metric-value"><?= $total_pedidos ?></div>
        <div class="metric-label">Pedidos totales</div>
        <div class="metric-sub"><?= $en_proceso ?> en proceso ahora mismo</div>
      </div>

      <div class="metric-card mc-3">
        <div class="metric-header">
          <div class="metric-icon-wrap mi-3"><i class="bi bi-box-seam"></i></div>
          <span class="metric-trend trend-up"><?= $productos_activos ?> activos</span>
        </div>
        <div class="metric-value"><?= $total_productos ?></div>
        <div class="metric-label">Productos en catálogo</div>
        <div class="metric-sub"><?= $total_productos - $productos_activos ?> no disponibles</div>
      </div>

      <div class="metric-card mc-4">
        <div class="metric-header">
          <div class="metric-icon-wrap mi-4"><i class="bi bi-hourglass-split"></i></div>
          <span class="metric-trend trend-warn">Requieren atención</span>
        </div>
        <div class="metric-value"><?= $pendientes ?></div>
        <div class="metric-label">Pedidos pendientes</div>
        <div class="metric-sub">Sin procesar todavía</div>
      </div>

    </div>

    <!-- ── GRÁFICA ── -->
    <div class="section-head">
      <div class="section-title">Ventas de la semana</div>
      <div class="section-tag">Últimos 7 días</div>
    </div>
    <div class="chart-card">
      <div class="chart-legend">
        <div class="legend-item"><span class="legend-dot" style="background:var(--mocha);"></span> Ingresos ($)</div>
        <div class="legend-item"><span class="legend-dot" style="background:var(--caramel);"></span> N° de pedidos</div>
      </div>
      <div style="position:relative;width:100%;height:250px;">
        <canvas id="chartVentas"></canvas>
      </div>
    </div>

    <!-- ── FILA INFERIOR ── -->
    <div class="bottom-grid">

      <!-- Top productos -->
      <div class="data-card">
        <div class="section-head">
          <div class="section-title">Más pedidos</div>
          <div class="section-tag">Top 5</div>
        </div>
        <?php
        $bar_colors = ['#3b2314','#c0703a','#c9a84c','#d4737a','#4a7c59'];
        if (!empty($top_productos)):
          foreach ($top_productos as $i => $prod):
            $pct    = $max_top > 0 ? round(($prod['total'] / $max_top) * 100) : 0;
            $nombre = (string)$prod['_id'];
            $color  = $bar_colors[$i % count($bar_colors)];
        ?>
        <div class="prod-row">
          <div class="prod-rank"><?= $i + 1 ?></div>
          <div class="prod-info">
            <div class="prod-name"><?= htmlspecialchars($nombre) ?></div>
            <div class="prod-bar-bg">
              <div class="prod-bar-fill" style="width:<?= $pct ?>%;background:<?= $color ?>;"></div>
            </div>
          </div>
          <div class="prod-qty"><?= $prod['total'] ?></div>
        </div>
        <?php endforeach; else: ?>
        <p style="color:var(--muted);font-size:13px;margin-top:1rem;">Sin datos de ventas aún.</p>
        <?php endif; ?>

        <!-- Acciones rápidas debajo -->
        <div style="margin-top:1.5rem; padding-top:1.25rem; border-top:1px solid #f5ede3;">
          <div class="section-title" style="margin-bottom:0.9rem;">Acciones rápidas</div>
          <div class="actions-grid">
            <a href="productos.php" class="action-btn">
              <div class="action-icon ai-mocha"><i class="bi bi-plus-lg"></i></div>
              <div class="action-text">
                <strong>Nuevo producto</strong>
                <span>Agregar al catálogo</span>
              </div>
              <i class="bi bi-arrow-right action-arrow"></i>
            </a>
            <a href="pedidos.php" class="action-btn">
              <div class="action-icon ai-caramel"><i class="bi bi-list-check"></i></div>
              <div class="action-text">
                <strong>Ver pedidos</strong>
                <span>Gestionar todos</span>
              </div>
              <i class="bi bi-arrow-right action-arrow"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Pedidos recientes -->
      <div class="data-card">
        <div class="section-head">
          <div class="section-title">Pedidos recientes</div>
          <a href="pedidos.php" style="font-size:12px;color:var(--caramel);text-decoration:none;">Ver todos →</a>
        </div>
        <?php if (!empty($pedidos_recientes)): ?>
        <table class="pedidos-table">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Total</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pedidos_recientes as $pedido):
              $estado  = $pedido['estado'] ?? 'Pendiente';
              $clase   = match(strtolower($estado)) {
                'entregado'  => 'bs-entregado',
                'en proceso' => 'bs-proceso',
                'cancelado'  => 'bs-cancelado',
                default      => 'bs-pendiente'
              };
              $cliente = $pedido['nombre_cliente'] ?? $pedido['telefono'] ?? 'Cliente';
              $inicial = strtoupper(substr($cliente, 0, 1));
              $total_p = number_format($pedido['total'] ?? 0, 0, ',', '.');
            ?>
            <tr>
              <td>
                <div class="cliente-cell">
                  <div class="cliente-avatar"><?= $inicial ?></div>
                  <?= htmlspecialchars($cliente) ?>
                </div>
              </td>
              <td><strong>$<?= $total_p ?></strong></td>
              <td><span class="badge-estado <?= $clase ?>"><?= htmlspecialchars($estado) ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
        <p style="color:var(--muted);font-size:13px;margin-top:1rem;">No hay pedidos recientes.</p>
        <?php endif; ?>
      </div>

    </div>

  </div><!-- /page -->
</div><!-- /main -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  const labels   = <?= $js_labels ?>;
  const pedidos  = <?= $js_pedidos ?>;
  const ingresos = <?= $js_ingresos ?>;

  new Chart(document.getElementById('chartVentas'), {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Ingresos ($)',
          data: ingresos,
          backgroundColor: 'rgba(59,35,20,0.85)',
          borderRadius: 8,
          borderSkipped: false,
          yAxisID: 'y'
        },
        {
          label: 'Pedidos',
          data: pedidos,
          backgroundColor: 'rgba(192,112,58,0.75)',
          borderRadius: 8,
          borderSkipped: false,
          yAxisID: 'y1'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#3b2314',
          titleColor: '#f5e6d0',
          bodyColor: '#e8d5c4',
          padding: 12,
          cornerRadius: 10,
          displayColors: true,
          boxRadius: 4
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { size: 11, family: 'DM Sans' }, color: '#9a7b6a' },
          border: { display: false }
        },
        y: {
          position: 'left',
          grid: { color: '#f5ede3', drawBorder: false },
          ticks: { font: { size: 11, family: 'DM Sans' }, color: '#9a7b6a' },
          border: { display: false, dash: [4,4] }
        },
        y1: {
          position: 'right',
          grid: { display: false },
          ticks: { font: { size: 11, family: 'DM Sans' }, color: '#9a7b6a' },
          border: { display: false }
        }
      }
    }
  });
</script>

</body>
</html>