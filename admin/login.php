<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../includes/db.php';

// Si ya está logueado como admin, redirigir directo
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = trim($_POST['correo'] ?? '');
    $clave  = trim($_POST['clave']  ?? '');

    try {
        $admin = $db->usuarios->findOne([
            'correo' => $correo,
            'rol'    => 'admin'
        ]);

        if ($admin && password_verify($clave, $admin['clave'])) {
            $_SESSION['admin_id']     = (string)$admin['_id'];
            $_SESSION['admin_nombre'] = $admin['nombre'];
            header("Location: index.php");
            exit;
        } else {
            $mensaje = "Correo o contraseña incorrectos.";
        }

    } catch (Exception $e) {
        $mensaje = "Error de conexión. Intenta de nuevo.";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Acceso Administrador | Atrato Dulce</title>

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
      background: var(--mocha);
      background-image:
        radial-gradient(circle at 20% 50%, rgba(192,112,58,.25) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(201,168,76,.15) 0%, transparent 40%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'DM Sans', sans-serif;
      padding: 1rem;
    }

    .login-card {
      background: #fff;
      border-radius: 24px;
      box-shadow: 0 8px 40px rgba(59,35,20,.3);
      width: 100%;
      max-width: 400px;
      overflow: hidden;
    }

    /* HEADER */
    .card-header-ad {
      background: var(--mocha);
      padding: 2rem 2rem 1.75rem;
      text-align: center;
      position: relative;
    }
    .card-header-ad::after {
      content: '';
      position: absolute;
      bottom: 0; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--caramel), var(--rose), var(--gold));
    }
    .card-logo {
      width: 64px; height: 64px;
      background: var(--warm);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 1rem;
      font-size: 28px;
    }
    .card-header-ad h4 {
      font-family: 'Cormorant Garamond', serif;
      color: var(--gold);
      font-size: 1.4rem;
      font-weight: 600;
      margin-bottom: 4px;
    }
    .card-header-ad p {
      color: rgba(255,255,255,.5);
      font-size: 12px;
      margin: 0;
      text-transform: uppercase;
      letter-spacing: 2px;
    }

    /* BODY */
    .card-body-ad { padding: 2rem; }

    /* ALERTA */
    .alerta-error {
      background: #fdecea;
      color: #8c1a1a;
      border-left: 4px solid #e53935;
      border-radius: 10px;
      padding: 0.75rem 1rem;
      font-size: 13px;
      margin-bottom: 1.25rem;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* INPUTS */
    .input-group-ad { margin-bottom: 1.1rem; }
    .form-label-ad {
      font-size: 12px; font-weight: 500;
      color: var(--muted); text-transform: uppercase;
      letter-spacing: .06em; margin-bottom: 6px; display: block;
    }
    .input-wrap { position: relative; }
    .input-icon {
      position: absolute; left: 14px; top: 50%;
      transform: translateY(-50%);
      color: var(--muted); font-size: 15px; pointer-events: none;
    }
    .toggle-pass {
      position: absolute; right: 14px; top: 50%;
      transform: translateY(-50%);
      color: var(--muted); font-size: 15px;
      cursor: pointer; border: none; background: none; padding: 0;
    }
    .toggle-pass:hover { color: var(--caramel); }
    .form-control-ad {
      width: 100%;
      border: 1.5px solid rgba(192,112,58,.25);
      border-radius: 10px;
      padding: 0.65rem 1rem 0.65rem 2.5rem;
      font-size: 14px; color: var(--text);
      background: var(--cream);
      transition: border-color .2s, box-shadow .2s;
      outline: none;
    }
    .form-control-ad:focus {
      border-color: var(--caramel);
      box-shadow: 0 0 0 3px rgba(192,112,58,.12);
      background: #fff;
    }
    .con-toggle { padding-right: 2.5rem; }

    /* BOTÓN */
    .btn-ingresar {
      width: 100%; background: var(--caramel); color: #fff;
      border: none; border-radius: 50px; padding: 0.75rem;
      font-size: 14px; font-weight: 500; cursor: pointer;
      transition: background .2s, transform .1s;
      display: flex; align-items: center; justify-content: center; gap: 8px;
      margin-top: 0.5rem;
    }
    .btn-ingresar:hover  { background: var(--mocha); }
    .btn-ingresar:active { transform: scale(.98); }

    /* FOOTER CARD */
    .card-footer-ad {
      text-align: center;
      padding: 1rem 2rem 1.5rem;
      border-top: 1px solid var(--warm);
    }
    .card-footer-ad a {
      font-size: 13px; color: var(--caramel); text-decoration: none;
    }
    .card-footer-ad a:hover { text-decoration: underline; }
  </style>
</head>
<body>

<div class="login-card">

  <!-- HEADER -->
  <div class="card-header-ad">
    <div class="card-logo">🍰</div>
    <h4>Atrato Dulce</h4>
    <p>Panel de Administración</p>
  </div>

  <!-- BODY -->
  <div class="card-body-ad">

    <?php if ($mensaje): ?>
    <div class="alerta-error">
      <i class="bi bi-exclamation-circle-fill"></i>
      <?= htmlspecialchars($mensaje) ?>
    </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">

      <!-- CORREO -->
      <div class="input-group-ad">
        <label class="form-label-ad">Correo electrónico</label>
        <div class="input-wrap">
          <i class="bi bi-envelope input-icon"></i>
          <input
            type="email" name="correo"
            class="form-control-ad"
            placeholder="admin@atratodulce.com"
            value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
            required>
        </div>
      </div>

      <!-- CONTRASEÑA -->
      <div class="input-group-ad">
        <label class="form-label-ad">Contraseña</label>
        <div class="input-wrap">
          <i class="bi bi-lock input-icon"></i>
          <input
            type="password" name="clave" id="inputClave"
            class="form-control-ad con-toggle"
            placeholder="••••••••"
            required>
          <button type="button" class="toggle-pass" onclick="togglePass()">
            <i class="bi bi-eye" id="iconOjo"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-ingresar">
        <i class="bi bi-box-arrow-in-right"></i> Ingresar al panel
      </button>

    </form>

  </div>

  <!-- FOOTER -->
  <div class="card-footer-ad">
    <a href="../public/index.php">
      <i class="bi bi-arrow-left"></i> Volver al sitio
    </a>
  </div>

</div>

<script>
  function togglePass() {
    const input = document.getElementById('inputClave');
    const icon  = document.getElementById('iconOjo');
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