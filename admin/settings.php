<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowed = [
        'site_name', 'site_tagline', 'meta_description',
        'hero_title', 'hero_text',
        'about_intro', 'about_text', 'about_image', 'about_objective',
        'contact_phone', 'contact_email', 'contact_address', 'contact_image', 'contact_map_iframe',
        'site_logo', 'email_logo', 'footer_icon', 'login_image', 'cta_background_image'
    ];

    // These keys match BOTH the $_POST text input and the $_FILES input name
    $uploadKeys = ['site_logo', 'email_logo', 'footer_icon', 'login_image', 'about_image', 'contact_image', 'cta_background_image'];

    // Add dynamic slide keys if needed (though now managed in Sliders module)
    $slideTextKeys = [
        'slider_title_1', 'slider_text_1',
        'slider_title_2', 'slider_text_2',
        'slider_title_3', 'slider_text_3'
    ];
    $allowed = array_merge($allowed, $slideTextKeys);

    $stmt = db()->prepare('INSERT INTO site_settings (`key`, `value`) VALUES (:key, :value) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
    
    foreach ($allowed as $key) {
        $value = trim((string) ($_POST[$key] ?? ''));
        
        // If it's an upload key, check if a file was provided in $_FILES
        if (in_array($key, $uploadKeys, true) && isset($_FILES[$key])) {
            $uploadedUrl = handle_upload($_FILES[$key], 'settings', $key);
            if ($uploadedUrl) {
                $value = $uploadedUrl;
            }
        }
        
        $stmt->execute([
            'key' => $key,
            'value' => $value,
        ]);
    }
    redirect(app_url('admin/settings'));
}

$adminTitle = 'Ajustes';
$adminSubtitle = 'Personaliza textos globales, datos de contacto e identidad del sitio.';

function setting_value(string $key, string $default = ''): string
{
    $stmt = db()->prepare('SELECT `value` FROM site_settings WHERE `key` = :key LIMIT 1');
    $stmt->execute(['key' => $key]);
    $value = $stmt->fetchColumn();
    return $value !== false ? (string) $value : $default;
}

