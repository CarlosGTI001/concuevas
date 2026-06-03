<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM quote_requests WHERE id = :id');
        $stmt->execute(['id' => (int) $_POST['id']]);
        redirect(app_url('admin/quotes'));
    } elseif ($action === 'reply') {
        $to = trim((string) ($_POST['email'] ?? ''));
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));
        $name = trim((string) ($_POST['client_name'] ?? ''));
        $isAjax = !empty($_POST['is_ajax']);

        if ($to !== '' && $body !== '') {
            $success = send_mail($to, $subject, $body, ['name' => $name]);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => $success ? 'success' : 'error']);
                exit;
            }
        } elseif ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos']);
            exit;
        }
        
        redirect(app_url('admin/quotes'));
    }
}

$adminTitle = 'Cotizaciones';
$adminSubtitle = 'Revisa, filtra y gestiona solicitudes recibidas desde la web.';
$query = trim((string) ($_GET['q'] ?? ''));
$type = trim((string) ($_GET['type'] ?? ''));
$where = [];
$params = [];

if ($query !== '') {
    $where[] = '(name LIKE :q OR email LIKE :q OR message LIKE :q)';
    $params['q'] = '%' . $query . '%';
}
if ($type !== '') {
    $where[] = 'project_type = :type';
    $params['type'] = $type;
}

$sql = 'SELECT * FROM quote_requests';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY created_at DESC';

$stmtQuotes = db()->prepare($sql);
$stmtQuotes->execute($params);
$quotes = $stmtQuotes->fetchAll();
$quoteTypes = db()->query('SELECT DISTINCT project_type FROM quote_requests WHERE project_type <> "" ORDER BY project_type ASC')->fetchAll();
$totalQuotes = (int) db()->query('SELECT COUNT(*) FROM quote_requests')->fetchColumn();
require __DIR__ . '/partials/header.php';
?>
<div class="card bg-soft border-light shadow-soft p-4 mb-4">
  <form method="get" class="row align-items-center">
    <div class="col-12 col-md-5 mb-3 mb-md-0">
      <input type="text" name="q" class="form-control" value="<?= e($query) ?>" placeholder="Buscar solicitudes...">
    </div>
    <div class="col-12 col-md-3 mb-3 mb-md-0">
      <select name="type" class="custom-select">
        <option value="">Todos los tipos</option>
        <?php foreach ($quoteTypes as $option): ?>
          <option value="<?= e((string) $option['project_type']) ?>" <?= $type === (string) $option['project_type'] ? 'selected' : '' ?>><?= e((string) $option['project_type']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12 col-md-4 text-md-right">
      <button class="btn btn-primary btn-sm" type="submit">Filtrar</button>
      <?php if ($query !== '' || $type !== ''): ?><a class="btn btn-primary btn-sm ml-2" href="<?= e(app_url('admin/quotes.php')) ?>">Limpiar</a><?php endif; ?>
      <p class="mb-0 mt-2 text-muted">Mostrando <?= count($quotes) ?> de <?= $totalQuotes ?> solicitudes.</p>
    </div>
  </form>
</div>

<div class="card bg-soft border-light shadow-soft p-4">
  <?php if ($quotes): ?>
    <div class="table-responsive">
      <table class="table table-hover border-light table-clickable">
        <thead><tr><th class="border-0">Nombre</th><th class="border-0">Contacto</th><th class="border-0">Tipo</th><th class="border-0">Fecha</th><th class="border-0">Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($quotes as $q): ?>
        <tr class="js-view-quote" 
            data-name="<?= e($q['name']) ?>" 
            data-email="<?= e($q['email'] ?: 'No proporcionado') ?>" 
            data-phone="<?= e($q['phone']) ?>" 
            data-type="<?= e($q['project_type']) ?>" 
            data-message="<?= e($q['message']) ?>" 
            data-date="<?= e($q['created_at']) ?>">
          <td><?= e($q['name']) ?></td>
          <td>
            <?php if ($q['email']): ?><div class="small text-info"><?= e($q['email']) ?></div><?php endif; ?>
            <div class="small text-muted"><?= e($q['phone']) ?></div>
          </td>
          <td><span class="badge badge-primary text-dark"><?= e($q['project_type']) ?></span></td>
          <td class="small"><?= date('d/m/Y H:i', strtotime((string)$q['created_at'])) ?></td>
          <td class="text-right">
            <form method="post" onsubmit="return confirm('¿Eliminar solicitud?');" class="d-inline">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int) $q['id'] ?>">
              <button class="btn btn-danger btn-sm" type="submit" title="Eliminar">
                <span class="fas fa-trash-alt"></span>
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-muted text-center py-4">No hay cotizaciones con los filtros actuales.</p>
  <?php endif; ?>
