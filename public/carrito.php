<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db.php';

// Si ya está logueado, redirigir
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$mensaje = "";
$tipo    = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $clave  = $_POST['clave'] ?? '';
    $clave2 = $_POST['clave2'] ?? '';

    if (!$nombre || !$correo || !$clave || !$clave2) {
        $mensaje = "Todos los campos son obligatorios.";
        $tipo    = "error";
    } elseif ($clave !== $clave2) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo    = "error";
    } elseif (strlen($clave) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        $tipo    = "error";
    } else {
        $existe = $db->usuarios->findOne(['correo' => $correo]);
        if ($existe) {
            $mensaje = "Este correo ya está registrado.";
            $tipo    = "error";
        } else {
            $db->usuarios->insertOne([
                'nombre' => $nombre,
                'correo' => $correo,
                'clave'  => password_hash($clave, PASSWORD_DEFAULT),
                'fecha'  => new MongoDB\BSON\UTCDateTime()
            ]);
            header("Location: login.php?registrado=1");
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Crear cuenta | Atrato Dulce</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    :root {
      --cream:   #fdf6ee;
      --warm:    #f5e6d0;
      --mocha:   #3b2314;
      --caramel: #c0703a;
      --rose:    #d4737a;
      --gold:    #c9a84c;
      --text:    #2c1a0e;
      --muted:   #8a6f5e;
    }
    * { box-sizing: border-box; }
    body {
      min-height: 100vh;
      background: var(--cream);
      background-image: radial-gradient(circle at 10% 90%, rgba(192,112,58,.12) 0%, transparent 50%),
                        radial-gradient(circle at 90% 10%, rgba(212,115,122,.1) 0%, transparent 40%);
      display: flex; align-items: center; justify-content: center;
      font-family: 'DM Sans', sans-serif; padding: 2rem 1rem;
    }
    .register-wrap {
      display: grid;
      grid-template-columns: 1fr 1fr;
      max-width: 860px;
      width: 100%;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 16px 60px rgba(59,35,20,.18);
    }
    /* PANEL IZQUIERDO */
    .panel-left {
      background: var(--mocha);
      padding: 3rem 2.5rem;
      display: flex; flex-direction: column;
      justify-content: space-between;
      position: relative;
      overflow: hidden;
    }
    .panel-left::before {
      content: '🍰';
      position: absolute; bottom: -20px; right: -20px;
      font-size: 10rem; opacity: .06;
    }
    .panel-left::after {
      content: '';
      position: absolute; bottom: 0; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--caramel), var(--rose), var(--gold));
    }
    .panel-logo {
      width: 56px; height: 56px; background: var(--warm);
      border-radius: 50%; display: flex; align-items: center;
      justify-content: center; font-size: 24px; margin-bottom: 1.5rem;
    }
    .panel-left h2 {
      font-family: 'Cormorant Garamond', serif;
      color: var(--gold); font-size: 1.8rem; font-weight: 600;
      margin-bottom: 0.5rem;
    }
    .panel-left p { color: rgba(255,255,255,.55); font-size: 13.5px; line-height: 1.7; }
    .beneficios { margin-top: 2rem; }
    .beneficio {
      display: flex; align-items: center; gap: 10px;
      color: rgba(255,255,255,.7); font-size: 13px; margin-bottom: 12px;
    }
    .beneficio i { color: var(--gold); font-size: 15px; }
    .panel-login-link {
      margin-top: 2rem; font-size: 13px; color: rgba(255,255,255,.4);
    }
    .panel-login-link a { color: var(--gold); text-decoration: none; }
    .panel-login-link a:hover { text-decoration: underline; }

    /* PANEL DERECHO */
    .panel-right {
      background: #fff;
      padding: 3rem 2.5rem;
    }
    .panel-right h3 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.6rem; font-weight: 600;
      color: var(--mocha); margin-bottom: 0.25rem;
    }
    .panel-right .subtitle {
      font-size: 13px; color: var(--muted); margin-bottom: 1.75rem;
    }

    /* ALERTA */
    .alerta {
      border-radius: 10px; padding: 0.75rem 1rem;
      font-size: 13px; margin-bottom: 1.25rem;
      display: flex; align-items: center; gap: 8px;
    }
    .alerta-error { background: #fdecea; color: #8c1a1a; border-left: 4px solid #e53935; }

    /* INPUTS */
    .input-group-ad { margin-bottom: 1rem; }
    .form-label-ad {
      font-size: 11.5px; font-weight: 500; color: var(--muted);
      text-transform: uppercase; letter-spacing: .06em;
      margin-bottom: 5px; display: block;
    }
    .input-wrap { position: relative; }
    .input-icon {
      position: absolute; left: 13px; top: 50%;
      transform: translateY(-50%);
      color: var(--muted); font-size: 14px; pointer-events: none;
    }
    .toggle-pass {
      position: absolute; right: 13px; top: 50%;
      transform: translateY(-50%);
      color: var(--muted); font-size: 14px;
      cursor: pointer; border: none; background: none; padding: 0;
    }
    .toggle-pass:hover { color: var(--caramel); }
    .form-control-ad {
      width: 100%;
      border: 1.5px solid rgba(192,112,58,.25);
      border-radius: 10px;
      padding: 0.6rem 1rem 0.6rem 2.4rem;
      font-size: 13.5px; color: var(--text);
      background: var(--cream);
      transition: border-color .2s, box-shadow .2s;
      outline: none;
    }
    .form-control-ad:focus {
      border-color: var(--caramel);
      box-shadow: 0 0 0 3px rgba(192,112,58,.12);
      background: #fff;
    }
    .con-toggle { padding-right: 2.4rem; }

    /* BOTÓN */
    .btn-registrar {
      width: 100%; background: var(--caramel); color: #fff;
      border: none; border-radius: 50px; padding: 0.72rem;
      font-size: 14px; font-weight: 500; cursor: pointer;
      transition: background .2s, transform .1s;
      display: flex; align-items: center; justify-content: center; gap: 8px;
      margin-top: 0.75rem;
    }
    .btn-registrar:hover  { background: var(--mocha); }
    .btn-registrar:active { transform: scale(.98); }

    .divider {
      text-align: center; font-size: 12px; color: var(--muted);
      margin: 1.25rem 0; position: relative;
    }
    .divider::before, .divider::after {
      content: ''; position: absolute; top: 50%; width: 42%; height: 1px;
      background: rgba(192,112,58,.2);
    }
    .divider::before { left: 0; }
    .divider::after  { right: 0; }

    .link-login {
      display: block; text-align: center; font-size: 13px;
      color: var(--caramel); text-decoration: none;
    }
    .link-login:hover { text-decoration: underline; }

    @media (max-width: 680px) {
      .register-wrap { grid-template-columns: 1fr; }
      .panel-left { display: none; }
    }
  </style>
</head>
<body>

<div class="register-wrap">

  <!-- IZQUIERDA -->
  <div class="panel-left">
    <div>
      <div class="panel-logo">🍰</div>
      <h2>Atrato Dulce</h2>
      <p>Únete y disfruta de la mejor pastelería artesanal del Chocó directamente en tu puerta.</p>
      <div class="beneficios">
        <div class="beneficio"><i class="bi bi-check-circle-fill"></i> Seguimiento de tus pedidos</div>
        <div class="beneficio"><i class="bi bi-check-circle-fill"></i> Historial de compras</div>
        <div class="beneficio"><i class="bi bi-check-circle-fill"></i> Ofertas exclusivas para miembros</div>
        <div class="beneficio"><i class="bi bi-check-circle-fill"></i> Proceso de compra más rápido</div>
      </div>
    </div>
    <div class="panel-login-link">
      ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
    </div>
  </div>

  <!-- DERECHA -->
  <div class="panel-right">
    <h3>Crear cuenta</h3>
    <p class="subtitle">Completa tus datos para registrarte</p>

    <?php if ($mensaje): ?>
    <div class="alerta alerta-error">
      <i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($mensaje) ?>
    </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">

      <div class="input-group-ad">
        <label class="form-label-ad">Nombre completo</label>
        <div class="input-wrap">
          <i class="bi bi-person input-icon"></i>
          <input type="text" name="nombre" class="form-control-ad"
            placeholder="Tu nombre completo"
            value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
        </div>
      </div>

      <div class="input-group-ad">
        <label class="form-label-ad">Correo electrónico</label>
        <div class="input-wrap">
          <i class="bi bi-envelope input-icon"></i>
          <input type="email" name="correo" class="form-control-ad"
            placeholder="tucorreo@email.com"
            value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" required>
        </div>
      </div>

      <div class="input-group-ad">
        <label class="form-label-ad">Contraseña</label>
        <div class="input-wrap">
          <i class="bi bi-lock input-icon"></i>
          <input type="password" name="clave" id="inputClave"
            class="form-control-ad con-toggle"
            placeholder="Mínimo 6 caracteres" required>
          <button type="button" class="toggle-pass" onclick="togglePass('inputClave','ojo1')">
            <i class="bi bi-eye" id="ojo1"></i>
          </button>
        </div>
      </div>

      <div class="input-group-ad">
        <label class="form-label-ad">Confirmar contraseña</label>
        <div class="input-wrap">
          <i class="bi bi-lock-fill input-icon"></i>
          <input type="password" name="clave2" id="inputClave2"
            class="form-control-ad con-toggle"
            placeholder="Repite tu contraseña" required>
          <button type="button" class="toggle-pass" onclick="togglePass('inputClave2','ojo2')">
            <i class="bi bi-eye" id="ojo2"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-registrar">
        <i class="bi bi-person-plus"></i> Crear mi cuenta
      </button>

    </form>

    <div class="divider">o</div>
    <a href="login.php" class="link-login">Ya tengo cuenta — Iniciar sesión</a>
  </div>

</div>

<script>
  function togglePass(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
      input.type = 'text';
      icon.className = 'bi bi-eye-slash';
    } else {
      input.type = 'password';
      icon.className = 'bi bi-eye';
    }
  }
</script>
</body>
</html>