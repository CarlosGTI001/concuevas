<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

// Generate a unique session ID for this creation process
$sessionId = bin2hex(random_bytes(16));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        die('CSRF token validation failed.');
    }
    
    $name = trim((string) $_POST['name']);
    $formSessionId = $_POST['upload_session_id'] ?? '';
    $imageUrl = handle_upload($_FILES['image'] ?? [], 'services', $name);
    
    db()->beginTransaction();
    try {
        $stmt = db()->prepare('INSERT INTO services (name, short_description, long_description, image_url, sort_order, created_at, updated_at) VALUES (:name, :short_description, :long_description, :image_url, :sort_order, NOW(), NOW())');
        $stmt->execute([
            'name' => $name,
            'short_description' => trim((string) $_POST['short_description']),
            'long_description' => $_POST['long_description'] ?? '',
            'image_url' => $imageUrl,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
        
        $newId = db()->lastInsertId();

        // "Claim" images uploaded during this session
        if ($formSessionId !== '') {
            $updateImages = db()->prepare('UPDATE service_images SET service_id = :sid, session_id = NULL WHERE session_id = :sess');
            $updateImages->execute(['sid' => $newId, 'sess' => $formSessionId]);
        }

        db()->commit();
        redirect(app_url('admin/services'));
    } catch (Exception $e) {
        db()->rollBack();
        die("Error al guardar: " . $e->getMessage());
    }
}

$adminTitle = 'Nuevo Servicio';
$adminSubtitle = 'Define el servicio y añade fotos a su galería antes de publicar.';
require __DIR__ . '/partials/header.php';
?>

<div class="card bg-soft border-light shadow-soft p-4 p-lg-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h5 mb-0">Completar datos del servicio</h2>
        <a href="<?= e(app_url('admin/services')) ?>" class="btn btn-primary btn-sm">Volver</a>
    </div>

    <form method="post" enctype="multipart/form-data" id="main-form">
        <?= csrf_input() ?>
        <input type="hidden" name="upload_session_id" value="<?= $sessionId ?>">
        
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="name">Nombre del Servicio</label>
                    <input id="name" name="name" class="form-control" required placeholder="Ej. Arquitectura de Interiores">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="sort_order">Orden de aparición</label>
                    <input id="sort_order" name="sort_order" class="form-control" type="number" value="0">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="short_description">Descripción Corta (Resumen para tarjetas)</label>
            <input id="short_description" name="short_description" class="form-control" required placeholder="Resumen corto...">
        </div>

        <div class="form-group">
            <label for="image">Imagen de Portada Principal</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
        </div>

        <div class="form-group">
            <label for="long_description">Descripción Detallada</label>
            <textarea id="long_description" name="long_description" class="form-control js-summernote"></textarea>
        </div>

        <div class="mt-5 pt-4 border-top border-light">
            <h2 class="h5 mb-4"><span class="fas fa-images mr-2"></span> Galería del Servicio (Añade fotos ahora)</h2>
            
            <div id="gallery-dropzone" class="dropzone dropzone-custom mb-5">
                <div class="dz-message">
                    <span class="fas fa-cloud-upload-alt fa-3x mb-3"></span>
                    <h4>Arrastra las fotos de la galería aquí</h4>
                    <p class="text-muted">Se subirán temporalmente y se vincularán al publicar.</p>
                </div>
            </div>

            <div id="gallery-manager" class="gallery-manager-grid">
                <!-- Temporary images will appear here -->
            </div>
        </div>

        <div class="mt-5 text-right">
            <button type="submit" class="btn btn-primary btn-lg px-5 shadow-soft">
                <span class="fas fa-paper-plane mr-2"></span> Publicar Servicio Completo
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sessionId = '<?= $sessionId ?>';
    const type = 'service';
    const handlerUrl = '<?= e(app_url('admin/gallery_handler')) ?>';

    // Dropzone logic
    Dropzone.autoDiscover = false;
    const myDropzone = new Dropzone("#gallery-dropzone", {
        url: handlerUrl,
        paramName: "file",
        params: { action: 'upload', session_id: sessionId, type: type, csrf_token: '<?= generate_csrf_token() ?>' },
        acceptedFiles: 'image/*',
        success: function(file, response) {
            const html = `
                <div class="gallery-item-card" data-id="${response.id}">
                    <img src="${response.url}" alt="Gallery Image">
                    <div class="sort-handle"><span class="fas fa-arrows-alt"></span></div>
                </div>`;
            $('#gallery-manager').append(html);
        }
    });

    // Sortable logic
    const el = document.getElementById('gallery-manager');
    const sortable = Sortable.create(el, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        onEnd: function() {
            const ids = Array.from(el.querySelectorAll('.gallery-item-card')).map(item => item.dataset.id);
            $.post(handlerUrl, { action: 'sort', ids: ids, type: type, csrf_token: '<?= generate_csrf_token() ?>' });
        }
    });
});
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
