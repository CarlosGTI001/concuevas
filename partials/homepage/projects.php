<!-- 5. Recent Projects Carousel -->
<section class="section section-lg bg-soft overflow-hidden">
  <div class="container">
    <div class="row">
      <div class="col-12 text-center mb-5">
        <h2 class="h1">Proyectos Recientes</h2>
      </div>
    </div>

    <div class="position-relative <?= count($projects) > 3 ? 'px-md-5' : '' ?>">
      <div class="swiper projects-swiper pb-5 px-3">
        <div class="swiper-wrapper">
          <?php foreach ($projects as $project): ?>
            <div class="swiper-slide h-auto">
              <div class="card bg-primary border-light shadow-soft h-100 m-2">
                <img src="<?= e($project['cover_image_url']) ?>" class="card-img-top rounded-top img-normalized img-ratio-3-2" alt="<?= e($project['title']) ?>">
                <div class="card-body">
                  <h3 class="h5 card-title mt-3"><?= e($project['title']) ?></h3>
                  <p class="card-text text-truncate small text-muted"><?= e($project['excerpt']) ?></p>
                  <a href="<?= e(app_url('proyecto.php?slug=' . urlencode((string) $project['slug']))) ?>" class="btn btn-primary btn-sm shadow-soft">Ver Proyecto</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <div class="swiper-pagination"></div>
      </div>

      <?php if (count($projects) > 3): ?>
        <!-- Navigation Buttons - Only show if enough items -->
        <div class="swiper-button-prev swiper-btn-outer shadow-soft rounded-circle d-none d-lg-flex"></div>
        <div class="swiper-button-next swiper-btn-outer shadow-soft rounded-circle d-none d-lg-flex"></div>
      <?php endif; ?>
    </div>

    <div class="row mt-4">
        <div class="col-12 text-center">
            <a href="<?= e(app_url('proyectos')) ?>" class="btn btn-primary shadow-soft">Ver todo el portafolio</a>
        </div>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const projectCount = <?= count($projects) ?>;
    
    new Swiper('.projects-swiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        // Solo loop y botones si hay más de 3 proyectos
        loop: projectCount > 3,
        autoplay: projectCount > 3 ? {
            delay: 5000,
            disableOnInteraction: false,
        } : false,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            576: { slidesPerView: 1 },
            768: { slidesPerView: 2 },
            1024: { slidesPerView: 3 }
        }
    });
});
</script>
