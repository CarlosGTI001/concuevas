<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        die('CSRF token validation failed.');
    }
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $imageUrl = handle_upload($_FILES['image'] ?? [], 'sliders');
        if (!$imageUrl) {
            $imageUrl = trim((string) ($_POST['image_url'] ?? ''));
        }

        $stmt = db()->prepare('INSERT INTO sliders (title, text, image_url, position, image_position, sort_order) VALUES (:title, :text, :image_url, :position, :image_position, :sort_order)');
        $stmt->execute([
            'title' => trim((string) $_POST['title']),
            'text' => trim((string) $_POST['text']),
            'image_url' => $imageUrl,
            'position' => $_POST['position'] ?? 'center-center',
            'image_position' => $_POST['image_position'] ?? 'center-center',
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
    } elseif ($action === 'update') {
        $id = (int) $_POST['id'];
        $imageUrl = handle_upload($_FILES['image'] ?? [], 'sliders');
        
        $params = [
            'id' => $id,
            'title' => trim((string) $_POST['title']),
            'text' => trim((string) $_POST['text']),
            'position' => $_POST['position'] ?? 'center-center',
            'image_position' => $_POST['image_position'] ?? 'center-center',
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ];

        if ($imageUrl) {
            $params['image_url'] = $imageUrl;
        } else {
            $params['image_url'] = trim((string) ($_POST['image_url'] ?? ''));
        }
        
        $stmt = db()->prepare('UPDATE sliders SET title=:title, text=:text, image_url=:image_url, position=:position, image_position=:image_position, sort_order=:sort_order WHERE id=:id');
        $stmt->execute($params);
    } elseif ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM sliders WHERE id = :id');
        $stmt->execute(['id' => (int) $_POST['id']]);
    }
    redirect(app_url('admin/sliders'));
}

$sliders = db()->query('SELECT * FROM sliders ORDER BY sort_order ASC, id DESC')->fetchAll();
$adminTitle = 'Sliders';
$adminSubtitle = 'Gestiona las imágenes y la posición de los textos del carrusel principal.';

$positions = [
    'top-left' => 'Arriba Izquierda',
    'top-center' => 'Arriba Centro',
    'top-right' => 'Arriba Derecha',
    'center-left' => 'Centro Izquierda',
    'center-center' => 'Centro Total',
    'center-right' => 'Centro Derecha',
    'bottom-left' => 'Abajo Izquierda',
    'bottom-center' => 'Abajo Centro',
    'bottom-right' => 'Abajo Derecha',
];

require __DIR__ . '/partials/header.php';
?>

<div class="card bg-soft border-light shadow-soft p-4 mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <p class="mb-0 text-muted">Tienes <?= count($sliders) ?> diapositivas registradas.</p>
    </div>
    <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#modal-slider-create">
      <span class="fas fa-plus mr-2"></span> Nueva Diapositiva
    </button>
  </div>
</div>

<div class="row">
  <?php foreach ($sliders as $s): ?>
    <div class="col-12 col-md-6 col-lg-4 mb-4">
      <div class="card bg-soft border-light shadow-soft h-100">
        <div class="p-3">
          <div class="position-relative overflow-hidden rounded shadow-inset-soft">
            <img src="<?= e($s['image_url']) ?>" class="card-img-top img-normalized" style="height: 180px;" alt="Slide">
            <span class="badge badge-primary position-absolute" style="top: 10px; right: 10px; z-index: 2;"><?= $positions[$s['position']] ?? $s['position'] ?></span>
          </div>
        </div>
        <div class="card-body pt-0">
          <h3 class="h6 mb-2"><?= e($s['title'] ?: '(Sin título)') ?></h3>
          <p class="small text-muted mb-3 text-truncate"><?= e($s['text'] ?: '(Sin descripción)') ?></p>
          <div class="d-flex justify-content-between align-items-center">
            <span class="badge badge-primary text-dark">Orden: <?= (int)$s['sort_order'] ?></span>
            <div class="btn-group">
              <button class="btn btn-primary btn-sm js-edit-slider" 
                      data-id="<?= $s['id'] ?>"
                      data-title="<?= e($s['title']) ?>"
                      data-text="<?= e($s['text']) ?>"
                      data-url="<?= e($s['image_url']) ?>"
                      data-position="<?= e($s['position']) ?>"
                      data-image-position="<?= e($s['image_position']) ?>"
                      data-sort="<?= $s['sort_order'] ?>"
                      title="Editar">
                <span class="fas fa-edit"></span>
              </button>
              <form method="post" onsubmit="return confirm('¿Eliminar esta diapositiva?');" class="d-inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $s['id'] ?>">
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
</div>

