<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        die('CSRF token validation failed.');
    }
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $stmt = db()->prepare('INSERT INTO site_goals (title, description, sort_order) VALUES (:title, :description, :sort_order)');
        $stmt->execute([
            'title' => trim((string) $_POST['title']),
            'description' => trim((string) $_POST['description']),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
    } elseif ($action === 'update') {
        $stmt = db()->prepare('UPDATE site_goals SET title=:title, description=:description, sort_order=:sort_order WHERE id=:id');
        $stmt->execute([
            'id' => (int) $_POST['id'],
            'title' => trim((string) $_POST['title']),
            'description' => trim((string) $_POST['description']),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
    } elseif ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM site_goals WHERE id = :id');
        $stmt->execute(['id' => (int) $_POST['id']]);
    }
    redirect(app_url('admin/goals'));
}

$goals = db()->query('SELECT * FROM site_goals ORDER BY sort_order ASC, id DESC')->fetchAll();
$adminTitle = 'Metas de la Empresa';
$adminSubtitle = 'Define los objetivos y metas que se muestran en la sección "Nosotros".';

require __DIR__ . '/partials/header.php';
?>

<div class="card bg-soft border-light shadow-soft p-4 mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <p class="mb-0 text-muted">Tienes <?= count($goals) ?> metas registradas.</p>
    </div>
    <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#modal-goal-create">
      <span class="fas fa-plus mr-2"></span> Nueva Meta
    </button>
  </div>
</div>

<div class="row">
  <?php foreach ($goals as $g): ?>
    <div class="col-12 mb-4">
      <div class="card bg-soft border-light shadow-soft p-4">
        <div class="row align-items-center">
          <div class="col-md-1">
             <div class="icon-shape icon-shape-primary rounded-circle shadow-inset-soft" style="width: 2.5rem; height: 2.5rem;">
                <span class="fas fa-bullseye"></span>
             </div>
          </div>
          <div class="col-md-8">
            <h3 class="h6 mb-1"><?= e($g['title']) ?></h3>
            <p class="small text-muted mb-0"><?= e($g['description']) ?></p>
          </div>
          <div class="col-md-3 text-right">
            <span class="badge badge-primary text-dark mr-3">Orden: <?= (int)$g['sort_order'] ?></span>
            <div class="btn-group">
              <button class="btn btn-primary btn-sm js-edit-goal" 
                      data-id="<?= $g['id'] ?>"
                      data-title="<?= e($g['title']) ?>"
                      data-description="<?= e($g['description']) ?>"
                      data-sort="<?= $g['sort_order'] ?>"
                      title="Editar">
                <span class="fas fa-edit"></span>
              </button>
              <form method="post" onsubmit="return confirm('¿Eliminar esta meta?');" class="d-inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $g['id'] ?>">
                <button class="btn btn-danger btn-sm" type="submit" title="Eliminar">
                  <span class="fas fa-trash-alt"></span>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (empty($goals)): ?>
    <div class="col-12 text-center py-5">
      <p class="text-muted">No has registrado metas aún.</p>
    </div>
  <?php endif; ?>
</div>

<!-- Modal Create -->
<div class="modal fade" id="modal-goal-create" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-soft">
            <div class="modal-header border-0">
                <h2 class="h6 modal-title mb-0">Nueva Meta</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="create">
                    <div class="form-group">
                        <label for="title">Título de la Meta</label>
                        <input id="title" name="title" class="form-control" required placeholder="Ej. Liderazgo Regional">
                    </div>
                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <textarea id="description" name="description" class="form-control" rows="3" required placeholder="Describe el objetivo detalladamente..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Orden</label>
                        <input type="number" name="sort_order" id="sort_order" class="form-control" value="0">
                    </div>
                    <div class="mt-4 text-right">
                        <button type="button" class="btn btn-primary text-danger mr-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Meta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modal-goal-edit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-soft">
            <div class="modal-header border-0">
                <h2 class="h6 modal-title mb-0">Editar Meta</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" id="edit-id" name="id">
                    <div class="form-group">
                        <label for="edit-title">Título</label>
                        <input id="edit-title" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-description">Descripción</label>
                        <textarea id="edit-description" name="description" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-sort">Orden</label>
                        <input type="number" name="sort_order" id="edit-sort" class="form-control">
                    </div>
                    <div class="mt-4 text-right">
                        <button type="button" class="btn btn-primary text-danger mr-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('.js-edit-goal').on('click', function() {
        const btn = $(this);
        $('#edit-id').val(btn.data('id'));
        $('#edit-title').val(btn.data('title'));
        $('#edit-description').val(btn.data('description'));
        $('#edit-sort').val(btn.data('sort'));
        $('#modal-goal-edit').modal('show');
    });
});
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
