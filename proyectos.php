<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$currentPage = 'proyectos';
$pageTitle = 'Proyectos | ' . site_name();
$projects = db()->query('SELECT id, title, slug, excerpt, cover_image_url FROM projects ORDER BY created_at DESC')->fetchAll();

require __DIR__ . '/partials/header.php';
?>
<section class="section section bg-soft pb-5 overflow-hidden z-2">
  <div class="container z-2">
    <div class="row justify-content-center text-center pt-6">
      <div class="col-lg-8 col-xl-8">
        <h1 class="display-2 mb-3">Portafolio de Proyectos</h1>
        <p class="lead px-md-6">Proyectos que transforman ideas en realidad.</p>
      </div>
    </div>
  </div>
</section>

<section class="section section-lg">
  <div class="container">
    <div class="row">
      <?php foreach ($projects as $project): ?>
        <div class="col-12 col-md-6 col-lg-4 mb-5">
          <div class="card bg-primary border-light shadow-soft h-100">
            <img src="<?= e($project['cover_image_url']) ?>" class="card-img-top rounded-top" alt="<?= e($project['title']) ?>">
            <div class="card-body">
              <h3 class="h5 card-title mt-3"><?= e($project['title']) ?></h3>
              <p class="card-text"><?= e($project['excerpt']) ?></p>
              <a href="<?= e(app_url('proyecto.php?slug=' . urlencode((string) $project['slug']))) ?>" class="btn btn-primary btn-sm">Ver Proyecto</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