<!-- Modal Create -->
<div class="modal fade" id="modal-slider-create" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content shadow-soft">
            <div class="modal-header border-0">
                <h2 class="h6 modal-title mb-0">Nueva Diapositiva</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" enctype="multipart/form-data">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="create">
                    <div class="form-group">
                        <label for="title">Título</label>
                        <input id="title" name="title" class="form-control" placeholder="Título impactante">
                    </div>
                    <div class="form-group">
                        <label for="text">Texto descriptivo</label>
                        <textarea id="text" name="text" class="form-control" rows="2" placeholder="Breve eslogan descriptivo"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label>Posición del Texto</label>
                                <input type="hidden" name="position" id="position-input" value="center-center">
                                <div id="create-text-grid-container">
                                    <div class="position-selector-grid" id="create-text-grid">
                                        <div class="pos-dot" data-pos="top-left" title="Arriba Izquierda"><i class="fas fa-arrow-up rotate-n45"></i></div>
                                        <div class="pos-dot" data-pos="top-center" title="Arriba Centro"><i class="fas fa-arrow-up"></i></div>
                                        <div class="pos-dot" data-pos="top-right" title="Arriba Derecha"><i class="fas fa-arrow-up rotate-45"></i></div>
                                        <div class="pos-dot" data-pos="center-left" title="Centro Izquierda"><i class="fas fa-arrow-left"></i></div>
                                        <div class="pos-dot active" data-pos="center-center" title="Centro Total"><i class="fas fa-dot-circle"></i></div>
                                        <div class="pos-dot" data-pos="center-right" title="Centro Derecha"><i class="fas fa-arrow-right"></i></div>
                                        <div class="pos-dot" data-pos="bottom-left" title="Abajo Izquierda"><i class="fas fa-arrow-down rotate-45"></i></div>
                                        <div class="pos-dot" data-pos="bottom-center" title="Abajo Centro"><i class="fas fa-arrow-down"></i></div>
                                        <div class="pos-dot" data-pos="bottom-right" title="Abajo Derecha"><i class="fas fa-arrow-down rotate-n45"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label>Enfoque de la Imagen (Recorte)</label>
                                <input type="hidden" name="image_position" id="position-input-image" value="center-center">
                                <div class="position-selector-grid" id="create-image-grid">
                                    <div class="pos-dot" data-pos="top-left" title="Arriba Izquierda"><i class="fas fa-expand-arrows-alt"></i></div>
                                    <div class="pos-dot" data-pos="top-center" title="Arriba Centro"><i class="fas fa-expand-arrows-alt"></i></div>
                                    <div class="pos-dot" data-pos="top-right" title="Arriba Derecha"><i class="fas fa-expand-arrows-alt"></i></div>
                                    <div class="pos-dot" data-pos="center-left" title="Centro Izquierda"><i class="fas fa-expand-arrows-alt"></i></div>
                                    <div class="pos-dot active" data-pos="center-center" title="Centro Total"><i class="fas fa-expand-arrows-alt"></i></div>
                                    <div class="pos-dot" data-pos="center-right" title="Centro Derecha"><i class="fas fa-expand-arrows-alt"></i></div>
                                    <div class="pos-dot" data-pos="bottom-left" title="Abajo Izquierda"><i class="fas fa-expand-arrows-alt"></i></div>
                                    <div class="pos-dot" data-pos="bottom-center" title="Abajo Centro"><i class="fas fa-expand-arrows-alt"></i></div>
                                    <div class="pos-dot" data-pos="bottom-right" title="Abajo Derecha"><i class="fas fa-expand-arrows-alt"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="image">Subir Imagen</label>
                                <input type="file" name="image" id="image" class="form-control" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label for="image_url">O URL de imagen externa</label>
                                <input id="image_url" name="image_url" class="form-control" placeholder="https://...">
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 text-right">
                        <button type="button" class="btn btn-primary text-danger mr-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Diapositiva</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modal-slider-edit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content shadow-soft">
            <div class="modal-header border-0">
                <h2 class="h6 modal-title mb-0">Editar Diapositiva</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" enctype="multipart/form-data">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" id="edit-id" name="id">
                    <div class="form-group">
                        <label for="edit-title">Título</label>
                        <input id="edit-title" name="title" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit-text">Texto descriptivo</label>
                        <textarea id="edit-text" name="text" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label>Posición del Texto</label>
                                <input type="hidden" name="position" id="edit-position-input">
                                <div class="position-selector-grid" id="edit-text-grid">
                                    <div class="pos-dot" data-pos="top-left" title="Arriba Izquierda"><i class="fas fa-arrow-up rotate-n45"></i></div>
                                    <div class="pos-dot" data-pos="top-center" title="Arriba Centro"><i class="fas fa-arrow-up"></i></div>
                                    <div class="pos-dot" data-pos="top-right" title="Arriba Derecha"><i class="fas fa-arrow-up rotate-45"></i></div>
                                    <div class="pos-dot" data-pos="center-left" title="Centro Izquierda"><i class="fas fa-arrow-left"></i></div>
                                    <div class="pos-dot" data-pos="center-center" title="Centro Total"><i class="fas fa-dot-circle"></i></div>
                                    <div class="pos-dot" data-pos="center-right" title="Centro Derecha"><i class="fas fa-arrow-right"></i></div>
                                    <div class="pos-dot" data-pos="bottom-left" title="Abajo Izquierda"><i class="fas fa-arrow-down rotate-45"></i></div>
                                    <div class="pos-dot" data-pos="bottom-center" title="Abajo Centro"><i class="fas fa-arrow-down"></i></div>
                                    <div class="pos-dot" data-pos="bottom-right" title="Abajo Derecha"><i class="fas fa-arrow-down rotate-n45"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label>Enfoque de la Imagen (Recorte)</label>
                                <input type="hidden" name="image_position" id="edit-image-position-input">
                                <div class="position-selector-grid" id="edit-image-grid">
                                    <div class="pos-dot" data-pos="top-left" title="Arriba Izquierda"><i class="fas fa-expand-arrows-alt"></i></div>
                                    <div class="pos-dot" data-pos="top-center" title="Arriba Centro"><i class="fas fa-expand-arrows-alt"></i></div>
                                    <div class="pos-dot" data-pos="top-right" title="Arriba Derecha"><i class="fas fa-expand-arrows-alt"></i></div>
                                    <div class="pos-dot" data-pos="center-left" title="Centro Izquierda"><i class="fas fa-expand-arrows-alt"></i></div>
                                    <div class="pos-dot" data-pos="center-center" title="Centro Total"><i class="fas fa-expand-arrows-alt"></i></div>
                                    <div class="pos-dot" data-pos="center-right" title="Centro Derecha"><i class="fas fa-expand-arrows-alt"></i></div>
                                    <div class="pos-dot" data-pos="bottom-left" title="Abajo Izquierda"><i class="fas fa-expand-arrows-alt"></i></div>
                                    <div class="pos-dot" data-pos="bottom-center" title="Abajo Centro"><i class="fas fa-expand-arrows-alt"></i></div>
                                    <div class="pos-dot" data-pos="bottom-right" title="Abajo Derecha"><i class="fas fa-expand-arrows-alt"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                         <div class="col-md-12">
                            <div class="form-group">
                                <label for="edit-sort">Orden de aparición</label>
                                <input type="number" name="sort_order" id="edit-sort" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit-image">Cambiar Imagen (opcional)</label>
                        <input type="file" name="image" id="edit-image" class="form-control" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label for="edit-url">URL de imagen actual</label>
                        <input id="edit-url" name="image_url" class="form-control">
                    </div>
                    <div class="mt-4 text-right">
                        <button type="button" class="btn btn-primary text-danger mr-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle position selector clicks
    function setupGrid(gridSelector, inputSelector) {
        $(gridSelector + ' .pos-dot').on('click', function() {
            const pos = $(this).data('pos');
            $(gridSelector + ' .pos-dot').removeClass('active');
            $(this).addClass('active');
            $(inputSelector).val(pos);
        });
    }

    // Create Modal Grids
    setupGrid('#modal-slider-create #create-text-grid', '#position-input');
    setupGrid('#modal-slider-create #create-image-grid', '#position-input-image');
    
    // Edit Modal Grids
    setupGrid('#modal-slider-edit #edit-text-grid', '#edit-position-input');
    setupGrid('#modal-slider-edit #edit-image-grid', '#edit-image-position-input');

    $('.js-edit-slider').on('click', function() {
        const btn = $(this);
        const textPos = btn.data('position') || 'center-center';
        const imgPos = btn.data('image-position') || 'center-center';
        
        $('#edit-id').val(btn.data('id'));
        $('#edit-title').val(btn.data('title'));
        $('#edit-text').val(btn.data('text'));
        $('#edit-url').val(btn.data('url'));
        $('#edit-sort').val(btn.data('sort'));
        
        // Sync Text Position Grid
        $('#edit-position-input').val(textPos);
        $('#edit-text-grid .pos-dot').removeClass('active');
        $('#edit-text-grid .pos-dot[data-pos=\"' + textPos + '\"]').addClass('active');
        
        // Sync Image Position Grid
        $('#edit-image-position-input').val(imgPos);
        $('#edit-image-grid .pos-dot').removeClass('active');
        $('#edit-image-grid .pos-dot[data-pos=\"' + imgPos + '\"]').addClass('active');
        
        $('#modal-slider-edit').modal('show');
    });
});
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
