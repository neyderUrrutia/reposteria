<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../vendor/autoload.php';

try {

    $uri = "mongodb+srv://neyderpereaurrutia92_db_user:Neyder123@cluster0.fuvknik.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0";

    $client = new MongoDB\Client($uri);

    $db = $client->selectDatabase('reposteria_db');

} catch (Exception $e) {

    die("Error de conexión MongoDB: " . $e->getMessage());
}