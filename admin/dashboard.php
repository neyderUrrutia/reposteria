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

// Total productos
$total_productos = $db->productos->countDocuments();

// Productos disponibles
$productos_activos = $db->productos->countDocuments(['disponible' => 1]);

// Total pedidos
$total_pedidos = $db->pedidos->countDocuments();

// Pedidos pendientes
$pendientes = $db->pedidos->countDocuments(['estado' => 'pendiente']);

// Pedidos entregados
$entregados = $db->pedidos->countDocuments(['estado' => 'entregado']);

// Pedidos en proceso
$en_proceso = $db->pedidos->countDocuments(['estado' => 'en proceso']);

// =============================================
// 💰 INGRESOS DEL MES (suma total de pedidos entregados este mes)
// =============================================
$inicio_mes = new MongoDB\BSON\UTCDateTime(strtotime(date('Y-m-01')) * 1000);
$fin_mes    = new MongoDB\BSON\UTCDateTime(strtotime(date('Y-m-t 23:59:59')) * 1000);

$pipeline_ingresos = [
  ['$match' => [
    'estado'     => 'entregado',
    'created_at' => ['$gte' => $inicio_mes, '$lte' => $fin_mes]
  ]],
  ['$group' => ['_id' => null, 'total' => ['$sum' => '$total']]]
];
$res_ingresos = $db->pedidos->aggregate($pipeline_ingresos)->toArray();
$ingresos_mes = !empty($res_ingresos) ? $res_ingresos[0]['total'] : 0;

// =============================================
// 📅 VENTAS POR DÍA (últimos 7 días) — para la gráfica de barras
// =============================================
$hace7 = new MongoDB\BSON\UTCDateTime(strtotime('-6 days midnight') * 1000);

$pipeline_dias = [
  ['$match' => ['created_at' => ['$gte' => $hace7]]],
  ['$group' => [
    '_id'     => ['$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$created_at']],
    'pedidos' => ['$sum' => 1],
    'ingresos'=> ['$sum' => '$total']
  ]],
  ['$sort' => ['_id' => 1]]
];
$ventas_dias_raw = $db->pedidos->aggregate($pipeline_dias)->toArray();

// Rellenar los 7 días aunque no tengan datos
$ventas_labels   = [];
$ventas_pedidos  = [];
$ventas_ingresos = [];
$dias_map = [];
foreach ($ventas_dias_raw as $d) {
  $dias_map[(string)$d['_id']] = $d;
}
for ($i = 6; $i >= 0; $i--) {
  $fecha = date('Y-m-d', strtotime("-$i days"));
  $label = date('D', strtotime($fecha)); // Mon, Tue...
  $labels_es = ['Mon'=>'Lun','Tue'=>'Mar','Wed'=>'Mié','Thu'=>'Jue','Fri'=>'Vie','Sat'=>'Sáb','Sun'=>'Dom'];
  $ventas_labels[]   = $labels_es[$label] ?? $label;
  $ventas_pedidos[]  = isset($dias_map[$fecha]) ? (int)$dias_map[$fecha]['pedidos']  : 0;
  $ventas_ingresos[] = isset($dias_map[$fecha]) ? (float)$dias_map[$fecha]['ingresos']: 0;
}

// =============================================
// 🎂 PEDIDOS POR CATEGORÍA — para la gráfica de dona
// =============================================
$pipeline_cat = [
  ['$lookup' => [
    'from'         => 'productos',
    'localField'   => 'producto_id',
    'foreignField' => '_id',
    'as'           => 'producto'
  ]],
  ['$unwind' => '$producto'],
  ['$group'  => [
    '_id'   => '$producto.categoria',
    'total' => ['$sum' => 1]
  ]],
  ['$sort' => ['total' => -1]],
  ['$limit' => 5]
];
$categorias_raw = $db->pedidos->aggregate($pipeline_cat)->toArray();
$cat_labels = [];
$cat_data   = [];
foreach ($categorias_raw as $c) {
  $cat_labels[] = (string)$c['_id'];
  $cat_data[]   = (int)$c['total'];
}
// Fallback si la colección está vacía
if (empty($cat_labels)) {
  $cat_labels = ['Tortas', 'Postres', 'Panes', 'Bebidas'];
  $cat_data   = [0, 0, 0, 0];
}

// =============================================
// 🏆 PRODUCTOS MÁS VENDIDOS (top 5)
// =============================================
$pipeline_top = [
  ['$group' => [
    '_id'   => '$producto_id',
    'total' => ['$sum' => 1]
  ]],
  ['$sort'  => ['total' => -1]],
  ['$limit' => 5],
  ['$lookup' => [
    'from'         => 'productos',
    'localField'   => '_id',
    'foreignField' => '_id',
    'as'           => 'producto'
  ]],
  ['$unwind' => '$producto']
];
$top_productos = $db->pedidos->aggregate($pipeline_top)->toArray();
$max_top = !empty($top_productos) ? (int)$top_productos[0]['total'] : 1;

// =============================================
// 🕐 PEDIDOS RECIENTES (últimos 6)
// =============================================
$pedidos_recientes = $db->pedidos->find(
  [],
  ['sort' => ['created_at' => -1], 'limit' => 6]
)->toArray();

