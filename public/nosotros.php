<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nosotros | Atrato Dulce 🍰</title>

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
      background: var(--mocha);
      color: #fff;
      text-align: center;
      padding: 1.1rem;
      font-size: 14px;
    }

    /* ── HERO ── */
    .page-hero {
      background: var(--warm);
      padding: 4rem 0 3rem;
      text-align: center;
    }
    .page-hero .label {
      font-size: .72rem;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: var(--caramel);
      display: block;
      margin-bottom: 0.5rem;
    }
    .page-hero h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: clamp(2rem, 5vw, 3rem);
      font-weight: 600;
      color: var(--mocha);
      margin-bottom: 0.5rem;
    }
    .page-hero p { color: var(--muted); font-size: 15px; margin: 0; }
    .rule {
      width: 48px; height: 2px;
      background: linear-gradient(90deg, var(--caramel), var(--rose));
      margin: 0.75rem auto;
    }

    /* ── HISTORIA ── */
    .historia-section { padding: 5rem 0; }
    .historia-section h2 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2rem; font-weight: 600;
      color: var(--mocha); margin-bottom: 1rem;
    }
    .historia-section p { color: var(--muted); line-height: 1.8; margin-bottom: 1rem; }
    .historia-section img { border-radius: var(--radius); box-shadow: var(--shadow); }

    /* ── VALORES ── */
    .valores-section {
      padding: 4rem 0;
      background: var(--warm);
    }
    .section-header { text-align: center; margin-bottom: 3rem; }
    .section-header .label {
      font-size: .72rem; letter-spacing: 3px;
      text-transform: uppercase; color: var(--caramel);
    }
    .section-header h2 {
      font-family: 'Cormorant Garamond', serif;
      font-size: clamp(1.6rem, 4vw, 2.2rem);
      font-weight: 600; color: var(--mocha);
    }
    .valor-card {
      background: #fff;
      border-radius: var(--radius);
      padding: 2rem 1.5rem;
      text-align: center;
      transition: .3s;
      height: 100%;
      box-shadow: 0 2px 16px rgba(59,35,20,.06);
    }
    .valor-card:hover { transform: translateY(-6px); box-shadow: var(--shadow); }
    .valor-icon {
      font-size: 2rem; color: var(--caramel);
      margin-bottom: 1rem;
    }
    .valor-card h5 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.2rem; font-weight: 600;
      color: var(--mocha); margin-bottom: 0.5rem;
    }
    .valor-card p { color: var(--muted); font-size: 13.5px; margin: 0; }

    /* ── EQUIPO ── */
    .equipo-section { padding: 5rem 0; }
    .team-card {
      background: #fff;
      border-radius: var(--radius);
      padding: 2rem 1.5rem;
      text-align: center;
      transition: .3s;
      box-shadow: 0 2px 16px rgba(59,35,20,.06);
    }
    .team-card:hover { transform: translateY(-6px); box-shadow: var(--shadow); }
    .team-card img {
      width: 140px; height: 140px;
      object-fit: cover;
      border-radius: 50%;
      border: 4px solid var(--warm);
      margin-bottom: 1rem;
    }
    .team-card h5 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.2rem; font-weight: 600;
      color: var(--mocha); margin-bottom: 4px;
    }
    .team-card .rol { font-size: 13px; color: var(--caramel); }

    /* ── CTA FINAL ── */
    .cta-section {
      background: var(--mocha);
      color: #fff;
      text-align: center;
      padding: 5rem 0;
    }
    .cta-section h2 {
      font-family: 'Cormorant Garamond', serif;
      font-size: clamp(1.6rem, 4vw, 2.4rem);
      font-weight: 600; margin-bottom: 1rem;
    }
    .cta-section p { color: rgba(255,255,255,.7); margin-bottom: 2rem; }
    .btn-cta-light {
      background: var(--gold); color: var(--mocha);
      border: none; border-radius: 50px;
      padding: 0.7rem 2rem; font-size: 14px; font-weight: 500;
      text-decoration: none; transition: opacity .2s;
      display: inline-flex; align-items: center; gap: 8px;
    }
    .btn-cta-light:hover { opacity: 0.85; color: var(--mocha); }

    footer { background: #1e1009; color: rgba(255,255,255,.55); padding: 2.5rem; text-align: center; }
  </style>
</head>
<body>

<div class="stripe"></div>

<?php include __DIR__ . '/../includes/header.php'; ?>



<!-- HERO -->
<div class="page-hero">
  <span class="label">Conoce nuestra historia</span>
  <h1>Sobre Nosotros</h1>
  <div class="rule"></div>
  <p>Descubre la historia detrás de cada dulce momento 💕</p>
</div>

<!-- HISTORIA -->
<section class="historia-section">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-md-6" data-aos="fade-right">
        <img
          src="assets/img/logo-atratodulce-transparente.png"
          alt="Historia Atrato Dulce"
          class="img-fluid w-100">
      </div>
      <div class="col-md-6" data-aos="fade-left">
        <span style="font-size:.72rem;letter-spacing:3px;text-transform:uppercase;color:var(--caramel);">Quiénes somos</span>
        <h2 class="mt-1">Nuestra Historia</h2>
        <div class="rule" style="margin:0.75rem 0;"></div>
        <p>
          Nacimos en el corazón del Chocó, donde los sabores se mezclan con la alegría y la tradición.
          Desde nuestros inicios, en <strong>Atrato Dulce</strong> nos propusimos crear postres que
          transmitieran amor, unión y felicidad en cada bocado.
        </p>
        <p>
          Cada torta, pastel o cupcake que sale de nuestro horno está elaborado con ingredientes frescos,
          manos talentosas y el toque único de la dulzura chocoana.
        </p>
        <a href="catalogo.php" class="btn btn-cta-light mt-2">
          <i class="bi bi-shop"></i> Ver nuestros productos
        </a>
      </div>
    </div>
  </div>
</section>

<!-- VALORES -->
<section class="valores-section">
  <div class="container">
    <div class="section-header" data-aos="fade-up">
      <span class="label">Lo que nos mueve</span>
      <h2>Nuestros valores</h2>
      <div class="rule"></div>
    </div>
    <div class="row g-4 justify-content-center">
      <div class="col-sm-6 col-lg-3" data-aos="fade-up">
        <div class="valor-card">
          <div class="valor-icon"><i class="bi bi-heart-fill"></i></div>
          <h5>Hecho con amor</h5>
          <p>Cada producto lleva el cariño y la pasión de nuestro equipo en cada detalle.</p>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
        <div class="valor-card">
          <div class="valor-icon"><i class="bi bi-leaf"></i></div>
          <h5>Ingredientes frescos</h5>
          <p>Usamos ingredientes de calidad seleccionados con cuidado para garantizar el mejor sabor.</p>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
        <div class="valor-card">
          <div class="valor-icon"><i class="bi bi-people-fill"></i></div>
          <h5>Comunidad</h5>
          <p>Somos parte de Quibdó y apoyamos el desarrollo de nuestra región con cada venta.</p>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
        <div class="valor-card">
          <div class="valor-icon"><i class="bi bi-star-fill"></i></div>
          <h5>Calidad garantizada</h5>
          <p>Cada orden pasa por un control de calidad antes de llegar a tus manos.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- EQUIPO -->
<section class="equipo-section">
  <div class="container">
    <div class="section-header" data-aos="fade-up">
      <span class="label">Las personas detrás</span>
      <h2>Nuestro Equipo</h2>
      <div class="rule"></div>
    </div>
    <div class="row g-4 justify-content-center">
      <div class="col-md-4" data-aos="zoom-in">
        <div class="team-card">
          <img src="assets/img/neyder.png" alt="Chef principal">
          <h5>Neyder Yesid</h5>
          <span class="rol">Chef pastelero y fundador</span>
        </div>
      </div>
      <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
        <div class="team-card">
          <img src="assets/img/neyder.png" alt="Maestro repostero">
          <h5>Neyder Yesid</h5>
          <span class="rol">Maestro repostero</span>
        </div>
      </div>
      <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
        <div class="team-card">
          <img src="assets/img/neyder.png" alt="Decoradora">
          <h5>Neyder Yesid</h5>
          <span class="rol">Decoradora y creadora de detalles</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="cta-section">
  <div class="container" data-aos="fade-up">
    <h2>✨ Dulzura con amor desde el Chocó ✨</h2>
    <p>Llevamos el sabor de nuestra tierra a cada celebración.</p>
    <a href="https://wa.link/37wo38" target="_blank" class="btn-cta-light">
      <i class="bi bi-whatsapp"></i> Contáctanos por WhatsApp
    </a>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({ duration: 800, once: true });</script>
</body>
</html>