<!-- 1. Unified Hero Slider -->
<section class="section py-0 overflow-hidden section-full-width unified-hero-section">
    <div id="CarouselHero" class="carousel slide shadow-soft overflow-hidden" data-ride="carousel">
        <ol class="carousel-indicators">
            <?php foreach ($sliders as $index => $slide): ?>
                <li data-target="#CarouselHero" data-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>"></li>
            <?php endforeach; ?>
        </ol>
        <div class="carousel-inner">
            <?php foreach ($sliders as $index => $slide): ?>
                <?php 
                    $posClass = 'pos-' . ($slide['position'] ?? 'center-center'); 
                    $imgPos = str_replace('-', ' ', ($slide['image_position'] ?? 'center-center'));
                ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <div class="hero-overlay"></div>
                    <img src="<?= e($slide['image_url']) ?>" 
                         class="d-block w-100 img-normalized img-ratio-hero" 
                         style="object-position: <?= $imgPos ?>;"
                         alt="Slide <?= $index + 1 ?>">
                    
                    <div class="carousel-caption <?= $posClass ?>">
                        <h1 class="display-2 text-white mb-3 shadow-text"><?= e($slide['title']) ?></h1>
                        <p class="lead text-white px-md-6 mb-5 shadow-text font-weight-bold"><?= e($slide['text']) ?></p>
                        <a class="btn btn-primary mb-3 shadow-soft" href="<?= e(app_url('cotizacion')) ?>">Solicitar Cotización</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <a class="carousel-control-prev" href="#CarouselHero" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Anterior</span>
        </a>
        <a class="carousel-control-next" href="#CarouselHero" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Siguiente</span>
        </a>
    </div>
</section>

<!-- Mobile Hero Content -->
<div class="d-block d-md-none bg-soft p-5 text-center border-bottom border-light shadow-soft">
    <h1 class="h3 mb-3"><?= e($sliders[0]['title']) ?></h1>
    <p class="small mb-4"><?= e($sliders[0]['text']) ?></p>
    <a class="btn btn-primary btn-sm" href="<?= e(app_url('cotizacion')) ?>">Solicitar Cotización</a>
</div>
