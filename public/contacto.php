<?php
require_once __DIR__ . '/../includes/db.php';

use MongoDB\BSON\UTCDateTime;

$mensaje = '';
$tipo    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email  = trim($_POST['email']  ?? '');
    $msj    = trim($_POST['mensaje'] ?? '');

    if ($nombre && $email && $msj) {
        try {
            $db->mensajes->insertOne([
                'nombre'  => $nombre,
                'email'   => $email,
                'mensaje' => $msj,
                'fecha'   => new UTCDateTime(),
                'estado'  => 'Nuevo'
            ]);
            $mensaje = "¡Gracias <strong>$nombre</strong>! Tu mensaje ha sido enviado correctamente. Te responderemos pronto.";
            $tipo    = 'exito';
        } catch (Exception $e) {
            $mensaje = "Ocurrió un error al enviar tu mensaje. Intenta de nuevo o escríbenos por WhatsApp.";
            $tipo    = 'error';
        }
    } else {
        $mensaje = "Por favor completa todos los campos antes de enviar.";
        $tipo    = 'aviso';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contacto | Atrato Dulce 🍰</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">

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
      --radius:  18px;
      --shadow:  0 8px 40px rgba(59,35,20,.10);
    }

    body { background-color: var(--cream); color: var(--text); font-family: 'DM Sans', sans-serif; }

    .stripe {
      height: 4px;
      background: linear-gradient(90deg, var(--caramel), var(--rose), var(--gold));
    }

    .promo-band {
      background: var(--mocha); color: #fff;
      text-align: center; padding: 1.1rem; font-size: 14px;
    }

    /* ── HERO ── */
    .page-hero {
      background: var(--warm);
      padding: 4rem 0 3rem;
      text-align: center;
    }
    .page-hero .label {
      font-size: .72rem; letter-spacing: 3px;
      text-transform: uppercase; color: var(--caramel);
      display: block; margin-bottom: 0.5rem;
    }
    .page-hero h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: clamp(2rem, 5vw, 3rem);
      font-weight: 600; color: var(--mocha); margin-bottom: 0.5rem;
    }
    .page-hero p { color: var(--muted); font-size: 15px; margin: 0; }
    .rule {
      width: 48px; height: 2px;
      background: linear-gradient(90deg, var(--caramel), var(--rose));
      margin: 0.75rem auto;
    }

    /* ── LAYOUT CONTACTO ── */
    .contacto-section { padding: 5rem 0; }

    /* ── TARJETA FORMULARIO ── */
    .form-card {
      background: #fff;
      border-radius: var(--radius);
      padding: 2.5rem;
      box-shadow: var(--shadow);
    }
    .form-card h3 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.6rem; font-weight: 600;
      color: var(--mocha); margin-bottom: 1.5rem; text-align: center;
    }
    .form-label { font-size: 13px; font-weight: 500; color: var(--text); margin-bottom: 5px; }
    .form-control {
      border: 1.5px solid rgba(192,112,58,.25);
      border-radius: 10px;
      padding: 0.65rem 1rem;
      font-size: 14px;
      color: var(--text);
      background: var(--cream);
      transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus {
      border-color: var(--caramel);
      box-shadow: 0 0 0 3px rgba(192,112,58,.12);
      background: #fff;
    }
    textarea.form-control { resize: vertical; min-height: 120px; }
    .btn-enviar {
      background: var(--caramel); color: #fff;
      border: none; border-radius: 50px;
      padding: 0.75rem 2rem; font-size: 14px; font-weight: 500;
      width: 100%; transition: background .2s;
      display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-enviar:hover { background: var(--mocha); }

    /* ── ALERTAS ESTILIZADAS ── */
    .alerta {
      border-radius: 12px; padding: 1rem 1.25rem;
      font-size: 13.5px; margin-bottom: 1.5rem;
      display: flex; align-items: flex-start; gap: 10px;
    }
    .alerta-exito { background: #eaf6e9; color: #2d6a2d; border-left: 4px solid #4caf50; }
    .alerta-error { background: #fdecea; color: #8c1a1a; border-left: 4px solid #e53935; }
    .alerta-aviso { background: var(--dorado-claro, #faeeda); color: #7a4a0b; border-left: 4px solid var(--caramel); }
    .alerta i { font-size: 18px; margin-top: 1px; flex-shrink: 0; }

    /* ── TARJETAS DE CONTACTO ── */
    .info-card {
      background: #fff;
      border-radius: var(--radius);
      padding: 1.5rem;
      box-shadow: 0 2px 16px rgba(59,35,20,.07);
      display: flex; align-items: flex-start; gap: 14px;
      margin-bottom: 16px;
      transition: .25s;
    }
    .info-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
    .info-icon {
      width: 44px; height: 44px; border-radius: 12px;
      background: var(--warm); color: var(--caramel);
      display: flex; align-items: center; justify-content: center;
      font-size: 20px; flex-shrink: 0;
    }
    .info-card h6 { font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 3px; font-weight: 500; }
    .info-card p  { font-size: 14px; color: var(--text); margin: 0; }
    .info-card a  { color: var(--caramel); text-decoration: none; }
    .info-card a:hover { text-decoration: underline; }

    /* ── WHATSAPP ── */
    .btn-whatsapp {
      background: #25D366; color: #fff;
      border: none; border-radius: 50px;
      padding: 0.7rem 1.75rem; font-size: 14px; font-weight: 500;
      text-decoration: none; display: inline-flex; align-items: center;
      gap: 8px; transition: opacity .2s; width: 100%; justify-content: center;
    }
    .btn-whatsapp:hover { opacity: 0.85; color: #fff; }

    /* ── MAPA ── */
    .mapa-section { padding: 4rem 0 5rem; background: var(--warm); }
    .mapa-section h4 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.6rem; font-weight: 600;
      color: var(--mocha); margin-bottom: 1.5rem; text-align: center;
    }
    .mapa-section iframe {
      border-radius: var(--radius);
      box-shadow: var(--shadow);
    }

    footer { background: #1e1009; color: rgba(255,255,255,.55); padding: 2.5rem; text-align: center; }
  </style>
</head>
<body>

<div class="stripe"></div>

<?php include __DIR__ . '/../includes/header.php'; ?>


<!-- HERO -->
<div class="page-hero">
  <span class="label">Estamos para ti</span>
  <h1>Contáctanos</h1>
  <div class="rule"></div>
  <p>Queremos endulzar tus momentos más especiales 💕</p>
</div>

<!-- CUERPO -->
<section class="contacto-section">
  <div class="container">
    <div class="row g-5 align-items-start">

      <!-- COLUMNA IZQUIERDA: info -->
      <div class="col-md-4" data-aos="fade-right">

        <div class="info-card">
          <div class="info-icon"><i class="bi bi-geo-alt-fill"></i></div>
          <div>
            <h6>Ubicación</h6>
            <p>Quibdó, Chocó — Colombia</p>
          </div>
        </div>

        <div class="info-card">
          <div class="info-icon"><i class="bi bi-whatsapp"></i></div>
          <div>
            <h6>WhatsApp</h6>
            <p><a href="https://wa.link/37wo38" target="_blank">Escríbenos ahora</a></p>
          </div>
        </div>

        <div class="info-card">
          <div class="info-icon"><i class="bi bi-clock-fill"></i></div>
          <div>
            <h6>Horario</h6>
            <p>Lun – Sáb: 8am – 7pm</p>
          </div>
        </div>

        <div class="info-card">
          <div class="info-icon"><i class="bi bi-instagram"></i></div>
          <div>
            <h6>Redes sociales</h6>
            <p><a href="#" target="_blank">@atratodulce</a></p>
          </div>
        </div>

        <a href="https://wa.link/37wo38" target="_blank" class="btn-whatsapp mt-2">
          <i class="bi bi-whatsapp"></i> Escríbenos por WhatsApp
        </a>

      </div>

      <!-- COLUMNA DERECHA: formulario -->
      <div class="col-md-8" data-aos="fade-left">
        <div class="form-card">
          <h3>Envíanos un mensaje</h3>

          <?php if ($mensaje): ?>
          <div class="alerta alerta-<?= $tipo ?>">
            <?php if ($tipo === 'exito'): ?>
              <i class="bi bi-check-circle-fill"></i>
            <?php elseif ($tipo === 'error'): ?>
              <i class="bi bi-x-circle-fill"></i>
            <?php else: ?>
              <i class="bi bi-exclamation-triangle-fill"></i>
            <?php endif; ?>
            <span><?= $mensaje ?></span>
          </div>
          <?php endif; ?>

          <form method="POST">
            <div class="row g-3">
              <div class="col-sm-6">
                <label class="form-label">Nombre</label>
                <input
                  type="text" name="nombre" class="form-control"
                  placeholder="Tu nombre completo"
                  value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                  required>
              </div>
              <div class="col-sm-6">
                <label class="form-label">Correo electrónico</label>
                <input
                  type="email" name="email" class="form-control"
                  placeholder="tucorreo@email.com"
                  value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                  required>
              </div>
              <div class="col-12">
                <label class="form-label">Mensaje</label>
                <textarea
                  name="mensaje" class="form-control"
                  placeholder="Cuéntanos en qué podemos ayudarte..."
                  required><?= htmlspecialchars($_POST['mensaje'] ?? '') ?></textarea>
              </div>
              <div class="col-12">
                <button type="submit" class="btn-enviar">
                  <i class="bi bi-envelope-fill"></i> Enviar mensaje
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- MAPA -->
<section class="mapa-section">
  <div class="container" data-aos="fade-up">
    <h4>Encuéntranos en Quibdó, Chocó</h4>
    <iframe
      src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3979.847003728983!2d-76.654!3d5.691!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1"
      width="100%" height="320"
      style="border:0;"
      allowfullscreen=""
      loading="lazy">
    </iframe>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({ duration: 800, once: true });</script>
</body>
</html>