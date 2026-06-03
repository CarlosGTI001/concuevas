<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

$adminTitle = 'Estructura de la Home';
$adminSubtitle = 'Organiza el orden de las secciones de tu página de inicio arrastrando los elementos.';

$sections = db()->query('SELECT * FROM page_sections ORDER BY sort_order ASC')->fetchAll();

require __DIR__ . '/partials/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card bg-soft border-light shadow-soft p-4 mb-4">
            <div class="d-flex align-items-center mb-4">
                <div class="icon-shape icon-shape-primary rounded-circle mr-3">
                    <span class="fas fa-layer-group"></span>
                </div>
                <div>
                    <h2 class="h5 mb-0">Secciones de la Página de Inicio</h2>
                    <p class="small text-muted mb-0">El orden aquí se refleja directamente en la web pública.</p>
                </div>
            </div>

            <div id="homepage-sections" class="list-group">
                <?php foreach ($sections as $s): ?>
                    <div class="list-group-item bg-soft border-light mb-3 shadow-inset-soft rounded p-3 d-flex align-items-center justify-content-between" 
                         data-id="<?= $s['id'] ?>" 
                         style="cursor: move;">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-grip-vertical mr-3 text-muted"></span>
                            <div>
                                <h3 class="h6 mb-0"><?= e($s['title']) ?></h3>
                                <code class="small text-muted">partials/homepage/<?= e($s['section_key']) ?>.php</code>
                            </div>
                        </div>
                        <div>
                            <?php if ($s['is_active']): ?>
                                <span class="badge badge-success text-white">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Oculto</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="save-status" class="mt-4 text-center" style="display:none;">
                <div class="alert alert-success">
                    <span class="fas fa-check-circle mr-2"></span> ¡Orden guardado correctamente!
                </div>
            </div>
        </div>
        
        <div class="alert alert-info shadow-soft border-light p-4">
            <div class="d-flex">
                <span class="fas fa-info-circle mt-1 mr-3"></span>
                <p class="mb-0 small">
                    <strong>¿Cómo funciona?</strong> Simplemente arrastra cada sección hacia arriba o hacia abajo. 
                    El sistema guardará el nuevo orden automáticamente vía AJAX. No necesitas recargar la página.
                </p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('homepage-sections');
    const status = document.getElementById('save-status');
    
    new Sortable(el, {
        animation: 150,
        ghostClass: 'bg-primary-light',
        onEnd: function() {
            let order = [];
            document.querySelectorAll('#homepage-sections .list-group-item').forEach((item, index) => {
                order.push({
                    id: item.dataset.id,
                    position: index + 1
                });
            });

            // Send to backend
            fetch('<?= app_url('includes/reorder_handler.php') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    type: 'page_sections',
                    order: order
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    status.style.display = 'block';
                    setTimeout(() => {
                        status.style.display = 'none';
                    }, 2000);
                } else {
                    alert('Error al guardar el orden: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error de red.');
            });
        }
    });
});
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
