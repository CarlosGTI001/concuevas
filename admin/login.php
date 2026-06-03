<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

if (is_logged_in()) {
    redirect(app_url('admin/index.php'));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if (!attempt_login($email, $password)) {
        $error = 'Credenciales inválidas.';
    } else {
        if (must_change_credentials()) {
            redirect(app_url('admin/force_credentials.php'));
        }
        redirect(app_url('admin/index.php'));
    }
}

function setting_value(string $key, string $default = ''): string
{
    $stmt = db()->prepare('SELECT `value` FROM site_settings WHERE `key` = :key LIMIT 1');
    $stmt->execute(['key' => $key]);
    $value = $stmt->fetchColumn();
    return $value !== false ? (string) $value : $default;
}

$loginImage = setting_value('login_image', 'https://images.unsplash.com/photo-1541888946425-d81bb19480c5?auto=format&fit=crop&w=800&q=80');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin | Construcciones Cuevas</title>

  <!-- Fontawesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <!-- Pixel CSS -->
  <link type="text/css" href="<?= e(app_url('css/neumorphism.css')) ?>?v=<?= APP_VERSION ?>" rel="stylesheet">

  <link rel="stylesheet" href="<?= e(app_url('styles.css')) ?>?v=<?= APP_VERSION ?>">
  <style>
    .login-page {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }
    .login-card-wrap {
      max-width: 900px;
      width: 100%;
    }
    .login-side-img {
      background-image: url('<?= e($loginImage) ?>');
      background-size: cover;
      background-position: center;
      min-height: 400px;
      border-radius: 1rem 0 0 1rem;
    }
    @media (max-width: 767.98px) {
      .login-side-img {
        border-radius: 1rem 1rem 0 0;
        min-height: 200px;
      }
      .login-form-side {
        border-radius: 0 0 1rem 1rem;
      }
    }
  </style>
</head>
<body class="bg-soft">
  <div class="login-page">
    <div class="login-card-wrap card bg-soft shadow-soft border-light">
      <div class="row no-gutters">
        <div class="col-md-6 login-side-img d-none d-md-block shadow-inset-soft">
          <!-- Side image from CMS -->
        </div>
        <div class="col-md-6 login-form-side p-4 p-lg-5">
          <div class="text-center mb-4">
            <div class="shadow-soft p-3 rounded-circle border border-light d-inline-block mb-3">
               <img src="<?= e(app_url('assets/img/brand/dark.svg')) ?>" width="40" height="40" alt="Logo">
            </div>
            <h1 class="h4">Panel de Control</h1>
            <p class="text-muted small">Ingresa tus credenciales para continuar</p>
          </div>

          <?php if ($error !== ''): ?>
            <div class="alert alert-danger shadow-inset-soft mb-4 small text-center">
              <span class="fas fa-exclamation-circle mr-2"></span> <?= e($error) ?>
            </div>
          <?php endif; ?>

          <form method="post">
            <div class="form-group">
              <label for="email">Email</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><span class="fas fa-envelope"></span></span>
                </div>
                <input id="email" name="email" class="form-control" type="email" placeholder="admin@cuevas.local" required>
              </div>
            </div>
            <div class="form-group">
              <label for="password">Contraseña</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><span class="fas fa-unlock-alt"></span></span>
                </div>
                <input id="password" name="password" class="form-control" type="password" placeholder="••••••••" required>
              </div>
            </div>
            <div class="mt-4">
              <button class="btn btn-primary btn-block" type="submit">Iniciar Sesión</button>
            </div>
          </form>
          
          <div class="mt-4 text-center">
            <a href="<?= e(app_url('index.php')) ?>" class="small text-muted"><span class="fas fa-arrow-left mr-2"></span> Volver al sitio público</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Core Scripts -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
