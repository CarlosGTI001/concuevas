<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
require_login(false);

if (!must_change_credentials()) {
    redirect(app_url('admin/index.php'));
}

$adminId = current_admin_id();
$stmt = db()->prepare('SELECT email FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $adminId]);
$currentUser = $stmt->fetch();

if (!$currentUser) {
    logout();
    redirect(app_url('admin/login.php'));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresa un email válido.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'La confirmación de contraseña no coincide.';
    } else {
        $emailCheck = db()->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
        $emailCheck->execute(['email' => $email, 'id' => $adminId]);
        if ($emailCheck->fetch()) {
            $error = 'Ese email ya está en uso por otro usuario.';
        } else {
            complete_credential_rotation($adminId, $email, $password);
            redirect(app_url('admin/index.php'));
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Actualizar credenciales | Admin</title>

  <!-- Fontawesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link type="text/css" href="<?= e(app_url('vendor/@fortawesome/fontawesome-free/css/all.min.css')) ?>" rel="stylesheet">

  <!-- Pixel CSS -->
  <link type="text/css" href="<?= e(app_url('css/neumorphism.css')) ?>?v=<?= APP_VERSION ?>" rel="stylesheet">

  <link rel="stylesheet" href="<?= e(app_url('styles.css')) ?>?v=<?= APP_VERSION ?>">
</head>
<body class="bg-soft">
  <main class="d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-7">
          <div class="card bg-soft shadow-soft border-light p-4 p-lg-5">
            <div class="row align-items-center">
              <div class="col-12 col-md-5 text-center mb-4 mb-md-0">
                <div class="shadow-inset-soft p-4 rounded-circle border border-light d-inline-block mb-3">
                  <img src="https://www.concuevas.com/uploads/settings/site-logo-5cfaf6.png" width="100" height="100" alt="Logo" style="object-fit: contain;">
                </div>
                <h1 class="h4 mb-2">CMS Cuevas</h1>
                <p class="text-muted small">Seguridad del Panel</p>
              </div>
              <div class="col-12 col-md-7 border-left-lg border-light pl-lg-5">
                <div class="mb-4">
                  <span class="badge badge-primary mb-2">Cambio Obligatorio</span>
                  <h2 class="h5">Actualizar Acceso</h2>
                  <p class="text-muted small">Por seguridad, actualiza tus credenciales predeterminadas antes de continuar.</p>
                </div>

                <?php if ($error !== ''): ?>
                  <div class="alert alert-danger shadow-inset-soft mb-4 small">
                    <span class="fas fa-exclamation-circle mr-2"></span> <?= e($error) ?>
                  </div>
                <?php endif; ?>

                <form method="post">
                  <div class="form-group mb-4">
                    <label for="email" class="small font-weight-bold">Nuevo Email</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text shadow-inset-soft"><span class="fas fa-envelope"></span></span>
                      </div>
                      <input id="email" name="email" class="form-control shadow-inset-soft border-0" type="email" value="<?= e((string) $currentUser['email']) ?>" required>
                    </div>
                  </div>

                  <div class="form-group mb-4">
                    <label for="password" class="small font-weight-bold">Nueva Contraseña</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text shadow-inset-soft"><span class="fas fa-unlock-alt"></span></span>
                      </div>
                      <input id="password" name="password" class="form-control shadow-inset-soft border-0" type="password" placeholder="Mínimo 8 caracteres" minlength="8" required>
                    </div>
                  </div>

                  <div class="form-group mb-4">
                    <label for="password_confirm" class="small font-weight-bold">Confirmar Contraseña</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text shadow-inset-soft"><span class="fas fa-check-double"></span></span>
                      </div>
                      <input id="password_confirm" name="password_confirm" class="form-control shadow-inset-soft border-0" type="password" placeholder="Repite la contraseña" minlength="8" required>
                    </div>
                  </div>

                  <div class="mt-5">
                    <button class="btn btn-primary btn-block shadow-soft border-0" type="submit">Guardar y Entrar al Panel</button>
                  </div>
                </form>
                
                <div class="mt-4 text-center">
                  <a href="<?= e(app_url('admin/logout.php')) ?>" class="small text-muted"><span class="fas fa-sign-out-alt mr-2"></span> Cerrar sesión</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Core Scripts -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
