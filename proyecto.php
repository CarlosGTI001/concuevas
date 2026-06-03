<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
if ($slug === '') {
    redirect(app_url('proyectos.php'));
}

$stmt = db()->prepare('SELECT id, title, excerpt, description, cover_image_url FROM projects WHERE slug = :slug LIMIT 1');
$stmt->execute(['slug' => $slug]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(404);
    $currentPage = 'proyectos';
    $pageTitle = 'Proyecto no encontrado | ' . site_name();
    require __DIR__ . '/partials/header.php';
    echo '<section><div class="container"><h2>Proyecto no encontrado</h2><p>El proyecto solicitado no existe.</p></div></section>';
    require __DIR__ . '/partials/footer.php';
    exit;
}

$imagesStmt = db()->prepare('SELECT image_url, media_type FROM project_images WHERE project_id = :id ORDER BY sort_order ASC, id ASC');
$imagesStmt->execute(['id' => $project['id']]);
$gallery = $imagesStmt->fetchAll();

$currentPage = 'proyectos';
$pageTitle = (string) $project['title'] . ' | ' . site_name();
require __DIR__ . '/partials/header.php';
?>
<section class="section section bg-soft pb-5 overflow-hidden z-2">
  <div class="container z-2">
    <div class="row justify-content-center text-center pt-6">
      <div class="col-lg-8 col-xl-8">
        <h1 class="display-2 mb-3"><?= e($project['title']) ?></h1>
        <p class="lead px-md-6"><?= e($project['excerpt']) ?></p>
      </div>
    </div>
  </div>
</section>

<section class="section section-lg">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-12 col-md-6 mb-5 mb-md-0">
        <div class="card bg-primary border-light shadow-soft p-3">
          <img src="<?= e($project['cover_image_url']) ?>" class="card-img-top rounded" alt="<?= e($project['title']) ?>">
        </div>
      </div>
      <div class="col-12 col-md-6">
        <h2 class="h3 mb-4">Descripción</h2>
        <div class="lead text-muted project-content">
          <?= $project['description'] ?>
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
        <h2 class="h1">Galería del proyecto</h2>
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
                <img src="<?= e($img['image_url']) ?>" class="card-img-top rounded img-normalized img-ratio-3-2" alt="Imagen de <?= e($project['title']) ?>">
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
