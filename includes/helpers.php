<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_url(string $path = ''): string
{
    global $config;
    $base = rtrim((string) ($config['app']['base_url'] ?? ''), '/');
    $path = ltrim($path, '/');

    // Remove .php extension for cleaner URLs
    if (preg_match('/\.php$/', $path)) {
        if ($path === 'index.php') {
            $path = '';
        } elseif (substr($path, -10) === '/index.php') {
            $path = substr($path, 0, -10);
        } else {
            $path = substr($path, 0, -4);
        }
    }

    // Ensure we don't have double slashes if path is empty
    $finalPath = '/' . $path;
    if ($base !== '') {
        $finalPath = $base . ($path === '' ? '' : '/' . $path);
    }

    return $finalPath;
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function slugify(string $text): string
{
    $text = trim(mb_strtolower($text));
    $text = preg_replace('/[^a-z0-9]+/u', '-', $text) ?? '';
    $text = trim($text, '-');
    return $text !== '' ? $text : 'proyecto-' . time();
}

function handle_upload(array $file, string $subfolder = '', ?string $customName = null): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    // Stricter MIME type and extension validation
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedMimeTypes, true) || !in_array($extension, $allowedExtensions, true)) {
        return null;
    }

    // Validate if it's actually an image (check file signature/magic numbers)
    $imageSize = @getimagesize($file['tmp_name']);
    if ($imageSize === false) {
        return null;
    }

    $uploadDir = __DIR__ . '/../uploads/';
    if ($subfolder !== '') {
        $uploadDir .= rtrim($subfolder, '/') . '/';
    }

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        return null;
    }

    // Security: Ensure the uploads directory is protected against execution
    ensure_uploads_protection($uploadDir);
    
    // Generate a clean filename based on customName (SEO) or random
    if ($customName !== null && trim($customName) !== '') {
        $baseName = slugify($customName);
        // Append a short random string to avoid collisions
        $filename = $baseName . '-' . bin2hex(random_bytes(3)) . '.' . $extension;
    } else {
        $filename = bin2hex(random_bytes(8)) . '.' . $extension;
    }

    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $relativeDir = 'uploads/' . ($subfolder !== '' ? rtrim($subfolder, '/') . '/' : '');
        return app_url($relativeDir . $filename);
    }

    return null;
}

function ensure_uploads_protection(string $dir): void
{
    $htaccessPath = $dir . '.htaccess';
    if (!file_exists($htaccessPath)) {
        $content = "# Disable script execution in uploads directory\n";
        $content .= "Options -ExecCGI -Indexes\n";
        $content .= "RemoveHandler .php .phtml .php3 .php4 .php5 .php7 .phps\n";
        $content .= "RemoveType .php .phtml .php3 .php4 .php5 .php7 .phps\n";
        $content .= "<FilesMatch \"\.(?i:php|phtml|php3|php4|php5|php7|phps)$\">\n";
        $content .= "    Order Allow,Deny\n";
        $content .= "    Deny from all\n";
        $content .= "</FilesMatch>\n";
        @file_put_contents($htaccessPath, $content);
    }
}

function send_mail(string $to, string $subject, string $message, array $data = []): bool
{
    global $config;
    $fromEmail = $config['mail']['from_email'];
    $fromName = $config['mail']['from_name'];
    
    // Fetch Dynamic Settings
    $logo = setting('email_logo', setting('site_logo', app_url('assets/img/brand/dark.svg')));
    $phone = setting('contact_phone', '+52 000 000 0000');
    $email = setting('contact_email', 'contacto@construccionescuevas.com');
    $address = setting('contact_address', 'Av. Principal 123, Zona Centro');
    
    $brandBlue = '#0D284F';
    $brandGray = '#757575';
    $bgSoft = '#E6E6E6';

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Reply-To: ' . $fromEmail,
        'X-Mailer: PHP/' . phpversion()
    ];

    // Master Corporate Email Template
    $htmlMessage = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <style>
            body { font-family: 'Montserrat', Arial, sans-serif; background-color: {$bgSoft}; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 40px auto; background-color: {$bgSoft}; border-radius: 20px; overflow: hidden; box-shadow: 10px 10px 20px #bebebe, -10px -10px 20px #ffffff; }
            .header { background-color: {$bgSoft}; padding: 30px; text-align: center; border-bottom: 1px solid rgba(0,0,0,0.05); }
            .content { padding: 40px; color: {$brandBlue}; background-color: {$bgSoft}; }
            .footer { background-color: {$bgSoft}; padding: 30px; text-align: center; font-size: 13px; color: {$brandGray}; border-top: 1px solid rgba(0,0,0,0.05); }
            .badge { background-color: {$bgSoft}; color: {$brandBlue}; padding: 5px 15px; border-radius: 10px; display: inline-block; box-shadow: inset 2px 2px 5px #bebebe, inset -2px -2px 5px #ffffff; font-weight: bold; }
            .card { background-color: {$bgSoft}; padding: 20px; border-radius: 15px; box-shadow: 4px 4px 8px #bebebe, -4px -4px 8px #ffffff; margin-top: 20px; }
            .signature { margin-top: 40px; padding-top: 20px; border-top: 1px dashed #ccc; font-style: italic; }
            h1 { font-size: 22px; margin-bottom: 20px; color: {$brandBlue}; }
            p { line-height: 1.7; margin-bottom: 15px; }
            .info-item { margin-bottom: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <!-- Header -->
            <div class='header'>
                <img src='{$logo}' alt='{$fromName}' style='max-height: 70px;'>
            </div>

            <!-- Content -->
            <div class='content'>
                <h1>Hola" . (isset($data['name']) ? ", " . htmlspecialchars($data['name']) : "") . "!</h1>
                
                <div class='main-text'>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
                
                " . (isset($data['project_type']) ? "
                <div class='card'>
                    <p style='margin-top:0; font-weight:bold;'>Detalles de la solicitud:</p>
                    <div class='info-item'>Tipo: <span class='badge'>{$data['project_type']}</span></div>
                    <div class='info-item' style='font-size: 0.95em; opacity: 0.8; margin-top:10px;'>
                        <strong>Mensaje original:</strong><br>
                        \"" . htmlspecialchars($data['message'] ?? '') . "\"
                    </div>
                </div>" : "") . "

                <!-- Signature -->
                <div class='signature'>
                    <p style='margin-bottom: 5px;'>Atentamente,</p>
                    <p style='font-weight: bold; margin-top: 0;'>El equipo de {$fromName}</p>
                </div>
            </div>

            <!-- Footer -->
            <div class='footer'>
                <p style='font-weight: bold; margin-bottom: 10px;'>Construcciones Cuevas</p>
                <div class='info-item'>Tel: {$phone}</div>
                <div class='info-item'>Email: {$email}</div>
                <div class='info-item'>Dirección: {$address}</div>
                <p style='margin-top: 20px; font-size: 11px; opacity: 0.7;'>
                    &copy; " . date('Y') . " Todos los derechos reservados. Este mensaje es confidencial.
                </p>
            </div>
        </div>
    </body>
    </html>";

    return mail($to, $subject, $htmlMessage, implode("\r\n", $headers));
}
