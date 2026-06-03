<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

if (!is_logged_in()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Denegado');
}

$action = $_POST['action'] ?? '';

if ($action === 'upload') {
    $itemId = (int)($_POST['item_id'] ?? 0);
    $sessionId = $_POST['session_id'] ?? null;
    $type = $_POST['type'] ?? ''; // 'project' or 'service'
    $file = $_FILES['file'] ?? null;

    if (($itemId > 0 || $sessionId) && $file && in_array($type, ['project', 'service'])) {
        $subfolder = ($type === 'project' ? 'projects/gallery' : 'services/gallery');
        $itemName = ($type === 'project' ? 'proj-' : 'serv-') . ($itemId ?: 'temp');
        
        $url = handle_upload($file, $subfolder, $itemName . '-gal');
        
        if ($url) {
            $table = ($type === 'project' ? 'project_images' : 'service_images');
            $fk = ($type === 'project' ? 'project_id' : 'service_id');
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $mediaType = is_video_extension($extension) ? 'video' : 'image';
            
            $stmt = db()->prepare("INSERT INTO $table ($fk, session_id, image_url, sort_order, media_type) VALUES (?, ?, ?, 999, ?)");
            $stmt->execute([$itemId, $sessionId, $url, $mediaType]);
            
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'url' => $url, 'id' => db()->lastInsertId()]);
            exit;
        }
    }
} elseif ($action === 'sort') {
    $ids = $_POST['ids'] ?? [];
    $type = $_POST['type'] ?? '';
    
    if (!empty($ids) && in_array($type, ['project', 'service'])) {
        $table = ($type === 'project' ? 'project_images' : 'service_images');
        $stmt = db()->prepare("UPDATE $table SET sort_order = ? WHERE id = ?");
        
        db()->beginTransaction();
        foreach ($ids as $index => $id) {
            $stmt->execute([$index, (int)$id]);
        }
        db()->commit();
        echo json_encode(['status' => 'success']);
        exit;
    }
}

header('HTTP/1.1 400 Bad Request');
