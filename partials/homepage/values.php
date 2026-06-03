<!-- 4. Dynamic Values Section -->
<section class="section section-lg">
  <div class="container text-center">
    <h2 class="h1 mb-5">Por qué elegirnos</h2>
    <div class="row">
      <?php foreach ($siteValues as $v): ?>
        <div class="col-md-4 mb-5">
          <div class="card bg-soft border-light shadow-soft h-100 p-4 border-radius-lg">
            <div class="icon-shape icon-shape-primary rounded-circle mb-4">
              <span class="<?= e($v['icon']) ?>"></span>
            </div>
            <h3 class="h5"><?= e($v['title']) ?></h3>
            <p class="text-muted small mb-0"><?= e($v['description']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
