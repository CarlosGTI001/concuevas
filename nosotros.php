<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$currentPage = 'nosotros';
$pageTitle = 'Nosotros | ' . site_name();

require __DIR__ . '/partials/header.php';
?>
<!-- 1. Hero Section -->
<section class="section section bg-soft pb-5 overflow-hidden z-2">
  <div class="container z-2">
    <div class="row justify-content-center text-center pt-6">
      <div class="col-lg-8 col-xl-8">
        <h1 class="display-2 mb-3">Quiénes Somos</h1>
        <p class="lead px-md-6"><?= e(setting('about_intro', 'Equipo multidisciplinario enfocado en construir con calidad y cumplimiento.')) ?></p>
      </div>
    </div>
  </div>
</section>

<!-- 2. Essence / Detail Section (History) -->
<section class="section section-lg">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-12 col-lg-6 mb-5 mb-lg-0">
        <div class="card bg-soft border-light shadow-soft p-4 p-lg-5 h-100">
          <h2 class="h3 mb-4">Nuestra esencia</h2>
          <div class="lead text-muted about-content">
            <!-- Content from rich text editor -->
            <?= setting('about_text', 'En Construcciones Cuevas integramos arquitectura, ingeniería y gestión de obra para entregar resultados medibles.') ?>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="card bg-soft border-light shadow-soft p-3">
          <?php 
            $aboutImage = setting('about_image');
            if (empty($aboutImage)) {
                $aboutImage = 'https://placehold.co/780x520?text=Equipo+Cuevas';
            }
          ?>
          <img src="<?= e($aboutImage) ?>" class="card-img-top rounded img-normalized" style="height: 500px;" alt="Equipo de <?= e(site_name()) ?>">
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 3. Objective Section (Now third, after essence) -->
<?php if ($objective = setting('about_objective')): ?>
<section class="section section-lg pt-0">
  <div class="container">
    <div class="card bg-soft border-light shadow-inset-soft p-4 p-lg-5 text-center">
      <h2 class="h3 mb-4">Nuestro Objetivo</h2>
      <div class="lead about-content">
        <?= $objective ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- 4. Values Section -->
<?php
// Fetch dynamic values
$siteValues = db()->query('SELECT * FROM site_values ORDER BY sort_order ASC, id DESC')->fetchAll();

// Fallback if no values registered
if (empty($siteValues)) {
    $siteValues = [
        ['title' => 'Calidad', 'description' => 'Excelencia en cada detalle de la obra y materiales de primera.', 'icon' => 'fas fa-gem'],
        ['title' => 'Confianza', 'description' => 'Cumplimiento estricto de plazos y transparencia en costos.', 'icon' => 'fas fa-handshake'],
        ['title' => 'Compromiso', 'description' => 'Dedicación total hasta la entrega final de cada proyecto.', 'icon' => 'fas fa-bullseye'],
    ];
}
?>
<section class="section section-lg bg-soft">
  <div class="container text-center">
    <h2 class="h1 mb-5">Nuestros Valores</h2>
    <div class="row">
      <?php foreach ($siteValues as $v): ?>
        <div class="col-md-4 mb-5">
          <div class="card bg-soft border-light shadow-soft h-100 p-4">
            <div class="icon-shape icon-shape-primary rounded-circle mb-4">
              <span class="<?= e($v['icon']) ?>"></span>
            </div>
            <h3 class="h5"><?= e($v['title']) ?></h3>
            <p class="text-muted"><?= e($v['description']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 5. Goals Section -->
<?php
$siteGoals = db()->query('SELECT * FROM site_goals ORDER BY sort_order ASC, id DESC')->fetchAll();
?>
<?php if (!empty($siteGoals)): ?>
<section class="section section-lg">
  <div class="container">
    <h2 class="h1 text-center mb-5">Nuestras Metas</h2>
    <div class="row justify-content-center">
      <?php foreach ($siteGoals as $g): ?>
        <div class="col-12 col-lg-10 mb-4">
          <div class="card bg-soft border-light shadow-soft p-4">
             <div class="row align-items-center">
               <div class="col-md-auto mb-3 mb-md-0">
                  <div class="icon-shape icon-shape-primary rounded-circle shadow-inset-soft" style="width: 3rem; height: 3rem;">
                    <span class="fas fa-bullseye"></span>
                  </div>
               </div>
               <div class="col">
                  <h3 class="h5 mb-1"><?= e($g['title']) ?></h3>
                  <p class="mb-0 text-muted"><?= e($g['description']) ?></p>
               </div>
             </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
