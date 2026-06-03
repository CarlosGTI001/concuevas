<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        die('CSRF token validation failed.');
    }
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $title = trim((string) $_POST['title']);
        $imageUrl = handle_upload($_FILES['image'] ?? [], 'projects', $title);

        $slug = trim((string) ($_POST['slug'] ?? ''));
        if ($slug === '') {
            $slug = slugify($title);
        }

        $stmt = db()->prepare('INSERT INTO projects (title, slug, excerpt, description, cover_image_url, created_at, updated_at) VALUES (:title, :slug, :excerpt, :description, :cover_image_url, NOW(), NOW())');
        $stmt->execute([
            'title' => $title,
            'slug' => $slug,
            'excerpt' => trim((string) $_POST['excerpt']),
            'description' => trim((string) $_POST['description']),
            'cover_image_url' => $imageUrl,
        ]);
        redirect(app_url('admin/projects'));
    } elseif ($action === 'delete') {
        $id = (int) $_POST['id'];
        db()->prepare('DELETE FROM project_images WHERE project_id=:id')->execute(['id' => $id]);
        db()->prepare('DELETE FROM projects WHERE id=:id')->execute(['id' => $id]);
        redirect(app_url('admin/projects'));
    }
}

$adminTitle = 'Proyectos';
$adminSubtitle = 'Listado limpio: crea aquí y edita cada proyecto en su pantalla dedicada.';
$query = trim((string) ($_GET['q'] ?? ''));

if ($query !== '') {
    $stmtProjects = db()->prepare('SELECT * FROM projects WHERE title LIKE :q OR excerpt LIKE :q OR description LIKE :q ORDER BY updated_at DESC');
    $stmtProjects->execute(['q' => '%' . $query . '%']);
    $projects = $stmtProjects->fetchAll();
} else {
    $projects = db()->query('SELECT * FROM projects ORDER BY updated_at DESC')->fetchAll();
}

$totalProjects = (int) db()->query('SELECT COUNT(*) FROM projects')->fetchColumn();
require __DIR__ . '/partials/header.php';
?>
<div class="card bg-soft border-light shadow-soft p-4 mb-4">
  <div class="row align-items-center">
    <div class="col-12 col-md-6 mb-3 mb-md-0">
      <form method="get" class="d-flex">
        <input type="text" name="q" class="form-control mr-2" value="<?= e($query) ?>" placeholder="Buscar proyectos...">
        <button class="btn btn-primary btn-sm" type="submit">Buscar</button>
        <?php if ($query !== ''): ?><a class="btn btn-primary btn-sm ml-2" href="<?= e(app_url('admin/projects')) ?>">Limpiar</a><?php endif; ?>
      </form>
    </div>
    <div class="col-12 col-md-6 text-md-right">
      <p class="mb-2 text-muted">Mostrando <?= count($projects) ?> de <?= $totalProjects ?> proyectos.</p>
      <a href="<?= e(app_url('admin/project_create')) ?>" class="btn btn-primary">
        <span class="fas fa-plus mr-2"></span> Nuevo proyecto
      </a>
    </div>
  </div>
</div>

<div class="card bg-soft border-light shadow-soft p-4">
  <h2 class="h5 mb-4">Listado de Obras</h2>
  <?php if ($projects): ?>
    <div class="table-responsive">
      <table class="table table-hover border-light">
        <thead><tr><th class="border-0">Título</th><th class="border-0">Slug</th><th class="border-0">Actualizado</th><th class="border-0 text-right">Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($projects as $project): ?>
          <tr>
            <td><?= e($project['title']) ?></td>
            <td><span class="badge badge-primary text-dark"><?= e($project['slug']) ?></span></td>
            <td><?= e($project['updated_at']) ?></td>
            <td class="text-right">
              <div class="d-flex justify-content-end">
                <a class="btn btn-primary btn-sm mr-2" href="<?= e(app_url('admin/project_edit.php?id=' . (int) $project['id'])) ?>" title="Editar">
                  <span class="fas fa-edit"></span>
                </a>
                <a class="btn btn-primary btn-sm mr-2" href="<?= e(app_url('proyecto?slug=' . urlencode((string) $project['slug']))) ?>" target="_blank" title="Vista Pública">
                  <span class="fas fa-eye"></span>
                </a>
                <form method="post" onsubmit="return confirm('¿Eliminar proyecto?');">
                  <?= csrf_input() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $project['id'] ?>">
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
    <p class="text-muted text-center py-4">No hay proyectos para mostrar con el filtro actual.</p>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
