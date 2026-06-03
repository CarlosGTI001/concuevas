<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        die('CSRF token validation failed.');
    }
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $stmt = db()->prepare('INSERT INTO site_values (title, description, icon, sort_order) VALUES (:title, :description, :icon, :sort_order)');
        $stmt->execute([
            'title' => trim((string) $_POST['title']),
            'description' => trim((string) $_POST['description']),
            'icon' => trim((string) ($_POST['icon'] ?: 'fas fa-gem')),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
    } elseif ($action === 'update') {
        $stmt = db()->prepare('UPDATE site_values SET title=:title, description=:description, icon=:icon, sort_order=:sort_order WHERE id=:id');
        $stmt->execute([
            'id' => (int) $_POST['id'],
            'title' => trim((string) $_POST['title']),
            'description' => trim((string) $_POST['description']),
            'icon' => trim((string) ($_POST['icon'] ?: 'fas fa-gem')),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
    } elseif ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM site_values WHERE id = :id');
        $stmt->execute(['id' => (int) $_POST['id']]);
    }
    redirect(app_url('admin/values'));
}

$values = db()->query('SELECT * FROM site_values ORDER BY sort_order ASC, id DESC')->fetchAll();
$adminTitle = 'Valores Corporativos';
$adminSubtitle = 'Gestiona los valores que se muestran en la sección "Nosotros".';

// Expanded FontAwesome icon library
$allIcons = [
    'fas fa-gem', 'fas fa-handshake', 'fas fa-bullseye', 'fas fa-award', 'fas fa-balance-scale', 
    'fas fa-users', 'fas fa-lightbulb', 'fas fa-shield-alt', 'fas fa-rocket', 'fas fa-clock', 
    'fas fa-heart', 'fas fa-star', 'fas fa-check-circle', 'fas fa-globe', 'fas fa-building', 
    'fas fa-tools', 'fas fa-hard-hat', 'fas fa-drafting-compass', 'fas fa-leaf', 'fas fa-medal',
    'fas fa-user-tie', 'fas fa-user-shield', 'fas fa-thumbs-up', 'fas fa-smile', 'fas fa-seedling',
    'fas fa-search', 'fas fa-ruler-combined', 'fas fa-road', 'fas fa-recycle', 'fas fa-puzzle-piece',
    'fas fa-project-diagram', 'fas fa-microchip', 'fas fa-map-marked-alt', 'fas fa-magic', 'fas fa-link',
    'fas fa-key', 'fas fa-info-circle', 'fas fa-infinity', 'fas fa-history', 'fas fa-hammer',
    'fas fa-hand-holding-heart', 'fas fa-graduation-cap', 'fas fa-fist-raised', 'fas fa-feather', 'fas fa-eye',
    'fas fa-exclamation-triangle', 'fas fa-envelope-open-text', 'fas fa-desktop', 'fas fa-cubes', 'fas fa-crown',
    'fas fa-compass', 'fas fa-cog', 'fas fa-city', 'fas fa-chart-line', 'fas fa-certificate',
    'fas fa-briefcase', 'fas fa-boxes', 'fas fa-box-open', 'fas fa-bolt', 'fas fa-binoculars',
    'fas fa-atlas', 'fas fa-anchor', 'fas fa-archway', 'fas fa-balance-scale-left', 'fas fa-barcode',
    'fas fa-battery-full', 'fas fa-bell', 'fas fa-book', 'fas fa-brain', 'fas fa-broadcast-tower',
    'fas fa-calculator', 'fas fa-calendar-alt', 'fas fa-camera', 'fas fa-capsules', 'fas fa-car',
    'fas fa-cloud', 'fas fa-code', 'fas fa-comments', 'fas fa-database', 'fas fa-dice',
    'fas fa-dna', 'fas fa-dollar-sign', 'fas fa-door-open', 'fas fa-edit', 'fas fa-envelope',
    'fas fa-eraser', 'fas fa-euro-sign', 'fas fa-exchange-alt', 'fas fa-file-alt', 'fas fa-flask',
    'fas fa-folder-open', 'fas fa-gift', 'fas fa-glass-martini', 'fas fa-gavel', 'fas fa-helicopter',
    'fas fa-home', 'fas fa-id-badge', 'fas fa-image', 'fas fa-landmark', 'fas fa-laptop',
    'fas fa-list', 'fas fa-lock', 'fas fa-map-pin', 'fas fa-money-bill-wave', 'fas fa-music',
    'fas fa-newspaper', 'fas fa-paint-roller', 'fas fa-palette', 'fas fa-paper-plane', 'fas fa-pen-nib',
    'fas fa-phone', 'fas fa-plane', 'fas fa-plug', 'fas fa-print', 'fas fa-question-circle',
    'fas fa-quote-left', 'fas fa-robot', 'fas fa-route', 'fas fa-satellite', 'fas fa-school',
    'fas fa-server', 'fas fa-shopping-cart', 'fas fa-signal', 'fas fa-sitemap', 'fas fa-spa',
    'fas fa-store', 'fas fa-stream', 'fas fa-sun', 'fas fa-sync-alt', 'fas fa-tablet-alt',
    'fas fa-tag', 'fas fa-target', 'fas fa-terminal', 'fas fa-ticket-alt', 'fas fa-times-circle',
    'fas fa-train', 'fas fa-tree', 'fas fa-trophy', 'fas fa-truck', 'fas fa-tv',
    'fas fa-umbrella', 'fas fa-university', 'fas fa-unlock', 'fas fa-upload', 'fas fa-user',
    'fas fa-video', 'fas fa-volume-up', 'fas fa-walking', 'fas fa-wallet', 'fas fa-wifi',
    'fas fa-wrench', 'fas fa-yen-sign'
];

require __DIR__ . '/partials/header.php';
?>

<div class="card bg-soft border-light shadow-soft p-4 mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <p class="mb-0 text-muted">Tienes <?= count($values) ?> valores registrados.</p>
    </div>
    <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#modal-value-create">
      <span class="fas fa-plus mr-2"></span> Nuevo Valor
    </button>
  </div>
</div>

<div class="row">
  <?php foreach ($values as $v): ?>
    <div class="col-12 col-md-4 mb-4">
      <div class="card bg-soft border-light shadow-soft h-100 p-3">
        <div class="card-body text-center">
          <div class="icon-shape icon-shape-primary rounded-circle mb-4 mx-auto" style="width: 3rem; height: 3rem; display: flex; align-items: center; justify-content: center; box-shadow: inset 2px 2px 5px #b8b9be, inset -3px -3px 7px #ffffff;">
            <span class="<?= e($v['icon']) ?>"></span>
          </div>
          <h3 class="h5 mb-2"><?= e($v['title']) ?></h3>
          <p class="small text-muted mb-4"><?= e($v['description']) ?></p>
          <div class="d-flex justify-content-between align-items-center mt-auto">
            <span class="badge badge-primary text-dark">Orden: <?= (int)$v['sort_order'] ?></span>
            <div class="btn-group">
              <button class="btn btn-primary btn-sm js-edit-value" 
                      data-id="<?= $v['id'] ?>"
                      data-title="<?= e($v['title']) ?>"
                      data-description="<?= e($v['description']) ?>"
                      data-icon="<?= e($v['icon']) ?>"
                      data-sort="<?= $v['sort_order'] ?>"
                      title="Editar">
                <span class="fas fa-edit"></span>
              </button>
              <form method="post" onsubmit="return confirm('¿Eliminar este valor?');" class="d-inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $v['id'] ?>">
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
<div class="modal fade" id="modal-value-create" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content shadow-soft">
            <div class="modal-header border-0">
                <h2 class="h6 modal-title mb-0">Nuevo Valor</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="create">
                    <div class="form-group">
                        <label for="title">Título del Valor</label>
                        <input id="title" name="title" class="form-control" required placeholder="Ej. Integridad">
                    </div>
                    <div class="form-group">
                        <label for="description">Descripción corta</label>
                        <textarea id="description" name="description" class="form-control" rows="3" required placeholder="Breve explicación..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label>Selecciona un Icono</label>
                                <input type="text" class="form-control mb-2 icon-search-input" placeholder="Escribe para buscar... (ej: build, shield, award)" onkeyup="filterIcons(this, '#create-icon-picker')">
                                <input type="hidden" name="icon" id="create-icon-input" value="fas fa-gem">
                                <div class="icon-picker-container" id="create-icon-picker">
                                    <?php foreach ($allIcons as $index => $icon): ?>
                                        <div class="icon-option <?= $icon === 'fas fa-gem' ? 'active' : '' ?>" 
                                             data-icon="<?= $icon ?>" 
                                             title="<?= $icon ?>"
                                             <?= $index > 19 ? 'style="display:none;"' : '' ?>>
                                             <i class="<?= $icon ?>"></i>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <small class="text-muted mt-2 d-block">Mostrando iconos destacados. Usa el buscador para ver más.</small>
                            </div>
                        </div>
                        <div class="col-md-5 text-center border-left border-light">
                            <label class="d-block">Vista Previa</label>
                            <div class="icon-shape icon-shape-primary rounded-circle mx-auto mt-4" style="width: 5rem; height: 5rem; font-size: 2rem;">
                                <i id="create-icon-preview" class="fas fa-gem"></i>
                            </div>
                            <div class="form-group mt-4 px-3">
                                <label for="sort_order">Orden de aparición</label>
                                <input type="number" name="sort_order" id="sort_order" class="form-control" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 text-right">
                        <button type="button" class="btn btn-primary text-danger mr-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Valor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modal-value-edit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content shadow-soft">
            <div class="modal-header border-0">
                <h2 class="h6 modal-title mb-0">Editar Valor</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <?= csrf_input() ?>
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
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label>Cambiar Icono</label>
                                <input type="text" class="form-control mb-2 icon-search-input" placeholder="Buscar icono..." onkeyup="filterIcons(this, '#edit-icon-picker')">
                                <input type="hidden" name="icon" id="edit-icon-input">
                                <div class="icon-picker-container" id="edit-icon-picker">
                                    <?php foreach ($allIcons as $index => $icon): ?>
                                        <div class="icon-option" 
                                             data-icon="<?= $icon ?>" 
                                             title="<?= $icon ?>"
                                             <?= $index > 19 ? 'style="display:none;"' : '' ?>>
                                             <i class="<?= $icon ?>"></i>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 text-center border-left border-light">
                            <label class="d-block">Vista Previa</label>
                            <div class="icon-shape icon-shape-primary rounded-circle mx-auto mt-4" style="width: 5rem; height: 5rem; font-size: 2rem;">
                                <i id="edit-icon-preview"></i>
                            </div>
                            <div class="form-group mt-4 px-3">
                                <label for="edit-sort">Orden</label>
                                <input type="number" name="sort_order" id="edit-sort" class="form-control">
                            </div>
                        </div>
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
function filterIcons(input, pickerId) {
    const term = input.value.toLowerCase().trim();
    const picker = $(pickerId);
    const options = picker.find('.icon-option');
    
    if (term === '') {
        // Show only first 20 icons when search is empty
        options.each(function(index) {
            $(this).toggle(index < 20);
        });
    } else {
        // Show all matching icons
        options.each(function() {
            const icon = $(this).data('icon').toLowerCase();
            $(this).toggle(icon.includes(term));
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Handle icon selection
    $(document).on('click', '.icon-option', function() {
        const icon = $(this).data('icon');
        const container = $(this).closest('.modal-body');
        
        container.find('.icon-option').removeClass('active');
        $(this).addClass('active');
        
        container.find('input[name="icon"]').val(icon);
        container.find('.icon-shape i').attr('class', icon);
    });

    $('.js-edit-value').on('click', function() {
        const btn = $(this);
        const icon = btn.data('icon') || 'fas fa-gem';
        
        $('#edit-id').val(btn.data('id'));
        $('#edit-title').val(btn.data('title'));
        $('#edit-description').val(btn.data('description'));
        $('#edit-sort').val(btn.data('sort'));
        
        // Setup Icon Picker in Edit
        $('#edit-icon-input').val(icon);
        $('#edit-icon-preview').attr('class', icon);
        
        // Reset picker to default state before opening
        const picker = $('#edit-icon-picker');
        const options = picker.find('.icon-option');
        options.removeClass('active').hide();
        
        // Show the active icon and first 19 others
        let count = 0;
        options.each(function() {
            const current = $(this).data('icon');
            if (current === icon) {
                $(this).addClass('active').show();
            } else if (count < 19) {
                $(this).show();
                count++;
            }
        });
        
        $('#modal-value-edit').modal('show');
    });
});
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
