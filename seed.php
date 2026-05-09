<?php
require_once __DIR__ . '/includes/db.php';

// Crear admin por defecto
$email = 'admin@reposteria.test';
$check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$check->execute([$email]);
if (!$check->fetch()) {
    $passHash = password_hash('123456', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (nombre,email,password,rol) VALUES (?,?,?,?)")
        ->execute(['Administrador', $email, $passHash, 'admin']);
    echo "✅ Usuario admin creado: $email / 123456<br>";
} else {
    echo "ℹ️ Usuario admin ya existe<br>";
}

// Crear algunos productos de ejemplo
$productos = [
    ['Torta de chocolate', 'Deliciosa torta húmeda con cobertura de chocolate.', 25000, 'torta_choco.jpg'],
    ['Cupcakes surtidos', 'Caja de 6 cupcakes de diferentes sabores.', 18000, 'cupcakes.jpg'],
    ['Brownie con nueces', 'Brownie esponjoso con trozos de nuez y chispas de chocolate.', 15000, 'brownie.jpg']
];

foreach ($productos as $p) {
    $stmt = $pdo->prepare("SELECT id FROM productos WHERE nombre = ?");
    $stmt->execute([$p[0]]);
    if (!$stmt->fetch()) {
        $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, disponible, imagen) VALUES (?,?,?,?,?)")
            ->execute([$p[0], $p[1], $p[2], 1, $p[3]]);
        echo "🍰 Producto creado: {$p[0]}<br>";
    } else {
        echo "⚠️ Producto {$p[0]} ya existe<br>";
    }
}

echo "<br>✅ Base de datos inicial lista. Ahora puedes entrar al admin.";