</div>

<!-- Modal View Quote -->
<div class="modal fade" id="modal-view-quote" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content shadow-soft">
            <div class="modal-header border-0">
                <h2 class="h6 modal-title mb-0">Detalles de la Solicitud</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="small font-weight-bold text-muted uppercase">Nombre Cliente</label>
                        <p class="h5" id="view-name"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="small font-weight-bold text-muted uppercase">Fecha de Solicitud</label>
                        <p id="view-date"></p>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="small font-weight-bold text-muted uppercase">Correo Electrónico</label>
                        <p id="view-email"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="small font-weight-bold text-muted uppercase">Teléfono</label>
                        <p id="view-phone"></p>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="small font-weight-bold text-muted uppercase">Tipo de Proyecto</label>
                    <div><span class="badge badge-primary text-dark" id="view-type"></span></div>
                </div>
                <div class="card bg-soft border-light shadow-inset-soft p-3 mb-4">
                    <label class="small font-weight-bold text-muted uppercase">Mensaje / Requerimientos</label>
                    <p class="mb-0 mt-2" id="view-message" style="white-space: pre-wrap;"></p>
                </div>

                <!-- Reply Section -->
                <div id="reply-section" class="d-none mt-5 pt-4 border-top border-light">
                    <h3 class="h6 mb-4"><span class="fas fa-reply mr-2"></span> Responder por Correo</h3>
                    <form id="form-reply-email">
                        <?= csrf_input() ?>
                        <input type="hidden" name="action" value="reply">
                        <input type="hidden" name="email" id="reply-email">
                        <input type="hidden" name="client_name" id="reply-name">
                        <input type="hidden" name="is_ajax" value="1">
                        <div class="form-group">
                            <label for="reply-subject">Asunto</label>
                            <input type="text" name="subject" id="reply-subject" class="form-control" value="Respuesta a su solicitud de cotización - Construcciones Cuevas">
                        </div>
                        <div class="form-group">
                            <label for="reply-body">Mensaje de respuesta</label>
                            <textarea name="body" id="reply-body" class="form-control" rows="5" placeholder="Escribe tu respuesta aquí..." required></textarea>
                        </div>
                        <div class="text-right">
                            <button type="submit" id="btn-send-reply" class="btn btn-primary btn-sm">
                                <span class="btn-text">Enviar Respuesta</span>
                                <span class="spinner-border spinner-border-sm d-none ml-2" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-4 text-right" id="modal-footer-btns">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('.js-view-quote').on('click', function(e) {
        // Don't open modal if clicking on delete button or its icon
        if ($(e.target).closest('button, form').length) return;

        const data = $(this).data();
        const clientName = $(this).attr('data-name'); // More robust extraction
        $('#view-name').text(clientName);
        $('#view-email').text(data.email);
        $('#view-phone').text(data.phone);
        $('#view-type').text(data.type);
        $('#view-message').text(data.message);
        $('#view-date').text(data.date);
        
        // Setup Reply Section
        const email = data.email && data.email !== 'No proporcionado' ? data.email : '';
        if (email) {
            $('#reply-section').removeClass('d-none');
            $('#reply-email').val(email);
            $('#reply-name').val(data.name);
        } else {
            $('#reply-section').addClass('d-none');
        }

        $('#modal-view-quote').modal('show');
    });

    // AJAX form submission for email reply
    const replyForm = document.getElementById('form-reply-email');
    if (replyForm) {
        replyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-send-reply');
            const btnText = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.spinner-border');
            
            // UI Loading state
            btn.disabled = true;
            btnText.textContent = 'Enviando...';
            spinner.classList.remove('d-none');

            const formData = new FormData(replyForm);

            fetch('<?= e(app_url('admin/quotes.php')) ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Success state
                    btnText.textContent = '¡Enviado!';
                    btn.classList.replace('btn-primary', 'btn-success');
                    spinner.classList.add('d-none');
                    
                    setTimeout(() => {
                        $('#modal-view-quote').modal('hide');
                        // Reset button
                        btn.disabled = false;
                        btnText.textContent = 'Enviar Respuesta';
                        btn.classList.replace('btn-success', 'btn-primary');
                        replyForm.reset();
                    }, 1500);
                } else {
                    throw new Error(data.message || 'Error al enviar');
                }
            })
            .catch(error => {
                alert('No se pudo enviar el correo: ' + error.message);
                // Reset button on error
                btn.disabled = false;
                btnText.textContent = 'Enviar Respuesta';
                spinner.classList.add('d-none');
            });
        });
    }
});
</script>
<?php require __DIR__ . '/partials/footer.php'; ?>
