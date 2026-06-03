<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

// Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Verificar token CSRF
if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('CSRF failed');
}

$id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($id > 0 && $action === 'quote_confirmation') {
    // Buscar la solicitud en la base de datos
    $stmt = db()->prepare('SELECT * FROM quote_requests WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $quote = $stmt->fetch();

    if ($quote && !empty($quote['email'])) {
        $confirmSubject = 'Confirmación de solicitud de cotización - ' . site_name();
        $confirmMessage = "Hemos recibido tu solicitud de cotización. Un asesor técnico se pondrá en contacto contigo a la brevedad a través de este correo o al número proporcionado ({$quote['phone']}).";
        
        send_mail($quote['email'], $confirmSubject, $confirmMessage, [
            'name' => $quote['name'],
            'project_type' => $quote['project_type'],
            'message' => $quote['message']
        ]);
    }
}

// Retornar un simple JSON de éxito (el frontend no lo espera, pero es buena práctica)
header('Content-Type: application/json');
echo json_encode(['status' => 'done']);
