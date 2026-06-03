<!-- 4. Dynamic Objective Section -->
<?php if ($objective = setting('about_objective')): ?>
<section class="section section-lg bg-soft">
  <div class="container">
    <div class="card bg-soft border-light shadow-inset-soft p-4 p-lg-5 text-center">
      <h2 class="h3 mb-4">Nuestro Objetivo</h2>
      <div class="lead about-content px-lg-5">
        <?= $objective ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>
