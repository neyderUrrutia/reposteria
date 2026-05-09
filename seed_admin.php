<?php
require_once __DIR__ . '/includes/db.php';

// Datos del admin
$email = 'admin@reposteria.com';
$nombre = 'Administrador';
$passPlano = 'admin123';

// Comprobar si ya existe
$check = $pdo->prepare("SELECT id FROM admin WHERE email = ?");
$check->execute([$email]);

if (!$check->fetch()) {
    // Crear hash seguro
    $passHash = password_hash($passPlano, PASSWORD_DEFAULT);

    // Insertar en tabla admin
    $stmt = $pdo->prepare("INSERT INTO admin (nombre, email, password) VALUES (?,?,?)");
    $stmt->execute([$nombre, $email, $passHash]);

    echo "✅ Admin creado correctamente:<br>";
    echo "📧 Email: $email<br>";
    echo "🔐 Contraseña: $passPlano<br>";
} else {
    echo "ℹ️ El usuario admin ya existe.<br>";
}
