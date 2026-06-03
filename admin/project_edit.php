<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

$projectId = (int) ($_GET['id'] ?? 0);
if ($projectId <= 0) {
    redirect(app_url('admin/projects'));
}

$stmt = db()->prepare('SELECT * FROM projects WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $projectId]);
$project = $stmt->fetch();

if (!$project) {
    redirect(app_url('admin/projects'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        die('CSRF token validation failed.');
    }
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update') {
        $title = trim((string) $_POST['title']);
        $slug = trim((string) ($_POST['slug'] ?? ''));
        if ($slug === '') {
            $slug = slugify($title);
        }
        
        $imageUrl = handle_upload($_FILES['image'] ?? [], 'projects', $title);
        $description = $_POST['description'] ?? '';

        $sql = 'UPDATE projects SET title=:title, slug=:slug, excerpt=:excerpt, description=:description, updated_at=NOW()';
        $params = [
            'id' => $projectId,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => trim((string) $_POST['excerpt']),
            'description' => $description,
        ];

        if ($imageUrl) {
            $sql .= ', cover_image_url=:img';
            $params['img'] = $imageUrl;
        }

        $sql .= ' WHERE id=:id';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        
    } elseif ($action === 'delete_image') {
        $stmt = db()->prepare('DELETE FROM project_images WHERE id = :id AND project_id = :pid');
        $stmt->execute(['id' => (int) $_POST['id'], 'pid' => $projectId]);
    }
    redirect(app_url('admin/project_edit.php?id=' . $projectId));
}

$images = db()->prepare('SELECT * FROM project_images WHERE project_id = :id ORDER BY sort_order ASC, id ASC');
$images->execute(['id' => $projectId]);
$images = $images->fetchAll();

$adminTitle = 'Editar Proyecto: ' . $project['title'];
$adminSubtitle = 'Gestión avanzada: Arrastra imágenes para organizar tu galería.';
require __DIR__ . '/partials/header.php';
?>

<!-- Main Info -->
<div class="card bg-soft border-light shadow-soft p-4 p-lg-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h5 mb-0">Información General</h2>
        <a href="<?= e(app_url('admin/projects')) ?>" class="btn btn-primary btn-sm">Volver</a>
    </div>

    <form method="post" enctype="multipart/form-data">
        <?= csrf_input() ?>
        <input type="hidden" name="action" value="update">
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="title">Título del Proyecto</label>
                    <input id="title" name="title" class="form-control" value="<?= e($project['title']) ?>" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="slug">Slug (URL)</label>
                    <input id="slug" name="slug" class="form-control" value="<?= e($project['slug']) ?>" required>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="excerpt">Resumen (Extracto)</label>
            <input id="excerpt" name="excerpt" class="form-control" value="<?= e($project['excerpt']) ?>" required>
        </div>
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="image">Cambiar Portada</label>
                    <input type="file" name="image" id="image" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="col-md-4 text-center">
                <img src="<?= e($project['cover_image_url']) ?>" class="rounded shadow-soft" style="width: 120px; height: 80px; object-fit: cover;">
            </div>
        </div>
        <div class="form-group">
            <label for="description">Descripción Detallada</label>
            <textarea id="description" name="description" class="form-control js-summernote"><?= e($project['description']) ?></textarea>
        </div>
        <div class="text-right">
            <button type="submit" class="btn btn-primary">Guardar Información</button>
        </div>
    </form>
</div>

<!-- Advanced Gallery Manager -->
<div class="card bg-soft border-light shadow-soft p-4 p-lg-5">
    <h2 class="h5 mb-4"><span class="fas fa-images mr-2"></span> Gestor de Galería</h2>
    
    <div id="gallery-dropzone" class="dropzone dropzone-custom mb-5">
        <div class="dz-message">
            <span class="fas fa-cloud-upload-alt fa-3x mb-3"></span>
            <h4>Arrastra varias imágenes aquí</h4>
            <p class="text-muted">O haz clic para seleccionar archivos</p>
        </div>
    </div>

    <div id="gallery-manager" class="gallery-manager-grid">
        <?php foreach ($images as $img): ?>
            <div class="gallery-item-card" data-id="<?= $img['id'] ?>">
                <?php if (($img['media_type'] ?? 'image') === 'video'): ?>
                    <video src="<?= e($img['image_url']) ?>" style="width:100%; height:100%; object-fit:cover;" muted></video>
                    <div style="position:absolute; top:5px; left:5px; background:rgba(0,0,0,0.6); color:#fff; padding:2px 6px; border-radius:4px; font-size:12px;">
                        <i class="fas fa-video"></i>
                    </div>
                <?php else: ?>
                    <img src="<?= e($img['image_url']) ?>" alt="Gallery Image">
                <?php endif; ?>
                <div class="sort-handle"><span class="fas fa-arrows-alt"></span></div>
                <div class="item-actions">
                    <form method="post" onsubmit="return confirm('¿Eliminar este elemento?');">
                        <?= csrf_input() ?>
                        <input type="hidden" name="action" value="delete_image">
                        <input type="hidden" name="id" value="<?= (int) $img['id'] ?>">
                        <button class="btn btn-danger btn-sm p-1 px-2" type="submit"><span class="fas fa-times"></span></button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemId = <?= $projectId ?>;
    const type = 'project';
    const handlerUrl = '<?= e(app_url('admin/gallery_handler')) ?>';

    // Dropzone logic
    Dropzone.autoDiscover = false;
    const myDropzone = new Dropzone("#gallery-dropzone", {
        url: handlerUrl,
        paramName: "file",
        params: { action: 'upload', item_id: itemId, type: type },
        acceptedFiles: 'image/*,video/mp4,video/webm,video/ogg',
        success: function(file, response) {
            location.reload(); // Simplest way to show new items
        }
    });

    // Sortable logic
    const el = document.getElementById('gallery-manager');
    if (el) {
        const sortable = Sortable.create(el, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function() {
                const ids = Array.from(el.querySelectorAll('.gallery-item-card')).map(item => item.dataset.id);
                $.post(handlerUrl, { action: 'sort', ids: ids, type: type, csrf_token: '<?= generate_csrf_token() ?>' });
            }
        });
    }
});
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
