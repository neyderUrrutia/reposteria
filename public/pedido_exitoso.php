<?php
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

if (!isset($_GET['id'])) {
    header('Location: catalogo.php');
    exit;
}

$pedido_id = (int)$_GET['id'];


// Obtener pedido
$stmt = $pdo->prepare("
    SELECT *
    FROM pedidos
    WHERE id = ?
");

$stmt->execute([$pedido_id]);

$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    die("Pedido no encontrado");
}


// Obtener items
$stmt = $pdo->prepare("
    SELECT *
    FROM pedido_items
    WHERE pedido_id = ?
");

$stmt->execute([$pedido_id]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Mensaje para WhatsApp

$message = "Pedido #%23{$pedido['id']}%0A";
$message .= "Cliente: {$pedido['nombre_cliente']}%0A";
$message .= "Teléfono: {$pedido['telefono']}%0A%0A";

foreach ($items as $it) {

    $message .=
    "{$it['nombre_producto']} x{$it['cantidad']} - $" .
    number_format($it['precio'], 0, ',', '.') .
    "%0A";

}

$message .= "%0ATotal: $" .
number_format($pedido['total'], 0, ',', '.');


// Número de la tienda

$tienda_phone = "573001112233";

$wa_link =
"https://wa.me/{$tienda_phone}?text=" . $message;

?>
<!doctype html>
<html lang="es">
<head>

<meta charset="utf-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>Pedido recibido | Atrato Dulce</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

</head>

<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container py-5">

<div class="card p-4 shadow">

<h3 class="text-success">

¡Pedido recibido!
#<?= $pedido['id'] ?>

</h3>

<p>

Gracias
<strong>

<?= h($pedido['nombre_cliente']) ?>

</strong>

Hemos registrado tu pedido con total

<strong>

$
<?= number_format(
$pedido['total'],
0,
',',
'.'
) ?>

</strong>

</p>

<hr>

<h5>Detalle del pedido:</h5>

<ul>

<?php foreach ($items as $it): ?>

<li>

<?= h($it['nombre_producto']) ?>

x

<?= $it['cantidad'] ?>

—

$

<?= number_format(
$it['precio'],
0,
',',
'.'
) ?>

</li>

<?php endforeach; ?>

</ul>

<div class="mt-4">

<a
href="<?= $wa_link ?>"
target="_blank"
class="btn btn-success me-2">

<i class="bi bi-whatsapp"></i>

Enviar pedido por WhatsApp

</a>


<a
href="javascript:window.print()"
class="btn btn-outline-secondary">

Imprimir factura

</a>

</div>

</div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>