<?php
declare(strict_types=1);
$adminTitle = $adminTitle ?? 'Panel';
$adminSubtitle = $adminSubtitle ?? '';
$currentFile = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));

function get_site_logo(): string
{
    $stmt = db()->prepare('SELECT `value` FROM site_settings WHERE `key` = "site_logo" LIMIT 1');
    $stmt->execute();
    $logo = $stmt->fetchColumn();
    return $logo ?: app_url('assets/img/brand/dark.svg');
}

$navItems = [
    ['file' => 'index.php', 'label' => 'Dashboard', 'icon' => 'fas fa-chart-line'],
    ['file' => 'homepage.php', 'label' => 'Estructura Home', 'icon' => 'fas fa-layer-group'],
    ['file' => 'sliders.php', 'label' => 'Sliders', 'icon' => 'fas fa-images'],
    ['file' => 'values.php', 'label' => 'Valores', 'icon' => 'fas fa-heart'],
    ['file' => 'goals.php', 'label' => 'Metas', 'icon' => 'fas fa-bullseye'],
    ['file' => 'services.php', 'label' => 'Servicios', 'icon' => 'fas fa-tools'],
    ['file' => 'projects.php', 'label' => 'Proyectos', 'icon' => 'fas fa-building'],
    ['file' => 'quotes.php', 'label' => 'Cotizaciones', 'icon' => 'fas fa-file-invoice-dollar'],
    ['file' => 'users.php', 'label' => 'Usuarios', 'icon' => 'fas fa-users-cog'],
    ['file' => 'settings.php', 'label' => 'Ajustes', 'icon' => 'fas fa-cog'],
];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($adminTitle) ?> | Admin</title>

  <!-- Favicon -->
  <link rel="apple-touch-icon" sizes="120x120" href="<?= e(get_site_logo()) ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= e(get_site_logo()) ?>">
  <link rel="icon" type="image/png" sizes="16x16" href="<?= e(get_site_logo()) ?>">

  <!-- Fontawesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <!-- Pixel CSS -->
  <link type="text/css" href="<?= e(app_url('css/neumorphism.css')) ?>?v=<?= APP_VERSION ?>" rel="stylesheet">

  <!-- Summernote CSS -->
  <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">

  <!-- Dropzone & Sortable -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css">

  <link rel="stylesheet" href="<?= e(app_url('styles.css')) ?>?v=<?= APP_VERSION ?>">
</head>
<body class="admin-body bg-soft">
  <!-- Admin Mobile Navbar -->
  <nav class="navbar navbar-dark navbar-theme-primary px-4 d-lg-none shadow-soft border-bottom border-light">
    <a class="navbar-brand mr-lg-5" href="<?= e(app_url('admin/index')) ?>">
        <img src="<?= e(get_site_logo()) ?>" alt="Logo Admin" style="height: 40px;">
    </a>
    <div class="d-flex align-items-center">
        <button class="navbar-toggler ml-2 collapsed" type="button" data-toggle="collapse" data-target="#admin-sidebar-mobile" aria-controls="admin-sidebar-mobile" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
  </nav>

  <div class="admin-shell">
    <aside class="admin-sidebar bg-soft collapse d-lg-block" id="admin-sidebar-mobile">
      <div class="text-center mb-4">
        <a href="<?= e(app_url('admin/index')) ?>" class="d-inline-block shadow-soft p-2 rounded-circle border border-light">
          <img src="<?= e(get_site_logo()) ?>" width="40" height="40" alt="Logo" style="object-fit: contain;">
        </a>
        <h2 class="h6 mt-3 mb-0">CMS Cuevas</h2>
      </div>
      <?php foreach ($navItems as $item): ?>
        <?php
          $isActive = $currentFile === $item['file']
            || ($item['file'] === 'projects.php' && $currentFile === 'project_edit.php');
        ?>
        <a class="nav-link <?= $isActive ? 'active' : '' ?>" href="<?= e(app_url('admin/' . $item['file'])) ?>">
          <span class="<?= $item['icon'] ?> mr-2"></span> <?= e($item['label']) ?>
        </a>
      <?php endforeach; ?>
      <hr class="my-3 border-light shadow-inset">
      <a class="nav-link text-danger" href="<?= e(app_url('admin/logout.php')) ?>">
        <span class="fas fa-sign-out-alt mr-2"></span> Cerrar sesión
      </a>
      <div class="mt-auto pt-4 text-center">
        <p class="small text-muted mb-0" style="font-size: 0.7rem; opacity: 0.6;">Dev: CarlosGTI001 DEV</p>
      </div>
    </aside>
    <main class="admin-main">
      <header class="admin-page-head admin-card mb-4">
        <p class="admin-kicker">Panel Administrador</p>
        <h1><?= e($adminTitle) ?></h1>
        <?php if ($adminSubtitle !== ''): ?><p class="admin-subtitle"><?= e($adminSubtitle) ?></p><?php endif; ?>
      </header>
