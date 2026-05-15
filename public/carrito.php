<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$collection = $db->productos;

/* AÑADIR */
if (isset($_GET['add'])) {
    $id = $_GET['add'];
    if (isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id]['cantidad']++;
    } else {
        $producto = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
        if ($producto) {
            $_SESSION['carrito'][$id] = [
                'id'       => (string)$producto->_id,
                'nombre'   => $producto->nombre ?? '',
                'precio'   => $producto->precio ?? 0,
                'imagen'   => $producto->imagen ?? '',
                'cantidad' => 1
            ];
        }
    }
    header("Location: carrito.php"); exit;
}

/* ELIMINAR */
if (isset($_GET['del'])) {
    unset($_SESSION['carrito'][$_GET['del']]);
    header("Location: carrito.php"); exit;
}

/* SUMAR */
if (isset($_GET['sum'])) {
    $id = $_GET['sum'];
    if (isset($_SESSION['carrito'][$id])) $_SESSION['carrito'][$id]['cantidad']++;
    header("Location: carrito.php"); exit;
}

/* RESTAR */
if (isset($_GET['res'])) {
    $id = $_GET['res'];
    if (isset($_SESSION['carrito'][$id]) && $_SESSION['carrito'][$id]['cantidad'] > 1)
        $_SESSION['carrito'][$id]['cantidad']--;
    header("Location: carrito.php"); exit;
}

/* TOTAL */
$total = 0;
foreach ($_SESSION['carrito'] as $p) {
    $total += ($p['precio'] ?? 0) * ($p['cantidad'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Carrito | Atrato Dulce</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="assets/style.css" rel="stylesheet">
</head>
<body>

<div class="stripe"></div>
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- BANNER INTERIOR -->
<div style="height:180px; background: linear-gradient(135deg, var(--mocha, #3b2314) 0%, #6b3a22 100%);
            display:flex; align-items:center; justify-content:center; margin-top:76px;">
  <div class="text-center text-white">
    <h1 style="font-family:'Cormorant Garamond',serif; font-size:2.4rem;">Tu carrito</h1>
    <p style="opacity:.75; font-size:.9rem;">Revisa tus productos antes de finalizar</p>
  </div>
</div>

<div class="container py-5" style="max-width:860px;">

  <?php if (empty($_SESSION['carrito'])): ?>

    <div class="text-center py-5">
      <i class="bi bi-cart-x" style="font-size:4rem; color:var(--caramel,#c0703a); opacity:.5;"></i>
      <h3 style="font-family:'Cormorant Garamond',serif; color:var(--mocha,#3b2314); margin-top:1rem;">
        Tu carrito está vacío
      </h3>
      <p style="color:var(--muted,#8a6f5e);">Aún no has agregado ningún producto.</p>
      <a href="catalogo.php" class="btn btn-danger mt-2 px-4">
        <i class="bi bi-basket me-1"></i> Ir al catálogo
      </a>
    </div>

  <?php else: ?>

    <table class="table cart-table align-middle">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Precio</th>
          <th class="text-center">Cantidad</th>
          <th>Subtotal</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($_SESSION['carrito'] as $p):
          $id       = $p['id'] ?? '';
          $nombre   = $p['nombre'] ?? 'Sin nombre';
          $precio   = $p['precio'] ?? 0;
          $imagen   = $p['imagen'] ?? '';
          $cantidad = $p['cantidad'] ?? 1;
        ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-3">
              <?php if ($imagen): ?>
                <img src="assets/uploads/<?= htmlspecialchars($imagen) ?>"
                     width="60" height="60"
                     style="object-fit:cover; border-radius:12px;">
              <?php else: ?>
                <div style="width:60px;height:60px;border-radius:12px;
                            background:var(--warm,#f5e6d0);display:flex;
                            align-items:center;justify-content:center;
                            color:var(--caramel,#c0703a);font-size:1.6rem;">
                  <i class="bi bi-image"></i>
                </div>
              <?php endif; ?>
              <span style="font-weight:500; color:var(--mocha,#3b2314);">
                <?= htmlspecialchars($nombre) ?>
              </span>
            </div>
          </td>

          <td style="color:var(--caramel,#c0703a); font-family:'Cormorant Garamond',serif; font-size:1.1rem;">
            $<?= number_format($precio, 0, ',', '.') ?>
          </td>

          <td class="text-center">
            <div class="d-flex align-items-center justify-content-center gap-2">
              <a href="carrito.php?res=<?= urlencode($id) ?>" class="btn-qty">−</a>
              <span style="font-weight:600; min-width:24px; text-align:center; color:var(--mocha,#3b2314);">
                <?= $cantidad ?>
              </span>
              <a href="carrito.php?sum=<?= urlencode($id) ?>" class="btn-qty">+</a>
            </div>
          </td>

          <td style="font-weight:600; color:var(--mocha,#3b2314);">
            $<?= number_format($precio * $cantidad, 0, ',', '.') ?>
          </td>

          <td>
            <a href="carrito.php?del=<?= urlencode($id) ?>" class="btn-del" title="Eliminar">
              <i class="bi bi-trash3"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- TOTAL + ACCIONES -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between
                mt-4 gap-3 pt-4"
         style="border-top: 2px solid var(--warm,#f5e6d0);">

      <a href="catalogo.php" class="btn btn-outline-mocha px-4">
        <i class="bi bi-arrow-left me-1"></i> Seguir comprando
      </a>

      <div class="text-md-end">
        <p style="font-size:.85rem; color:var(--muted,#8a6f5e); margin-bottom:.3rem;">Total a pagar</p>
        <p style="font-family:'Cormorant Garamond',serif; font-size:2rem;
                  font-weight:600; color:var(--caramel,#c0703a); margin:0;">
          $<?= number_format($total, 0, ',', '.') ?>
        </p>
        <a href="checkout.php" class="btn btn-success mt-2 px-4">
          Finalizar pedido <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>

    </div>

  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>