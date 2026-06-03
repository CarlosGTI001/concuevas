<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

// Only allow logged in admins
if (!is_logged_in()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso denegado');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $folder = $_POST['folder'] ?? 'editor';
    $name = $_POST['name'] ?? 'image';
    
    $url = handle_upload($_FILES['image'], $folder, $name);
    
    if ($url) {
        header('Content-Type: application/json');
        echo json_encode(['url' => $url]);
        exit;
    }
}

header('HTTP/1.1 400 Bad Request');
echo json_encode(['error' => 'No se pudo subir la imagen']);
