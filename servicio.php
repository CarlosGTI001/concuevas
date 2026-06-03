<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect(app_url('servicios.php'));
}

$stmt = db()->prepare('SELECT * FROM services WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$service = $stmt->fetch();

if (!$service) {
    redirect(app_url('servicios.php'));
}

$imagesStmt = db()->prepare('SELECT image_url, media_type FROM service_images WHERE service_id = :id ORDER BY sort_order ASC, id ASC');
$imagesStmt->execute(['id' => $service['id']]);
$gallery = $imagesStmt->fetchAll();

$pageTitle = e($service['name']) . ' | ' . site_name();
require __DIR__ . '/partials/header.php';
?>
<section class="section section bg-soft pb-5 overflow-hidden z-2">
  <div class="container z-2">
    <div class="row justify-content-center text-center pt-6">
      <div class="col-lg-8 col-xl-8">
        <h1 class="display-2 mb-3"><?= e($service['name']) ?></h1>
        <p class="lead px-md-6"><?= e($service['short_description']) ?></p>
      </div>
    </div>
  </div>
</section>

<section class="section section-lg">
  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-8 mx-auto">
        <?php if ($service['image_url']): ?>
          <div class="card bg-primary border-light shadow-soft p-3 mb-5">
            <img src="<?= e($service['image_url']) ?>" class="card-img-top rounded img-normalized img-ratio-16-9" alt="<?= e($service['name']) ?>">
          </div>
        <?php endif; ?>
        
        <div class="card bg-primary border-light shadow-soft p-4 p-lg-5">
          <div class="service-content text-dark">
            <!-- Long description can contain HTML from rich text editor -->
            <?= $service['long_description'] ?>
          </div>
          
          <div class="mt-5 text-center">
            <a href="<?= e(app_url('servicios.php')) ?>" class="btn btn-primary mr-3"><i class="fas fa-arrow-left mr-2"></i> Volver a servicios</a>
            <a href="<?= e(app_url('cotizacion.php')) ?>" class="btn btn-primary"><i class="fas fa-envelope mr-2"></i> Solicitar cotización</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if ($gallery): ?>
<section class="section section-lg bg-soft">
  <div class="container">
    <div class="row">
      <div class="col-12 text-center mb-5">
        <h2 class="h1">Galería del servicio</h2>
      </div>
    </div>
    <div class="row">
      <?php foreach ($gallery as $index => $img): ?>
        <div class="col-12 col-md-6 col-lg-4 mb-5">
          <div class="card bg-primary border-light shadow-soft p-2 js-gallery-item" data-index="<?= $index ?>" style="cursor: pointer; position: relative;">
            <?php if (($img['media_type'] ?? 'image') === 'video'): ?>
                <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); color:rgba(255,255,255,0.8); font-size:3rem; pointer-events:none; z-index:2;">
                    <i class="fas fa-play-circle"></i>
                </div>
                <video src="<?= e($img['image_url']) ?>" class="card-img-top rounded img-normalized img-ratio-3-2" style="object-fit: cover;" muted></video>
            <?php else: ?>
                <img src="<?= e($img['image_url']) ?>" class="card-img-top rounded img-normalized img-ratio-3-2" alt="Imagen de <?= e($service['name']) ?>">
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Lightbox Modal (Cinematic Version) -->
<div class="modal fade lightbox-custom-modal" id="modal-lightbox" tabindex="-1" role="dialog" aria-hidden="true">
    <button type="button" class="lightbox-close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div id="CarouselLightbox" class="carousel slide" data-ride="carousel" data-interval="false">
                    <div class="carousel-inner">
                        <?php foreach ($gallery as $index => $img): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <div class="lightbox-image-container">
                                    <?php if (($img['media_type'] ?? 'image') === 'video'): ?>
                                        <video src="<?= e($img['image_url']) ?>" class="d-block shadow-lg rounded" controls style="max-height: 80vh; max-width: 100%;"></video>
                                    <?php else: ?>
                                        <img src="<?= e($img['image_url']) ?>" class="d-block shadow-lg rounded" alt="Full Image">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a class="carousel-control-prev" href="#CarouselLightbox" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Anterior</span>
                    </a>
                    <a class="carousel-control-next" href="#CarouselLightbox" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Siguiente</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const galleryItems = document.querySelectorAll('.js-gallery-item');
    const lightboxCarousel = document.getElementById('CarouselLightbox');
    
    galleryItems.forEach(item => {
        item.addEventListener('click', function() {
            const index = this.getAttribute('data-index');
            $('#CarouselLightbox').carousel(parseInt(index));
            $('#modal-lightbox').modal('show');
            
            // Auto play if it's a video in the newly selected slide
            playVideoIfPresent(document.querySelector('.carousel-item.active'));
        });
    });

    // Custom backdrop behavior
    $('#modal-lightbox').on('show.bs.modal', function() {
        setTimeout(() => {
            $('.modal-backdrop').addClass('lightbox-backdrop-custom');
        }, 0);
    });

    // Handle video playback logic on slide change
    $('#CarouselLightbox').on('slide.bs.carousel', function (e) {
        // Pause current video if any
        const currentVideo = e.from ? document.querySelectorAll('.carousel-item')[e.from].querySelector('video') : null;
        if (currentVideo) currentVideo.pause();
    });
    
    $('#CarouselLightbox').on('slid.bs.carousel', function (e) {
        // Try to play new video if any
        playVideoIfPresent(e.relatedTarget);
    });

    // Pause all videos when modal closes
    $('#modal-lightbox').on('hide.bs.modal', function() {
        const videos = lightboxCarousel.querySelectorAll('video');
        videos.forEach(v => v.pause());
    });

    function playVideoIfPresent(slideElement) {
        if (!slideElement) return;
        const video = slideElement.querySelector('video');
        if (video) {
            // Attempt to play, catch potential auto-play restrictions
            const playPromise = video.play();
            if (playPromise !== undefined) {
                playPromise.catch(error => {
                    console.log("Auto-play prevented", error);
                });
            }
        }
    }
});
</script>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
