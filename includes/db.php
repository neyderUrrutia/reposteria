<?php
// ── Sesión primero, antes de cualquier output ──
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $db = $client->reposteria_db;
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}