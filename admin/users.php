<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

$notice = '';
$noticeType = 'success';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        die('CSRF token validation failed.');
    }
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
        $mustChange = isset($_POST['must_change']) ? 1 : 0;

        if ($name === '') {
            $error = 'El nombre es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Ingresa un email válido.';
        } elseif (strlen($password) < 8) {
            $error = 'La contraseña debe tener al menos 8 caracteres.';
        } elseif ($password !== $passwordConfirm) {
            $error = 'La confirmación de contraseña no coincide.';
        } else {
            $emailCheck = db()->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $emailCheck->execute(['email' => $email]);
            if ($emailCheck->fetch()) {
                $error = 'Ese email ya está en uso por otro usuario.';
            } else {
                $stmt = db()->prepare(
                    'INSERT INTO users (name, email, password_hash, must_change_credentials, created_at)
                     VALUES (:name, :email, :password_hash, :must_change, NOW())'
                );
                $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'must_change' => $mustChange,
                ]);
                redirect(app_url('admin/users?status=updated'));
            }
        }
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
        $mustChange = isset($_POST['must_change']) ? 1 : 0;

        if ($id <= 0) {
            $error = 'No se encontró el usuario a editar.';
        } elseif ($name === '') {
            $error = 'El nombre es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Ingresa un email válido.';
        } elseif ($password !== '' && strlen($password) < 8) {
            $error = 'La nueva contraseña debe tener al menos 8 caracteres.';
        } elseif ($password !== '' && $password !== $passwordConfirm) {
            $error = 'La confirmación de contraseña no coincide.';
        } else {
            $emailCheck = db()->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
            $emailCheck->execute(['email' => $email, 'id' => $id]);
            if ($emailCheck->fetch()) {
                $error = 'Ese email ya está en uso por otro usuario.';
            } else {
                if ($password !== '') {
                    $stmt = db()->prepare(
                        'UPDATE users
                         SET name = :name, email = :email, password_hash = :password_hash, must_change_credentials = :must_change
                         WHERE id = :id'
                    );
                    $stmt->execute([
                        'id' => $id,
                        'name' => $name,
                        'email' => $email,
                        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        'must_change' => $mustChange,
                    ]);
                } else {
                    $stmt = db()->prepare(
                        'UPDATE users
                         SET name = :name, email = :email, must_change_credentials = :must_change
                         WHERE id = :id'
                    );
                    $stmt->execute([
                        'id' => $id,
                        'name' => $name,
                        'email' => $email,
                        'must_change' => $mustChange,
                    ]);
                }
                redirect(app_url('admin/users?status=updated'));
            }
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $currentId = current_admin_id();
        $totalUsers = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();

        if ($id <= 0) {
            $error = 'No se encontró el usuario a eliminar.';
        } elseif ($id === $currentId) {
            $error = 'No puedes eliminar tu propia cuenta.';
        } elseif ($totalUsers <= 1) {
            $error = 'Debe existir al menos un usuario administrador.';
        } else {
            $stmt = db()->prepare('DELETE FROM users WHERE id = :id');
            $stmt->execute(['id' => $id]);
            redirect(app_url('admin/users?status=updated'));
        }
    }
}

$status = trim((string) ($_GET['status'] ?? ''));
if ($status === 'created') {
    $notice = 'Usuario creado correctamente.';
} elseif ($status === 'updated') {
    $notice = 'Usuario actualizado correctamente.';
} elseif ($status === 'deleted') {
    $notice = 'Usuario eliminado correctamente.';
}

$query = trim((string) ($_GET['q'] ?? ''));
if ($query !== '') {
    $stmt = db()->prepare('SELECT id, name, email, must_change_credentials, created_at FROM users WHERE name LIKE :q OR email LIKE :q ORDER BY created_at DESC');
    $stmt->execute(['q' => '%' . $query . '%']);
    $users = $stmt->fetchAll();
} else {
    $users = db()->query('SELECT id, name, email, must_change_credentials, created_at FROM users ORDER BY created_at DESC')->fetchAll();
}

$totalUsers = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$adminTitle = 'Usuarios';
$adminSubtitle = 'Crea y edita accesos al panel administrativo.';

require __DIR__ . '/partials/header.php';
?>
<div class="card bg-soft border-light shadow-soft p-4 mb-4">
  <div class="row align-items-center">
    <div class="col-12 col-md-6 mb-3 mb-md-0">
      <form method="get" class="d-flex">
        <input type="text" name="q" class="form-control mr-2" value="<?= e($query) ?>" placeholder="Buscar usuarios...">
        <button class="btn btn-primary btn-sm" type="submit">Buscar</button>
        <?php if ($query !== ''): ?><a class="btn btn-primary btn-sm ml-2" href="<?= e(app_url('admin/users')) ?>">Limpiar</a><?php endif; ?>
      </form>
    </div>
    <div class="col-12 col-md-6 text-md-right">
      <p class="mb-2 text-muted">Mostrando <?= count($users) ?> de <?= $totalUsers ?> usuarios.</p>
      <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#modal-user-create">
        <span class="fas fa-user-plus mr-2"></span> Nuevo usuario
      </button>
    </div>
  </div>
