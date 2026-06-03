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
<body class="admin-body">
  <main class="admin-wrap">
    <div class="login-layout">
      <section class="login-brand">
        <p class="login-chip">Primer acceso</p>
        <h1>Cambio obligatorio de credenciales</h1>
        <p>Por seguridad debes cambiar el usuario (email) y la contraseña predeterminada antes de continuar.</p>
      </section>
      <section class="admin-card login-card">
        <h2>Actualizar acceso</h2>
        <p class="login-subtitle">Este paso solo aparece una vez para la cuenta inicial.</p>
        <?php if ($error !== ''): ?><p class="notice error"><?= e($error) ?></p><?php endif; ?>
        <form method="post">
          <label for="email">Nuevo email de acceso</label>
          <input id="email" name="email" type="email" value="<?= e((string) $currentUser['email']) ?>" required>

          <label for="password">Nueva contraseña</label>
          <input id="password" name="password" type="password" minlength="8" required>

          <label for="password_confirm">Confirmar nueva contraseña</label>
          <input id="password_confirm" name="password_confirm" type="password" minlength="8" required>

          <button class="btn" type="submit">Guardar y continuar</button>
        </form>
      </section>
    </div>
  </main>
</body>
</html>
