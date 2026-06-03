<?php 
$ctaBg = setting('cta_background_image');
if (empty($ctaBg)) {
    $ctaBg = 'https://images.unsplash.com/photo-1541888946425-d81bb19480c5?auto=format&fit=crop&w=1920&q=80';
}
?>
<!-- Call to Action Section with Background Image and Glass Effect -->
<section class="section section-lg position-relative shadow-inset-soft" style="background-image: url('<?= e($ctaBg) ?>'); background-size: cover; background-position: center; background-attachment: fixed;">
    <!-- Overlay for better contrast -->
    <div class="position-absolute w-100 h-100 top-0 left-0" style="background: rgba(49, 52, 75, 0.4); z-index: 1;"></div>
    
    <div class="container position-relative" style="z-index: 2;">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="card cta-glass-card p-4 p-md-5 text-center">
                    <h2 class="display-3 text-white mb-3 shadow-text">¿Tienes un proyecto en mente?</h2>
                    <p class="lead text-white mb-5 shadow-text font-weight-bold">
                        Deja que nuestro equipo de expertos se encargue de todo. Desde la planificación hasta la entrega final, garantizamos calidad, cumplimiento y excelencia técnica en cada detalle.
                    </p>
                    <div class="d-flex justify-content-center">
                        <a href="<?= e(app_url('cotizacion.php')) ?>" class="btn btn-primary btn-lg shadow-soft animate-up-2 py-3 px-5">
                            <span class="fas fa-file-invoice-dollar mr-2"></span> Cotiza con nosotros ahora
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Modern Glassmorphism Effect */
.cta-glass-card {
    background: rgba(230, 231, 238, 0.25) !important; /* Translucent version of bg-primary */
    backdrop-filter: blur(15px) saturate(150%);
    -webkit-backdrop-filter: blur(15px) saturate(150%);
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    border-radius: 1.5rem !important;
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37) !important;
}

.shadow-text {
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

/* Ensure the background attachment fixed works well on mobile */
@media (max-width: 1024px) {
    .section {
        background-attachment: scroll;
    }
}
</style>