</div>

<?php if ($notice !== ''): ?>
  <div class="alert alert-success shadow-inset-soft mb-4" role="alert">
    <span class="fas fa-check-circle mr-2"></span><?= e($notice) ?>
  </div>
<?php endif; ?>
<?php if ($error !== ''): ?>
  <div class="alert alert-danger shadow-inset-soft mb-4" role="alert">
    <span class="fas fa-exclamation-circle mr-2"></span><?= e($error) ?>
  </div>
<?php endif; ?>

<div class="card bg-soft border-light shadow-soft p-4">
  <h2 class="h5 mb-4">Listado de usuarios</h2>
  <?php if ($users): ?>
    <div class="table-responsive">
      <table class="table table-hover border-light">
        <thead>
          <tr>
            <th class="border-0">Nombre</th>
            <th class="border-0">Email</th>
            <th class="border-0">Estado</th>
            <th class="border-0">Alta</th>
            <th class="border-0 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
          <tr>
            <td><?= e($user['name']) ?></td>
            <td><?= e($user['email']) ?></td>
            <td>
              <?php if ((int) $user['must_change_credentials'] === 1): ?>
                <span class="badge badge-warning">Debe actualizar credenciales</span>
              <?php else: ?>
                <span class="badge badge-primary text-dark">Activo</span>
              <?php endif; ?>
            </td>
            <td class="small"><?= e($user['created_at']) ?></td>
            <td class="text-right">
              <div class="d-flex justify-content-end">
                <button class="btn btn-primary btn-sm mr-2 js-edit-user"
                        data-id="<?= (int) $user['id'] ?>"
                        data-name="<?= e($user['name']) ?>"
                        data-email="<?= e($user['email']) ?>"
                        data-must-change="<?= (int) $user['must_change_credentials'] ?>"
                        title="Editar">
                  <span class="fas fa-edit"></span>
                </button>
                <?php if ((int) $user['id'] !== current_admin_id()): ?>
                  <form method="post" onsubmit="return confirm('¿Eliminar este usuario?');">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                    <button class="btn btn-danger btn-sm" type="submit" title="Eliminar">
                      <span class="fas fa-trash-alt"></span>
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-muted text-center py-4">No hay usuarios registrados.</p>
  <?php endif; ?>
</div>

<!-- Modal Create -->
<div class="modal fade" id="modal-user-create" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content shadow-soft">
      <div class="modal-header border-0">
        <h2 class="h6 modal-title mb-0">Nuevo usuario</h2>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="post">
          <?= csrf_input() ?>
          <input type="hidden" name="action" value="create">
          <div class="form-group">
            <label for="create-name">Nombre</label>
            <input id="create-name" name="name" class="form-control" required placeholder="Ej. Ana Gómez">
          </div>
          <div class="form-group">
            <label for="create-email">Email</label>
            <input id="create-email" name="email" class="form-control" type="email" required placeholder="usuario@correo.com">
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="create-password">Contraseña</label>
                <input id="create-password" name="password" class="form-control" type="password" minlength="8" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="create-password-confirm">Confirmar contraseña</label>
                <input id="create-password-confirm" name="password_confirm" class="form-control" type="password" minlength="8" required>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="custom-control custom-checkbox">
              <input class="custom-control-input" id="create-must-change" type="checkbox" name="must_change" checked>
              <label class="custom-control-label" for="create-must-change">Forzar cambio de credenciales al iniciar sesión</label>
            </div>
          </div>
          <div class="mt-4 text-right">
            <button type="button" class="btn btn-primary text-danger mr-2" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar usuario</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modal-user-edit" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content shadow-soft">
      <div class="modal-header border-0">
        <h2 class="h6 modal-title mb-0">Editar usuario</h2>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="post">
          <?= csrf_input() ?>
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" id="edit-id">
          <div class="form-group">
            <label for="edit-name">Nombre</label>
            <input id="edit-name" name="name" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="edit-email">Email</label>
            <input id="edit-email" name="email" class="form-control" type="email" required>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit-password">Nueva contraseña (opcional)</label>
                <input id="edit-password" name="password" class="form-control" type="password" minlength="8">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit-password-confirm">Confirmar contraseña</label>
                <input id="edit-password-confirm" name="password_confirm" class="form-control" type="password" minlength="8">
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="custom-control custom-checkbox">
              <input class="custom-control-input" id="edit-must-change" type="checkbox" name="must_change">
              <label class="custom-control-label" for="edit-must-change">Forzar cambio de credenciales al iniciar sesión</label>
            </div>
          </div>
          <div class="mt-4 text-right">
            <button type="button" class="btn btn-primary text-danger mr-2" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Actualizar usuario</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  $('.js-edit-user').on('click', function() {
    const data = $(this).data();
    $('#edit-id').val(data.id);
    $('#edit-name').val(data.name);
    $('#edit-email').val(data.email);
    $('#edit-must-change').prop('checked', parseInt(data.mustChange, 10) === 1);
    $('#edit-password').val('');
    $('#edit-password-confirm').val('');
    $('#modal-user-edit').modal('show');
  });
});
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
?>
