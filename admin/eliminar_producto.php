<?php
session_start();

use MongoDB\BSON\ObjectId;

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../includes/db.php';

// VALIDAR ID
if (!isset($_GET['id'])) {
    header("Location: pedidos.php");
    exit;
}

try {

    $id = new ObjectId($_GET['id']);

    // ELIMINAR PEDIDO
    $db->pedidos->deleteOne([
        '_id' => $id
    ]);

} catch (Exception $e) {
    // evita error si el id no es válido
}

header("Location: pedidos.php");
exit;
?>