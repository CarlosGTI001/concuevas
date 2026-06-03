<!-- 2. Featured Services Carousel -->
<section class="section section-lg overflow-hidden">
  <div class="container">
    <div class="row">
      <div class="col-12 text-center mb-5">
        <h2 class="h1">Servicios Destacados</h2>
      </div>
    </div>
    
    <div class="position-relative px-md-5">
      <!-- Swiper container -->
      <div class="swiper services-swiper pb-5 px-3">
        <div class="swiper-wrapper">
          <?php foreach ($services as $service): ?>
            <div class="swiper-slide h-auto">
              <div class="card bg-primary border-light shadow-soft h-100 m-2">
                <?php if ($service['image_url']): ?>
                  <img src="<?= e($service['image_url']) ?>" class="card-img-top rounded-top img-normalized img-ratio-4-3" alt="<?= e($service['name']) ?>">
                <?php else: ?>
                  <img src="https://placehold.co/600x400?text=Servicio" class="card-img-top rounded-top img-normalized img-ratio-4-3" alt="<?= e($service['name']) ?>">
                <?php endif; ?>
                <div class="card-body">
                  <h3 class="h5 card-title mt-3"><?= e($service['name']) ?></h3>
                  <p class="card-text small text-muted"><?= e($service['short_description']) ?></p>
                  <div class="mt-3">
                    <a href="<?= e(app_url('servicio.php?id=' . (int) $service['id'])) ?>" class="btn btn-primary btn-sm shadow-soft">Ver más</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Add Pagination -->
        <div class="swiper-pagination"></div>
      </div>

      <!-- Add Navigation Buttons -->
      <div class="swiper-button-prev swiper-btn-outer shadow-soft rounded-circle"></div>
      <div class="swiper-button-next swiper-btn-outer shadow-soft rounded-circle"></div>
    </div>
  </div>
</section>

<style>
/* Swiper Custom Navigation Styles (Neumorphism) */
.swiper-btn-outer {
    background-color: #e6e7ee; /* Same as bg-primary/bg-soft */
    color: #44476a !important;
    width: 50px !important;
    height: 50px !important;
    border: 1px solid #d1d9e6;
    transition: all 0.2s ease;
}
.swiper-btn-outer::after {
    font-size: 1.2rem !important;
    font-weight: bold;
}
.swiper-btn-outer:hover {
    box-shadow: inset 2px 2px 5px #b8b9be, inset -3px -3px 7px #ffffff !important;
}
.swiper-button-prev.swiper-btn-outer {
    left: -10px !important;
}
.swiper-button-next.swiper-btn-outer {
    right: -10px !important;
}

@media (max-width: 768px) {
    .swiper-btn-outer {
        display: none !important; /* Hide buttons on mobile to avoid overflow issues */
    }
}

.swiper-pagination-bullet-active {
    background: #31344b !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new Swiper('.services-swiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: true,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            // when window width is >= 576px
            576: {
                slidesPerView: 2,
                spaceBetween: 30
            },
            // when window width is >= 992px
            992: {
                slidesPerView: 3,
                spaceBetween: 30
            },
            // when window width is >= 1200px
            1200: {
                slidesPerView: 4,
                spaceBetween: 30
            }
        }
    });
});
</script>
