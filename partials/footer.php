<?php
declare(strict_types=1);
?>
  </main>

  <footer class="d-flex pb-5 pt-6 pt-md-7 border-top border-light bg-primary">
    <div class="container">
      <div class="row align-items-center">
        <!-- Brand and Info Column -->
        <div class="col-lg-4 col-md-6 mb-5 mb-lg-0">
          <p class="h5 mb-2"><strong><?= e(site_name()) ?></strong></p>
          <p class="text-muted mb-4"><?= e(site_tagline()) ?></p>
          <ul class="d-flex list-unstyled mb-0">
            <li class="mr-2">
              <a href="#" class="btn btn-icon-only btn-pill btn-primary" aria-label="twitter social link">
                <span aria-hidden="true" class="fab fa-twitter"></span>
              </a>
            </li>
            <li class="mr-2">
              <a href="#" class="btn btn-icon-only btn-pill btn-primary" aria-label="facebook social link">
                <span aria-hidden="true" class="fab fa-facebook"></span>
              </a>
            </li>
          </ul>
        </div>

        <!-- Links Column -->
        <div class="col-lg-3 col-6 col-md-6 mb-5 mb-lg-0">
          <h5>Enlaces</h5>
          <ul class="footer-links list-unstyled mt-2">
            <li class="mb-1"><a class="p-2" href="<?= e(app_url('index')) ?>">Inicio</a></li>
            <li class="mb-1"><a class="p-2" href="<?= e(app_url('servicios')) ?>">Servicios</a></li>
            <li class="mb-1"><a class="p-2" href="<?= e(app_url('proyectos')) ?>">Proyectos</a></li>
          </ul>
        </div>

        <!-- Dynamic Logo Column (Right on Desktop, Bottom on Mobile) -->
        <div class="col-lg-5 col-12 text-center text-lg-right mt-4 mt-lg-0">
          <?php if ($footerIcon = setting('footer_icon')): ?>
            <div class="footer-logo-wrapper d-inline-block">
              <img src="<?= e($footerIcon) ?>" style="width: 100%; max-width: 250px; height: auto; object-fit: contain; border: none;" alt="Footer Logo">
            </div>
          <?php endif; ?>
        </div>
      </div>

      <hr class="my-5">
      
      <div class="row">
        <div class="col">
          <div class="d-flex text-center justify-content-center align-items-center" role="contentinfo">
            <p class="font-weight-normal font-small mb-0 text-muted">
              Copyright © <?= e(site_name()) ?> <span class="current-year"><?= date('Y') ?></span>. 
              Programado por <span class="font-weight-bold">CarlosGTI001 DEV</span>. 
              Todos los derechos reservados.
            </p>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Core Scripts -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/headroom.js@0.11.0/dist/headroom.min.js"></script>

  <!-- Vendor JS -->
  <script src="https://cdn.jsdelivr.net/npm/on-screen@1.3.4/dist/on-screen.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/nouislider@14.6.3/distribute/nouislider.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jarallax@1.12.7/dist/jarallax.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery.counterup@2.1.0/jquery.counterup.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.countdown/2.2.0/jquery.countdown.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/smooth-scroll@16.1.3/dist/smooth-scroll.polyfills.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/prism.min.js"></script>

  <!-- Neumorphism JS -->
  <script src="<?= e(app_url('assets/js/neumorphism.js')) ?>?v=<?= APP_VERSION ?>"></script>

  <!-- Swiper JS -->
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</body>
</html>
