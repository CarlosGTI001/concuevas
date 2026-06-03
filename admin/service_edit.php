<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

$serviceId = (int) ($_GET['id'] ?? 0);
if ($serviceId <= 0) {
    redirect(app_url('admin/services'));
}

$stmt = db()->prepare('SELECT * FROM services WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $serviceId]);
$service = $stmt->fetch();

if (!$service) {
    redirect(app_url('admin/services'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        die('CSRF token validation failed.');
    }
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update') {
        $name = trim((string) $_POST['name']);
        $imageUrl = handle_upload($_FILES['image'] ?? [], 'services', $name);
        $description = $_POST['long_description'] ?? '';

        $sql = 'UPDATE services SET name=:name, short_description=:short, long_description=:desc, sort_order=:sort, updated_at=NOW()';
        $params = [
            'id' => $serviceId,
            'name' => $name,
            'short' => trim((string) $_POST['short_description']),
            'desc' => $description,
            'sort' => (int) ($_POST['sort_order'] ?? 0),
        ];

        if ($imageUrl) {
            $sql .= ', image_url=:img';
            $params['img'] = $imageUrl;
        }

        $sql .= ' WHERE id=:id';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        
    } elseif ($action === 'delete_image') {
        $stmt = db()->prepare('DELETE FROM service_images WHERE id = :id AND service_id = :sid');
        $stmt->execute(['id' => (int) $_POST['id'], 'sid' => $serviceId]);
    }
    redirect(app_url('admin/service_edit.php?id=' . $serviceId));
}

$images = db()->prepare('SELECT * FROM service_images WHERE service_id = :id ORDER BY sort_order ASC, id ASC');
$images->execute(['id' => $serviceId]);
$images = $images->fetchAll();

$adminTitle = 'Editar Servicio: ' . $service['name'];
$adminSubtitle = 'Gestión avanzada: Organiza la galería de este servicio arrastrando las fotos.';
require __DIR__ . '/partials/header.php';
?>

<!-- Main Info -->
<div class="card bg-soft border-light shadow-soft p-4 p-lg-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h5 mb-0">Información del Servicio</h2>
        <a href="<?= e(app_url('admin/services')) ?>" class="btn btn-primary btn-sm">Volver</a>
    </div>

    <form method="post" enctype="multipart/form-data">
        <?= csrf_input() ?>
        <input type="hidden" name="action" value="update">
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="name">Nombre del Servicio</label>
                    <input id="name" name="name" class="form-control" value="<?= e($service['name']) ?>" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="sort_order">Orden de aparición</label>
                    <input id="sort_order" name="sort_order" class="form-control" type="number" value="<?= (int)$service['sort_order'] ?>">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="short_description">Resumen Corto</label>
            <input id="short_description" name="short_description" class="form-control" value="<?= e($service['short_description']) ?>" required>
        </div>
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="image">Cambiar Imagen de Portada</label>
                    <input type="file" name="image" id="image" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="col-md-4 text-center">
                <?php if ($service['image_url']): ?>
                    <img src="<?= e($service['image_url']) ?>" class="rounded shadow-soft" style="width: 120px; height: 80px; object-fit: cover;">
                <?php endif; ?>
            </div>
        </div>
        <div class="form-group">
            <label for="long_description">Descripción Detallada</label>
            <textarea id="long_description" name="long_description" class="form-control js-summernote"><?= e($service['long_description']) ?></textarea>
        </div>
        <div class="text-right">
            <button type="submit" class="btn btn-primary">Guardar Información</button>
        </div>
    </form>
</div>

<!-- Advanced Gallery Manager -->
<div class="card bg-soft border-light shadow-soft p-4 p-lg-5">
    <h2 class="h5 mb-4"><span class="fas fa-images mr-2"></span> Gestor de Galería de Servicio</h2>
    
    <div id="gallery-dropzone" class="dropzone dropzone-custom mb-5">
        <div class="dz-message">
            <span class="fas fa-cloud-upload-alt fa-3x mb-3"></span>
            <h4>Sube varias fotos del servicio</h4>
            <p class="text-muted">Arrastra archivos aquí o haz clic para seleccionar</p>
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
                    <form method="post" onsubmit="return confirm('¿Eliminar esta foto?');">
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
    const itemId = <?= $serviceId ?>;
    const type = 'service';
    const handlerUrl = '<?= e(app_url('admin/gallery_handler')) ?>';

    // Dropzone logic
    Dropzone.autoDiscover = false;
    const myDropzone = new Dropzone("#gallery-dropzone", {
        url: handlerUrl,
        paramName: "file",
        params: { action: 'upload', item_id: itemId, type: type },
        acceptedFiles: 'image/*,video/mp4,video/webm,video/ogg',
        success: function(file, response) {
            location.reload();
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