// JSON para JS
$js_labels   = json_encode($ventas_labels,   JSON_UNESCAPED_UNICODE);
$js_pedidos  = json_encode($ventas_pedidos);
$js_ingresos = json_encode($ventas_ingresos);
$js_cat_lbl  = json_encode($cat_labels,  JSON_UNESCAPED_UNICODE);
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
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">

  <style>
    :root {
      --morado-oscuro:  #26215C;
      --morado:         #534AB7;
      --morado-claro:   #EEEDFE;
      --dorado:         #FAC775;
      --dorado-oscuro:  #854F0B;
      --dorado-claro:   #FAEEDA;
      --verde:          #3B6D11;
      --verde-claro:    #EAF3DE;
      --rojo:           #A32D2D;
      --rojo-claro:     #FCEBEB;
      --naranja:        #D85A30;
      --naranja-claro:  #FAECE7;
      --gris-bg:        #F5F4F9;
      --texto:          #1a1730;
      --texto-muted:    #6b6880;
    }

    * { box-sizing: border-box; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--gris-bg);
      color: var(--texto);
      min-height: 100vh;
    }

    /* ─── NAVBAR ─── */
    .navbar-ad {
      background: var(--morado-oscuro);
      padding: 0.85rem 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 100;
      border-bottom: 3px solid var(--dorado);
    }
    .navbar-brand-ad {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
    }
    .navbar-logo {
      width: 38px;
      height: 38px;
      background: var(--dorado);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
    }
    .navbar-title {
      font-family: 'Playfair Display', serif;
      color: var(--dorado);
      font-size: 1.2rem;
      font-weight: 700;
      letter-spacing: 0.02em;
    }
    .navbar-links { display: flex; align-items: center; gap: 8px; }
    .nav-link-ad {
      color: #CECBF6;
      text-decoration: none;
      font-size: 13px;
      font-weight: 500;
      padding: 6px 14px;
      border-radius: 6px;
      border: 1px solid rgba(206,203,246,0.3);
      transition: all 0.2s;
    }
    .nav-link-ad:hover { background: rgba(206,203,246,0.12); color: #fff; }
    .nav-user { color: #CECBF6; font-size: 13px; margin-right: 4px; }
    .btn-logout-ad {
      background: var(--dorado);
      color: var(--morado-oscuro);
      border: none;
      padding: 6px 16px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 500;
      text-decoration: none;
      transition: opacity 0.2s;
    }
    .btn-logout-ad:hover { opacity: 0.85; color: var(--morado-oscuro); }

    /* ─── LAYOUT ─── */
    .dash-container { max-width: 1280px; margin: 0 auto; padding: 2rem 1.5rem; }

    .dash-header { margin-bottom: 2rem; }
    .dash-header h2 {
      font-family: 'Playfair Display', serif;
      font-size: 1.6rem;
      font-weight: 700;
      color: var(--morado-oscuro);
      margin-bottom: 2px;
    }
    .dash-header p { font-size: 13px; color: var(--texto-muted); margin: 0; }

    .section-label {
      font-size: 11px;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--texto-muted);
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
      border: 1px solid rgba(83,74,183,0.1);
      position: relative;
      overflow: hidden;
    }
    .metric-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
    }
    .mc-purple::before { background: var(--morado); }
    .mc-gold::before   { background: var(--dorado); }
    .mc-green::before  { background: var(--verde); }
    .mc-red::before    { background: var(--rojo); }

    .metric-icon {
      width: 40px; height: 40px;
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
      margin-bottom: 12px;
    }
    .ic-purple { background: var(--morado-claro); color: var(--morado); }
    .ic-gold   { background: var(--dorado-claro); color: var(--dorado-oscuro); }
    .ic-green  { background: var(--verde-claro);  color: var(--verde); }
    .ic-red    { background: var(--rojo-claro);   color: var(--rojo); }

    .metric-label { font-size: 12px; color: var(--texto-muted); margin-bottom: 4px; font-weight: 500; }
    .metric-value { font-size: 26px; font-weight: 700; color: var(--texto); line-height: 1; margin-bottom: 6px; }
    .metric-sub   { font-size: 11px; color: var(--texto-muted); }
    .metric-sub.up   { color: var(--verde); }
    .metric-sub.down { color: var(--rojo); }

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
      border: 1px solid rgba(83,74,183,0.1);
    }
    .card-title-ad {
      font-size: 14px;
      font-weight: 500;
      color: var(--texto);
      margin-bottom: 0.75rem;
    }
    .legend-row {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 10px;
      font-size: 11px;
      color: var(--texto-muted);
    }
    .legend-row span { display: flex; align-items: center; gap: 5px; }
    .ldot { width: 10px; height: 10px; border-radius: 3px; }

    /* ─── BOTTOM ROW ─── */
    .bottom-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }

    /* ─── TOP PRODUCTOS ─── */
    .prod-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 0;
      border-bottom: 1px solid #f0eefa;
      font-size: 13px;
    }
    .prod-item:last-child { border: none; }
    .prod-name { flex: 0 0 160px; color: var(--texto); font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .prod-bar-wrap { flex: 1; height: 7px; background: var(--morado-claro); border-radius: 4px; }
    .prod-bar { height: 7px; border-radius: 4px; }
    .prod-qty { flex: 0 0 30px; text-align: right; color: var(--texto-muted); font-size: 12px; }

    /* ─── PEDIDOS TABLE ─── */
    .orders-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    .orders-table th {
      text-align: left;
