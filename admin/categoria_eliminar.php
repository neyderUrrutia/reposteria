<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: categorias.php");
    exit;
}

$id = intval($_GET['id']);

// OJO: solo eliminar si ningún producto usa la categoría
$check = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE categoria_id = ?");
$check->execute([$id]);
$usada = $check->fetchColumn();

if ($usada > 0) {
    die("❌ No puedes eliminar esta categoría porque tiene productos.");
}

$stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
$stmt->execute([$id]);

header("Location: categorias.php");
exit;
