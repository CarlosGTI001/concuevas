<?php
declare(strict_types=1);

$currentPage = $currentPage ?? '';
$pageTitle = $pageTitle ?? site_name() . ' | ' . site_tagline();

function get_site_logo(): string
{
    $stmt = db()->prepare('SELECT `value` FROM site_settings WHERE `key` = "site_logo" LIMIT 1');
    $stmt->execute();
    $logo = $stmt->fetchColumn();
    return $logo ?: app_url('assets/img/brand/dark.svg');
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e(setting('meta_description', 'Sitio web de Construcciones Cuevas.')) ?>">

  <!-- Favicon -->
  <link rel="apple-touch-icon" sizes="120x120" href="<?= e(get_site_logo()) ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= e(get_site_logo()) ?>">
  <link rel="icon" type="image/png" sizes="16x16" href="<?= e(get_site_logo()) ?>">
  <link rel="mask-icon" href="<?= e(get_site_logo()) ?>" color="#ffffff">
  <meta name="msapplication-TileColor" content="#ffffff">
  <meta name="theme-color" content="#ffffff">

  <!-- Fontawesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

  <!-- Pixel CSS -->
  <link type="text/css" href="<?= e(app_url('css/neumorphism.css')) ?>?v=<?= APP_VERSION ?>" rel="stylesheet">

  <!-- Project CSS -->
  <link rel="stylesheet" href="<?= e(app_url('styles.css')) ?>?v=<?= APP_VERSION ?>">
</head>
<body>
  <header class="header-global">
    <nav id="navbar-main" aria-label="Primary navigation" class="navbar navbar-main navbar-expand-lg navbar-light">
      <div class="container position-relative">
        <a class="navbar-brand shadow-soft py-2 px-3 rounded border border-light mr-lg-4" href="<?= e(app_url('index.php')) ?>">
          <img src="<?= e(get_site_logo()) ?>" height="60" alt="Logo">
        </a>
        <div class="navbar-collapse collapse" id="navbar_global">
          <div class="navbar-collapse-header">
            <div class="row">
              <div class="col-6 collapse-brand">
                <a href="<?= e(app_url('index')) ?>" class="navbar-brand shadow-soft py-2 px-3 rounded border border-light">
                  <img src="<?= e(get_site_logo()) ?>" alt="Logo">
                </a>
              </div>
              <div class="col-6 collapse-close">
                <a href="#navbar_global" class="fas fa-times" data-toggle="collapse" data-target="#navbar_global" aria-controls="navbar_global" aria-expanded="false" title="close" aria-label="Toggle navigation"></a>
              </div>
            </div>
          </div>
          <ul class="navbar-nav navbar-nav-hover align-items-lg-center">
            <li class="nav-item">
              <a class="nav-link <?= $currentPage === 'inicio' ? 'active' : '' ?>" href="<?= e(app_url('index.php')) ?>">Inicio</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $currentPage === 'servicios' ? 'active' : '' ?>" href="<?= e(app_url('servicios.php')) ?>">Servicios</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $currentPage === 'proyectos' ? 'active' : '' ?>" href="<?= e(app_url('proyectos.php')) ?>">Proyectos</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $currentPage === 'nosotros' ? 'active' : '' ?>" href="<?= e(app_url('nosotros.php')) ?>">Nosotros</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $currentPage === 'contacto' ? 'active' : '' ?>" href="<?= e(app_url('contacto.php')) ?>">Contacto</a>
            </li>
          </ul>
        </div>
        <div class="d-flex align-items-center">
          <a href="<?= e(app_url('cotizacion.php')) ?>" class="btn btn-primary"><i class="fas fa-book"></i> Cotización</a>
          <button class="navbar-toggler ml-2" type="button" data-toggle="collapse" data-target="#navbar_global" aria-controls="navbar_global" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
        </div>
      </div>
    </nav>
  </header>
  <main>