require __DIR__ . '/partials/header.php';
?>
<form method="post" enctype="multipart/form-data">
  <div class="row">
    <div class="col-12 col-lg-6 mb-4" id="identity-section">
      <div class="card bg-soft border-light shadow-soft p-4 h-100">
        <h2 class="h5 mb-4">Identidad y SEO</h2>
        <div class="form-group">
          <label for="site_name">Nombre del sitio</label>
          <input id="site_name" name="site_name" class="form-control" value="<?= e(setting_value('site_name', 'Construcciones Cuevas')) ?>" placeholder="Nombre del sitio">
        </div>
        <div class="form-group">
          <label for="site_tagline">Tagline</label>
          <input id="site_tagline" name="site_tagline" class="form-control" value="<?= e(setting_value('site_tagline', 'Contruccion & Arquitectura')) ?>" placeholder="Tagline">
        </div>
        <div class="form-group">
          <label for="site_logo">Logo del sitio (Navbar)</label>
          <input type="file" name="site_logo" id="site_logo" class="form-control" accept="image/*">
          <small class="text-muted d-block mt-1">URL actual: <input name="site_logo" class="form-control form-control-sm mt-1" value="<?= e(setting_value('site_logo')) ?>"></small>
        </div>
        <div class="form-group">
          <label for="email_logo">Logo para Correos</label>
          <input type="file" name="email_logo" id="email_logo" class="form-control" accept="image/*">
          <small class="text-muted d-block mt-1">URL actual: <input name="email_logo" class="form-control form-control-sm mt-1" value="<?= e(setting_value('email_logo')) ?>"></small>
        </div>
        <div class="form-group" id="footer-icon-section">
          <label for="footer_icon">Logo del Footer (Cuadrado)</label>
          <input type="file" name="footer_icon" id="footer_icon" class="form-control" accept="image/*">
          <small class="text-muted d-block mt-1">URL actual: <input name="footer_icon" class="form-control form-control-sm mt-1" value="<?= e(setting_value('footer_icon')) ?>"></small>
        </div>
        <div class="form-group">
          <label for="login_image">Imagen de Login</label>
          <input type="file" name="login_image" id="login_image" class="form-control" accept="image/*">
          <small class="text-muted d-block mt-1">URL actual: <input name="login_image" class="form-control form-control-sm mt-1" value="<?= e(setting_value('login_image')) ?>"></small>
        </div>
        <div class="form-group">
          <label for="meta_description">Meta descripción</label>
          <textarea id="meta_description" name="meta_description" class="form-control" rows="3" placeholder="Meta descripción"><?= e(setting_value('meta_description')) ?></textarea>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6 mb-4">
      <div class="card bg-soft border-light shadow-soft p-4 h-100">
        <h2 class="h5 mb-4">Contacto</h2>
        <div class="form-group">
          <label for="contact_phone">Teléfono</label>
          <input id="contact_phone" name="contact_phone" class="form-control" value="<?= e(setting_value('contact_phone')) ?>" placeholder="Teléfono">
        </div>
        <div class="form-group">
          <label for="contact_email">Email</label>
          <input id="contact_email" name="contact_email" class="form-control" value="<?= e(setting_value('contact_email')) ?>" placeholder="Email">
        </div>
        <div class="form-group">
          <label for="contact_address">Dirección</label>
          <input id="contact_address" name="contact_address" class="form-control" value="<?= e(setting_value('contact_address')) ?>" placeholder="Dirección">
        </div>
        <div class="form-group">
          <label for="contact_image">Imagen contacto</label>
          <input type="file" name="contact_image" id="contact_image" class="form-control" accept="image/*">
          <small class="text-muted d-block mt-1">URL actual: <input name="contact_image" class="form-control form-control-sm mt-1" value="<?= e(setting_value('contact_image')) ?>"></small>
        </div>
        <div class="form-group">
          <label for="contact_map_iframe">Código de Mapa (Iframe de Google Maps)</label>
          <textarea id="contact_map_iframe" name="contact_map_iframe" class="form-control" rows="4" placeholder='Pega aquí el código <iframe src="..."></iframe>'><?= e(setting_value('contact_map_iframe')) ?></textarea>
          <small class="text-muted">Esto reemplazará la imagen en la página de contacto.</small>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12 mb-4">
      <div class="card bg-soft border-light shadow-soft p-4">
        <h2 class="h5 mb-4">Inicio y sección nosotros</h2>
        <div class="row">
          <div class="col-12 col-md-6">
            <div class="form-group">
              <label for="hero_title">Título principal (Hero)</label>
              <input id="hero_title" name="hero_title" class="form-control" value="<?= e(setting_value('hero_title')) ?>" placeholder="Título hero">
            </div>
            <div class="form-group">
              <label for="hero_text">Texto principal (Hero)</label>
              <textarea id="hero_text" name="hero_text" class="form-control" rows="3" placeholder="Texto hero"><?= e(setting_value('hero_text')) ?></textarea>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="form-group">
              <label for="about_intro">Intro nosotros</label>
              <input id="about_intro" name="about_intro" class="form-control" value="<?= e(setting_value('about_intro')) ?>" placeholder="Intro nosotros">
            </div>
            <div class="form-group">
              <label for="about_image">Imagen nosotros</label>
              <input type="file" name="about_image" id="about_image" class="form-control" accept="image/*">
              <small class="text-muted d-block mt-1">URL actual: <input name="about_image" class="form-control form-control-sm mt-1" value="<?= e(setting_value('about_image')) ?>"></small>
            </div>
          </div>
          <div class="col-12 col-md-12">
            <div class="form-group">
              <label for="cta_background_image">Fondo de Llamado a la Acción (CTA Cotización)</label>
              <input type="file" name="cta_background_image" id="cta_background_image" class="form-control" accept="image/*">
              <small class="text-muted d-block mt-1">URL actual: <input name="cta_background_image" class="form-control form-control-sm mt-1" value="<?= e(setting_value('cta_background_image')) ?>"></small>
            </div>
          </div>
          <div class="col-12" id="about-objective">
            <div class="form-group">
              <label for="about_objective">Nuestro Objetivo</label>
              <textarea id="about_objective" name="about_objective" class="form-control js-summernote" rows="5" placeholder="Objetivo de la empresa"><?= e(setting_value('about_objective')) ?></textarea>
            </div>
          </div>
          <div class="col-12">
            <div class="form-group">
              <label for="about_text">Texto nosotros</label>
              <textarea id="about_text" name="about_text" class="form-control js-summernote" rows="5" placeholder="Texto nosotros"><?= e(setting_value('about_text')) ?></textarea>
            </div>
          </div>
        </div>
        <div class="mt-3 text-right">
          <button class="btn btn-primary" type="submit">Guardar todos los ajustes</button>
        </div>
      </div>
    </div>
  </div>
</form>
<?php require __DIR__ . '/partials/footer.php'; ?>
