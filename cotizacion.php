<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$currentPage = 'contacto';
$pageTitle = 'Cotización | ' . site_name();
$ok = false;

// Fetch services from DB for the dropdown
$services = db()->query('SELECT name FROM services ORDER BY sort_order ASC, name ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        die('CSRF token validation failed.');
    }
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $projectType = trim((string) ($_POST['project_type'] ?? ''));
    $otherType = trim((string) ($_POST['other_type'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));

    // If "Otro" is selected, use the custom text
    if ($projectType === 'Otro' && $otherType !== '') {
        $finalType = $otherType;
    } else {
        $finalType = $projectType;
    }

    // Basic validation (Name, Phone, and Message are strictly required; Email is optional)
    if ($name !== '' && $phone !== '' && $message !== '') {
        $stmt = db()->prepare('INSERT INTO quote_requests (name, email, phone, project_type, message, created_at) VALUES (:name, :email, :phone, :type, :message, NOW())');
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'type' => $finalType,
            'message' => $message,
        ]);
        
        // Automated Confirmation Email
        if ($email !== '') {
            $confirmSubject = 'Confirmación de solicitud de cotización - ' . site_name();
            $confirmMessage = "Hemos recibido tu solicitud de cotización. Un asesor técnico se pondrá en contacto contigo a la brevedad a través de este correo o al número proporcionado ({$phone}).";
            
            send_mail($email, $confirmSubject, $confirmMessage, [
                'name' => $name,
                'project_type' => $finalType,
                'message' => $message
            ]);
        }

        $ok = true;
    }
}

require __DIR__ . '/partials/header.php';
?>
<section class="section section bg-soft pb-5 overflow-hidden z-2">
  <div class="container z-2">
    <div class="row justify-content-center text-center pt-6">
      <div class="col-lg-8 col-xl-8">
        <h1 class="display-2 mb-3">Solicitud de Cotización</h1>
        <p class="lead px-md-6">Platícanos tu idea y nuestro equipo de expertos te contactará a la brevedad.</p>
      </div>
    </div>
  </div>
</section>

<section class="section section-lg">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10">
        <div class="card bg-soft border-light shadow-soft p-4 p-md-5">
          <h2 class="h3 mb-4 text-center">Formulario de contacto</h2>
          
          <?php if ($ok): ?>
            <div class="alert alert-success shadow-inset-soft mb-4" role="alert">
              <span class="alert-inner--icon"><i class="fas fa-check-circle"></i></span>
              <span class="alert-inner--text pl-2">Gracias. Tu solicitud ha sido enviada y guardada correctamente.</span>
            </div>
          <?php endif; ?>

          <form action="<?= e(app_url('cotizacion')) ?>" method="post">
            <?= csrf_input() ?>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name">Nombre completo <span class="text-danger">*</span></label>
                  <input id="name" name="name" class="form-control" type="text" placeholder="Ej. Juan Pérez" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="phone">Teléfono <span class="text-danger">*</span></label>
                  <input id="phone" name="phone" class="form-control" type="text" placeholder="Ej. +52 123 456 7890" required>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="email">Correo electrónico (Opcional)</label>
              <input id="email" name="email" class="form-control" type="email" placeholder="ejemplo@correo.com">
            </div>

            <div class="form-group">
              <label for="project_type">Tipo de proyecto</label>
              <select id="project_type" name="project_type" class="custom-select" onchange="toggleOtherField(this.value)">
                <option value="" selected disabled>Selecciona una opción</option>
                <?php foreach ($services as $service): ?>
                  <option value="<?= e($service['name']) ?>"><?= e($service['name']) ?></option>
                <?php endforeach; ?>
                <option value="Otro">Otro...</option>
              </select>
            </div>

            <div class="form-group d-none" id="other-type-group">
              <label for="other_type">Especifica el tipo de proyecto</label>
              <input id="other_type" name="other_type" class="form-control" type="text" placeholder="¿En qué podemos ayudarte?">
            </div>

            <div class="form-group">
              <label for="message">Descripción del proyecto <span class="text-danger">*</span></label>
              <textarea id="message" name="message" class="form-control" rows="4" placeholder="Cuéntanos más detalles sobre lo que necesitas..." required></textarea>
            </div>

            <div class="mt-4">
              <button class="btn btn-primary shadow-soft" type="submit">Enviar solicitud</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
function toggleOtherField(value) {
    const otherGroup = document.getElementById('other-type-group');
    const otherInput = document.getElementById('other_type');
    if (value === 'Otro') {
        otherGroup.classList.remove('d-none');
        otherInput.focus();
    } else {
        otherGroup.classList.add('d-none');
    }
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
