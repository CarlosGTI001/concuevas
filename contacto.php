<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$currentPage = 'contacto';
$pageTitle = 'Contacto | ' . site_name();

$mapIframe = setting('contact_map_iframe');

require __DIR__ . '/partials/header.php';
?>
<section class="section section bg-soft pb-5 overflow-hidden z-2">
  <div class="container z-2">
    <div class="row justify-content-center text-center pt-6">
      <div class="col-lg-8 col-xl-8">
        <h1 class="display-2 mb-3">Contacto</h1>
        <p class="lead px-md-6">Estamos listos para ayudarte con tu próximo proyecto.</p>
      </div>
    </div>
  </div>
</section>

<section class="section section-lg">
  <div class="container">
    <div class="row">
      <div class="col-12 col-md-6 mb-5 mb-md-0">
        <div class="card bg-soft border-light shadow-soft p-4 h-100">
          <h2 class="h3 mb-4">Datos de contacto</h2>
          <p class="mb-3 text-dark"><strong><i class="fas fa-phone mr-2"></i> Teléfono:</strong> <?= e(setting('contact_phone', '+52 000 000 0000')) ?></p>
          <p class="mb-3 text-dark"><strong><i class="fas fa-envelope mr-2"></i> Email:</strong> <?= e(setting('contact_email', 'contacto@construccionescuevas.com')) ?></p>
          <p class="mb-3 text-dark"><strong><i class="fas fa-map-marker-alt mr-2"></i> Dirección:</strong> <?= e(setting('contact_address', 'Av. Principal 123, Zona Centro')) ?></p>
          <div class="mt-4">
            <a class="btn btn-primary shadow-soft" href="<?= e(app_url('cotizacion')) ?>">Solicitar cotización</a>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="card bg-soft border-light shadow-soft p-3 h-100 overflow-hidden">
          <?php if ($mapIframe): ?>
            <div class="embed-responsive embed-responsive-16by9 h-100" style="min-height: 350px;">
                <!-- We output the iframe directly as it's trusted HTML from the admin -->
                <?= $mapIframe ?>
            </div>
          <?php else: ?>
            <img src="<?= e(setting('contact_image', 'https://placehold.co/760x520')) ?>" class="card-img-top rounded h-100 img-normalized" style="object-fit: cover;" alt="Oficina de <?= e(site_name()) ?>">
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
