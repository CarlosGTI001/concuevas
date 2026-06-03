<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

$adminTitle = 'Dashboard';
$adminSubtitle = 'Resumen rápido del contenido y actividad del sitio.';
$counts = [
    'services' => (int) db()->query('SELECT COUNT(*) FROM services')->fetchColumn(),
    'projects' => (int) db()->query('SELECT COUNT(*) FROM projects')->fetchColumn(),
    'quotes' => (int) db()->query('SELECT COUNT(*) FROM quote_requests')->fetchColumn(),
];
$lastQuoteAt = db()->query('SELECT created_at FROM quote_requests ORDER BY created_at DESC LIMIT 1')->fetchColumn();
$recentQuotes = db()->query('SELECT id, name, email, project_type, created_at FROM quote_requests ORDER BY created_at DESC LIMIT 5')->fetchAll();
$recentProjects = db()->query('SELECT title, slug, updated_at FROM projects ORDER BY updated_at DESC LIMIT 5')->fetchAll();

require __DIR__ . '/partials/header.php';
?>
<div class="row">
  <div class="col-12 col-md-6 col-lg-3 mb-4">
    <div class="card bg-soft border-light shadow-soft p-3">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div>
            <div class="h6 mb-0 text-muted">Servicios</div>
          </div>
          <div>
            <span class="fas fa-tools text-dark"></span>
          </div>
        </div>
        <div class="d-flex align-items-center">
          <h3 class="display-3 mb-0 counter" data-count="<?= $counts['services'] ?>">0</h3>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-lg-3 mb-4">
    <div class="card bg-soft border-light shadow-soft p-3">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div>
            <div class="h6 mb-0 text-muted">Proyectos</div>
          </div>
          <div>
            <span class="fas fa-building text-dark"></span>
          </div>
        </div>
        <div class="d-flex align-items-center">
          <h3 class="display-3 mb-0 counter" data-count="<?= $counts['projects'] ?>">0</h3>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-lg-3 mb-4">
    <div class="card bg-soft border-light shadow-soft p-3">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div>
            <div class="h6 mb-0 text-muted">Cotizaciones</div>
          </div>
          <div>
            <span class="fas fa-file-invoice-dollar text-dark"></span>
          </div>
        </div>
        <div class="d-flex align-items-center">
          <h3 class="display-3 mb-0 counter" data-count="<?= $counts['quotes'] ?>">0</h3>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-lg-3 mb-4">
    <div class="card bg-soft border-light shadow-soft p-3">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div>
            <div class="h6 mb-0 text-muted">Último contacto</div>
          </div>
          <div>
            <span class="fas fa-clock text-dark"></span>
          </div>
        </div>
        <div class="d-flex align-items-center">
          <p class="h6 mb-0"><?= e($lastQuoteAt ? date('d M, H:i', strtotime((string) $lastQuoteAt)) : 'Sin datos') ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row mt-4">
  <div class="col-12 col-lg-7 mb-4">
    <div class="card bg-soft border-light shadow-soft p-4 h-100">
      <h2 class="h5 mb-4"><span class="fas fa-clipboard-list mr-2"></span> Últimas cotizaciones</h2>
      <?php if ($recentQuotes): ?>
        <div class="table-responsive">
          <table class="table table-hover border-light">
            <thead><tr><th class="border-0">Nombre</th><th class="border-0">Tipo</th><th class="border-0">Fecha</th></tr></thead>
            <tbody>
              <?php foreach ($recentQuotes as $quote): ?>
                <tr>
                  <td>
                    <div class="font-weight-bold text-dark"><?= e($quote['name']) ?></div>
                    <div class="small text-muted"><?= e($quote['email']) ?></div>
                  </td>
                  <td><span class="badge badge-primary text-dark"><?= e($quote['project_type']) ?></span></td>
                  <td><span class="small"><?= date('d/m/Y', strtotime((string) $quote['created_at'])) ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="mt-4 text-center">
          <a class="btn btn-primary btn-sm" href="<?= e(app_url('admin/quotes.php')) ?>">Ver todas</a>
        </div>
      <?php else: ?>
        <p class="text-muted text-center py-4">Todavía no llegaron solicitudes.</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-12 col-lg-5 mb-4">
    <div class="card bg-soft border-light shadow-soft p-4 mb-4">
      <h2 class="h5 mb-4"><span class="fas fa-bolt mr-2"></span> Acciones rápidas</h2>
      <div class="list-group list-group-flush shadow-inset rounded p-3 mb-4">
        <a href="<?= e(app_url('admin/projects')) ?>" class="list-group-item list-group-item-action bg-transparent border-light d-flex align-items-center">
          <span class="fas fa-plus-circle mr-3 text-success"></span> Gestionar Proyectos
        </a>
        <a href="<?= e(app_url('admin/services')) ?>" class="list-group-item list-group-item-action bg-transparent border-light d-flex align-items-center">
          <span class="fas fa-concierge-bell mr-3 text-info"></span> Gestionar Servicios
        </a>
        <a href="<?= e(app_url('admin/settings')) ?>" class="list-group-item list-group-item-action bg-transparent border-light d-flex align-items-center">
          <span class="fas fa-cog mr-3 text-warning"></span> Configuración General
        </a>
        <a href="<?= e(app_url('admin/users')) ?>" class="list-group-item list-group-item-action bg-transparent border-0 d-flex align-items-center">
          <span class="fas fa-users-cog mr-3 text-primary"></span> Gestionar Usuarios
        </a>
      </div>
    </div>

    <div class="card bg-soft border-light shadow-soft p-4">
      <h2 class="h5 mb-4"><span class="fas fa-paint-brush mr-2"></span> Personalización del Sitio</h2>
      <div class="list-group list-group-flush shadow-inset rounded p-3">
        <a href="<?= e(app_url('admin/sliders')) ?>" class="list-group-item list-group-item-action bg-transparent border-light d-flex align-items-center">
          <span class="fas fa-images mr-3"></span> Gestionar Slider / Jumbotron
        </a>
        <a href="<?= e(app_url('admin/settings')) ?>#about-objective" class="list-group-item list-group-item-action bg-transparent border-light d-flex align-items-center">
          <span class="fas fa-bullseye mr-3"></span> Editar Nuestro Objetivo
        </a>
        <a href="<?= e(app_url('admin/values')) ?>" class="list-group-item list-group-item-action bg-transparent border-light d-flex align-items-center">
          <span class="fas fa-heart mr-3"></span> Gestionar Valores Corporativos
        </a>
        <a href="<?= e(app_url('admin/goals')) ?>" class="list-group-item list-group-item-action bg-transparent border-light d-flex align-items-center">
          <span class="fas fa-flag mr-3"></span> Gestionar Metas de la Empresa
        </a>
        <a href="<?= e(app_url('admin/settings')) ?>#footer-icon-section" class="list-group-item list-group-item-action bg-transparent border-light d-flex align-items-center">
          <span class="fas fa-image mr-3"></span> Editar Logo del Footer
        </a>
        <a href="<?= e(app_url('admin/settings')) ?>#identity-section" class="list-group-item list-group-item-action bg-transparent border-0 d-flex align-items-center">
          <span class="fas fa-id-card mr-3"></span> Logos, Favicon y Marca
        </a>
      </div>
      <p class="small text-muted mt-3">Personaliza el impacto visual y la identidad de tu landing page.</p>
    </div>
  </div>
  </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const counters = document.querySelectorAll('.counter');
  counters.forEach(counter => {
    const target = +counter.getAttribute('data-count');
    const increment = target / 50;
    
    const updateCount = () => {
      const count = +counter.innerText;
      if (count < target) {
        counter.innerText = Math.ceil(count + increment);
        setTimeout(updateCount, 20);
      } else {
        counter.innerText = target;
      }
    };
    updateCount();
  });
});
</script>
<?php require __DIR__ . '/partials/footer.php'; ?>
