<!-- 3. About Us Preview -->
<section class="section section-lg">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-12 col-lg-6 mb-5 mb-lg-0">
        <h2 class="h1 mb-4">Quiénes Somos</h2>
        <p class="lead text-muted mb-4"><?= e(setting('about_intro')) ?></p>
        <div class="about-content text-dark mb-4">
          <?php 
            $fullAbout = setting('about_text');
            // Remove HTML tags for the preview and truncate
            $previewAbout = strip_tags($fullAbout);
            if (strlen($previewAbout) > 350) {
                $previewAbout = substr($previewAbout, 0, 350) . '...';
            }
            echo nl2br(e($previewAbout));
          ?>
        </div>
        <a href="<?= e(app_url('nosotros')) ?>" class="btn btn-primary shadow-soft">
          <span class="fas fa-plus mr-2"></span> Leer más sobre nosotros
        </a>
      </div>
      <div class="col-12 col-lg-6">
        <div class="card bg-soft border-light shadow-soft p-3">
          <?php 
            $aboutImage = setting('about_image');
            if (empty($aboutImage)) {
                $aboutImage = 'https://placehold.co/780x520?text=Equipo+Cuevas';
            }
          ?>
          <img src="<?= e($aboutImage) ?>" class="card-img-top rounded img-normalized img-ratio-3-2" alt="Nuestra Empresa">
        </div>
      </div>
    </div>
  </div>
</section>
