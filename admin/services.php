<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        die('CSRF token validation failed.');
    }
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM service_images WHERE service_id=:id');
        $stmt->execute(['id' => (int) $_POST['id']]);
        $stmt = db()->prepare('DELETE FROM services WHERE id=:id');
        $stmt->execute(['id' => (int) $_POST['id']]);
        redirect(app_url('admin/services'));
    }
}

$adminTitle = 'Servicios';
$adminSubtitle = 'Gestiona tus servicios: utiliza las páginas dedicadas para crear y editar con libertad.';
$query = trim((string) ($_GET['q'] ?? ''));

if ($query !== '') {
    $stmt = db()->prepare('SELECT * FROM services WHERE name LIKE :q OR short_description LIKE :q OR long_description LIKE :q ORDER BY sort_order ASC, id DESC');
    $stmt->execute(['q' => '%' . $query . '%']);
    $services = $stmt->fetchAll();
} else {
    $services = db()->query('SELECT * FROM services ORDER BY sort_order ASC, id DESC')->fetchAll();
}

$totalServices = (int) db()->query('SELECT COUNT(*) FROM services')->fetchColumn();
require __DIR__ . '/partials/header.php';
?>
<div class="card bg-soft border-light shadow-soft p-4 mb-4">
  <div class="row align-items-center">
    <div class="col-12 col-md-6 mb-3 mb-md-0">
      <form method="get" class="d-flex">
        <input type="text" name="q" class="form-control mr-2" value="<?= e($query) ?>" placeholder="Buscar servicios...">
        <button class="btn btn-primary btn-sm" type="submit">Buscar</button>
        <?php if ($query !== ''): ?><a class="btn btn-primary btn-sm ml-2" href="<?= e(app_url('admin/services')) ?>">Limpiar</a><?php endif; ?>
      </form>
    </div>
    <div class="col-12 col-md-6 text-md-right">
      <p class="mb-2 text-muted">Mostrando <?= count($services) ?> de <?= $totalServices ?> servicios.</p>
      <a href="<?= e(app_url('admin/service_create')) ?>" class="btn btn-primary">
        <span class="fas fa-plus mr-2"></span> Nuevo servicio
      </a>
    </div>
  </div>
</div>

<div class="card bg-soft border-light shadow-soft p-4">
  <h2 class="h5 mb-4">Listado de Servicios</h2>
  <?php if ($services): ?>
    <div class="table-responsive">
      <table class="table table-hover border-light">
        <thead><tr><th class="border-0">Nombre</th><th class="border-0">Descripción corta</th><th class="border-0">Orden</th><th class="border-0 text-right">Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($services as $service): ?>
          <tr>
            <td><?= e($service['name']) ?></td>
            <td><?= e($service['short_description']) ?></td>
            <td><?= (int) $service['sort_order'] ?></td>
            <td class="text-right">
              <div class="d-flex justify-content-end">
                <a class="btn btn-primary btn-sm mr-2" href="<?= e(app_url('admin/service_edit.php?id=' . (int) $service['id'])) ?>" title="Editar">
                  <span class="fas fa-edit"></span>
                </a>
                <a class="btn btn-primary btn-sm mr-2" href="<?= e(app_url('servicio?id=' . (int) $service['id'])) ?>" target="_blank" title="Vista Pública">
                  <span class="fas fa-eye"></span>
                </a>
                <form method="post" onsubmit="return confirm('¿Eliminar servicio?');">
                  <?= csrf_input() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $service['id'] ?>">
                  <button class="btn btn-danger btn-sm" type="submit" title="Eliminar">
                    <span class="fas fa-trash-alt"></span>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-muted text-center py-4">No se encontraron servicios con ese criterio.</p>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
