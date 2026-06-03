<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$currentPage = 'servicios';
$pageTitle = 'Servicios | ' . site_name();
$services = db()->query('SELECT id, name, short_description, long_description, image_url FROM services ORDER BY sort_order ASC, id DESC')->fetchAll();

require __DIR__ . '/partials/header.php';
?>
<section class="section section bg-soft pb-5 overflow-hidden z-2">
  <div class="container z-2">
    <div class="row justify-content-center text-center pt-6">
      <div class="col-lg-8 col-xl-8">
        <h1 class="display-2 mb-3">Nuestros Servicios</h1>
        <p class="lead px-md-6">Soluciones integrales de Construcción & Arquitectura.</p>
      </div>
    </div>
  </div>
</section>

<section class="section section-lg">
  <div class="container">
    <div class="row">
      <?php foreach ($services as $service): ?>
        <div class="col-12 col-md-6 mb-5">
          <div class="card bg-primary border-light shadow-soft h-100">
            <?php if ($service['image_url']): ?>
              <img src="<?= e($service['image_url']) ?>" class="card-img-top rounded-top" style="height: 250px; object-fit: cover;" alt="<?= e($service['name']) ?>">
            <?php else: ?>
              <img src="https://placehold.co/600x400?text=Servicio" class="card-img-top rounded-top" style="height: 250px; object-fit: cover;" alt="<?= e($service['name']) ?>">
            <?php endif; ?>
            <div class="card-body">
              <h3 class="h5 card-title mt-3"><?= e($service['name']) ?></h3>
              <p class="card-text text-muted mb-3"><?= e($service['short_description']) ?></p>
              <div class="mt-4">
                <a href="<?= e(app_url('servicio.php?id=' . (int) $service['id'])) ?>" class="btn btn-primary btn-sm">Más información</a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
