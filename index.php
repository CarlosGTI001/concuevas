<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$currentPage = 'inicio';
$pageTitle = site_name() . ' | ' . site_tagline();

// 1. Fetch Section Order
$sections = db()->query('SELECT section_key FROM page_sections WHERE is_active = 1 ORDER BY sort_order ASC')->fetchAll(PDO::FETCH_COLUMN);

// 2. Fetch Data for all possible sections
$services = db()->query('SELECT id, name, short_description, image_url FROM services ORDER BY sort_order ASC, id DESC')->fetchAll();
$projects = db()->query('SELECT id, title, slug, excerpt, cover_image_url FROM projects ORDER BY updated_at DESC LIMIT 6')->fetchAll();
$sliders = db()->query('SELECT * FROM sliders ORDER BY sort_order ASC, id DESC')->fetchAll();
$siteValues = db()->query('SELECT * FROM site_values ORDER BY sort_order ASC, id DESC LIMIT 3')->fetchAll();

// Fallbacks
if (empty($sliders)) {
    $sliders[] = [
        'image_url' => 'https://images.unsplash.com/photo-1503387762-592be5a52680?auto=format&fit=crop&w=1200&q=80',
        'title' => setting('hero_title', 'Construcción inteligente'),
        'text' => setting('hero_text', 'Planificación, diseño y ejecución.'),
        'position' => 'center-center',
        'image_position' => 'center-center'
    ];
}
if (empty($siteValues)) {
    $siteValues = [
        ['title' => 'Calidad', 'description' => 'Excelencia en cada detalle de la obra.', 'icon' => 'fas fa-gem'],
        ['title' => 'Confianza', 'description' => 'Cumplimiento estricto de plazos.', 'icon' => 'fas fa-handshake'],
        ['title' => 'Compromiso', 'description' => 'Dedicación total hasta la entrega.', 'icon' => 'fas fa-bullseye'],
    ];
}

require __DIR__ . '/partials/header.php';
?>
<script>document.body.classList.add('home-page');</script>

<?php
// 3. Render sections in order
foreach ($sections as $sectionKey) {
    $file = __DIR__ . '/partials/homepage/' . $sectionKey . '.php';
    if (file_exists($file)) {
        include $file;
    }
}
?>

<?php require __DIR__ . '/partials/footer.php'; ?>
