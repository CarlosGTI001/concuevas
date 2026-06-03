<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// In a real scenario, you'd check for admin session here
// if (!is_admin()) { ... }

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['type'], $data['order'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$type = $data['type'];
$order = $data['order'];

$allowedTypes = ['services', 'sliders', 'page_sections'];
if (!in_array($type, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid type']);
    exit;
}

try {
    $db = db();
    $db->beginTransaction();

    $tableName = $type;
    // Special case if table name differs from type, but here they match our plan
    
    $stmt = $db->prepare("UPDATE `$tableName` SET sort_order = :pos WHERE id = :id");
    
    // For page_sections, we might use a string key or ID. 
    // Let's assume ID for consistency.
    
    foreach ($order as $item) {
        $stmt->execute([
            'pos' => $item['position'],
            'id' => $item['id']
        ]);
    }

    $db->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
