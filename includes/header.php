<?php
// session_start() ya fue llamado en db.php — NO repetir aquí
?>

<header class="main-header shadow-sm sticky-top bg-white">

  <div class="container d-flex align-items-center justify-content-between py-2">

    <!-- LOGO -->
    <div class="d-flex align-items-center">
      <a href="index.php" class="d-flex align-items-center text-decoration-none">

        <img src="assets/img/logo-atratodulce-transparente.png"
             alt="Atrato Dulce"
             class="logo me-2"
             style="height:60px;">

        <div class="d-flex flex-column lh-1">
          <h1 class="m-0 fs-4 fw-bold titulo-logo">
            Atrato <span class="logo-dulce">Dulce</span>
          </h1>
          <small class="text-muted fst-italic" style="font-size:0.85rem;">
            Horneamos felicidad 🍰
          </small>
        </div>

      </a>
    </div>

    <!-- MENÚ -->
    <nav class="d-none d-md-flex align-items-center">
      <a href="index.php"    class="nav-link px-3 fw-semibold menu-link">Inicio</a>
      <a href="catalogo.php" class="nav-link px-3 fw-semibold menu-link">Catálogo</a>
      <a href="nosotros.php" class="nav-link px-3 fw-semibold menu-link">Nosotros</a>
      <a href="contacto.php" class="nav-link px-3 fw-semibold menu-link">Contáctanos</a>
    </nav>

    <!-- LOGIN -->
    <div class="d-flex align-items-center gap-2">
      <?php if (!isset($_SESSION['usuario_id'])): ?>
        <a href="login.php" class="btn btn-login btn-sm rounded-pill px-3">
          <i class="bi bi-person"></i> Iniciar Sesión
        </a>
      <?php else: ?>
        <a href="logout.php" class="btn btn-cerrar btn-sm rounded-pill px-3">
          <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
        </a>
      <?php endif; ?>
    </div>

    <!-- MENÚ MÓVIL -->
    <button class="btn menu-mobile d-md-none border-0"
            data-bs-toggle="offcanvas"
            data-bs-target="#mobileMenu">
      <i class="bi bi-list fs-3"></i>
    </button>

  </div>

  <!-- OFFCANVAS -->
  <div class="offcanvas offcanvas-end text-bg-light" id="mobileMenu">

    <div class="offcanvas-header border-bottom">
      <h5 class="fw-bold">Atrato Dulce 🍰</h5>
      <button class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">
      <a href="index.php"    class="nav-link mb-2">Inicio</a>
      <a href="catalogo.php" class="nav-link mb-2">Catálogo</a>
      <a href="nosotros.php" class="nav-link mb-2">Nosotros</a>
      <a href="contacto.php" class="nav-link mb-3">Contáctanos</a>

      <?php if (!isset($_SESSION['usuario_id'])): ?>
        <a href="login.php" class="btn btn-login w-100 rounded-pill">Iniciar Sesión</a>
      <?php else: ?>
        <a href="logout.php" class="btn btn-cerrar w-100 rounded-pill">Cerrar Sesión</a>
      <?php endif; ?>
    </div>

  </div>

</header>