<?php
// ── Iniciar sesión ──
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Cargar Composer ──
require __DIR__ . '/../vendor/autoload.php';

try {

    // ── Conexión MongoDB Atlas ──
    $client = new MongoDB\Client(
        "mongodb+srv://neyderpereaurrutia92_db_user:erPFIEblB7MEglyQ@cluster0.fuvknik.mongodb.net/reposteria_db?retryWrites=true&w=majority&tls=true"
    );

    // ── Base de datos ──
    $db = $client->reposteria_db;

} catch (Exception $e) {

    die("Error de conexión: " . $e->getMessage());
}