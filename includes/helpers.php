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
    
    // Split path and query string to handle .php removal correctly
    $parts = explode('?', $path, 2);
    $pathOnly = $parts[0];
    $query = isset($parts[1]) ? '?' . $parts[1] : '';

    // Remove .php extension for cleaner URLs
    if (preg_match('/\.php$/', $pathOnly)) {
        if ($pathOnly === 'index.php') {
            $pathOnly = '';
        } elseif (substr($pathOnly, -10) === '/index.php') {
            $pathOnly = substr($pathOnly, 0, -10);
        } else {
            $pathOnly = substr($pathOnly, 0, -4);
        }
    }

    $path = ltrim($pathOnly, '/') . $query;

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

function is_video_extension(string $extension): bool
{
    $videoExtensions = ['mp4', 'webm', 'ogg', 'mov'];
    return in_array(strtolower($extension), $videoExtensions, true);
}

function handle_upload(array $file, string $subfolder = '', ?string $customName = null): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    // Stricter MIME type and extension validation
    $allowedMimeTypes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'
    ];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'ogg', 'mov'];
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedMimeTypes, true) || !in_array($extension, $allowedExtensions, true)) {
        return null;
    }

    // Validate if it's actually an image or a valid video
    if (!is_video_extension($extension)) {
        $imageSize = @getimagesize($file['tmp_name']);
        if ($imageSize === false) {
            return null;
        }
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

function format_email_address(string $email, string $name = ''): string
{
    $email = trim($email);
    $name = trim($name);
    if ($name === '') {
        return $email;
    }
    $safeName = str_replace('"', '\"', $name);
    $encodedName = function_exists('mb_encode_mimeheader')
        ? mb_encode_mimeheader($safeName, 'UTF-8')
        : $safeName;
    return sprintf('"%s" <%s>', $encodedName, $email);
}

function encode_mail_header(string $value): string
{
    return function_exists('mb_encode_mimeheader')
        ? mb_encode_mimeheader($value, 'UTF-8')
        : $value;
}

function smtp_send_message(array $smtp, string $fromEmail, string $to, string $rawMessage): bool
{
    $host = trim((string) ($smtp['host'] ?? ''));
    $port = (int) ($smtp['port'] ?? 25);
    $username = (string) ($smtp['username'] ?? '');
    $password = (string) ($smtp['password'] ?? '');
    $encryption = strtolower((string) ($smtp['encryption'] ?? ''));
    $timeout = (int) ($smtp['timeout'] ?? 10);

    if ($host === '') {
        error_log('SMTP host no configurado.');
        return false;
    }
    if ($fromEmail === '') {
        error_log('SMTP requiere un correo "from_email" válido.');
        return false;
    }

    $remoteHost = $encryption === 'ssl' ? 'ssl://' . $host : $host;
    error_log("SMTP conectando a: $remoteHost:$port (Encryption: $encryption)");
    
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ]
    ]);

    $socket = @stream_socket_client(
        $remoteHost . ':' . $port,
        $errno,
        $errstr,
        $timeout,
        STREAM_CLIENT_CONNECT,
        $context
    );
    if (!$socket) {
        error_log("SMTP conexión fallida: {$errstr} ({$errno}). Host: $remoteHost:$port");
        return false;
    }
    stream_set_timeout($socket, $timeout);

    $greeting = smtp_read_response($socket);
    if (!smtp_expect_code($greeting, 220)) {
        $meta = stream_get_meta_data($socket);
        error_log('SMTP saludo inválido: "' . trim($greeting) . '" (Timed out: ' . ($meta['timed_out'] ? 'SI' : 'NO') . ')');
        fclose($socket);
        return false;
    }

    $hostname = gethostname() ?: 'localhost';
    if (!smtp_command($socket, "EHLO {$hostname}", [250], 'SMTP EHLO falló')) {
        if (!smtp_command($socket, "HELO {$hostname}", [250], 'SMTP HELO falló')) {
            fclose($socket);
            return false;
        }
    }

    if ($encryption === 'tls') {
        if (!smtp_command($socket, 'STARTTLS', [220], 'SMTP STARTTLS falló')) {
            fclose($socket);
            return false;
        }
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            error_log('SMTP no pudo negociar TLS.');
            fclose($socket);
            return false;
        }
        if (!smtp_command($socket, "EHLO {$hostname}", [250], 'SMTP EHLO (post TLS) falló')) {
            fclose($socket);
            return false;
        }
    }

    if ($username !== '' || $password !== '') {
        if ($username === '' || $password === '') {
            error_log('SMTP autenticación requiere usuario y contraseña.');
            fclose($socket);
            return false;
        }
        if (!smtp_auth_login($socket, $username, $password)) {
            fclose($socket);
            return false;
        }
    }

    if (!smtp_command($socket, "MAIL FROM:<{$fromEmail}>", [250], 'SMTP MAIL FROM falló')) {
        fclose($socket);
        return false;
    }
    if (!smtp_command($socket, "RCPT TO:<{$to}>", [250, 251], 'SMTP RCPT TO falló')) {
        fclose($socket);
        return false;
    }
    if (!smtp_command($socket, 'DATA', [354], 'SMTP DATA falló')) {
        fclose($socket);
        return false;
    }

    $payload = smtp_prepare_data($rawMessage);
    smtp_write($socket, $payload . "\r\n.");
    $dataResponse = smtp_read_response($socket);
    if (!smtp_expect_code($dataResponse, 250)) {
        error_log('SMTP envío falló: ' . trim($dataResponse));
        fclose($socket);
        return false;
    }

    smtp_command($socket, 'QUIT', [221, 250], 'SMTP QUIT falló');
    fclose($socket);
    return true;
}

function smtp_auth_login($socket, string $username, string $password): bool
{
    if (!smtp_command($socket, 'AUTH LOGIN', [334], 'SMTP AUTH LOGIN falló')) {
        return false;
    }
    smtp_write($socket, base64_encode($username));
    $userResponse = smtp_read_response($socket);
    if (!smtp_expect_code($userResponse, 334)) {
        error_log('SMTP AUTH usuario rechazado: ' . trim($userResponse));
        return false;
    }
    smtp_write($socket, base64_encode($password));
    $passResponse = smtp_read_response($socket);
    if (!smtp_expect_code($passResponse, 235)) {
        error_log('SMTP AUTH contraseña rechazada: ' . trim($passResponse));
        return false;
    }
    return true;
}

function smtp_command($socket, string $command, array $expectedCodes, string $errorMessage): bool
{
    smtp_write($socket, $command);
    $response = smtp_read_response($socket);
    if (!smtp_expect_code($response, $expectedCodes)) {
        error_log($errorMessage . ': ' . trim($response));
        return false;
    }
    return true;
}

function smtp_write($socket, string $command): void
{
    fwrite($socket, $command . "\r\n");
}

function smtp_read_response($socket): string
{
    $data = '';
    while (($line = fgets($socket, 515)) !== false) {
        $data .= $line;
        if (preg_match('/^\d{3} /', $line)) {
            break;
        }
    }
    return $data;
}

function smtp_expect_code(string $response, $expectedCodes): bool
{
    $code = (int) substr(trim($response), 0, 3);
    $codes = is_array($expectedCodes) ? $expectedCodes : [$expectedCodes];
    return in_array($code, $codes, true);
}

function smtp_prepare_data(string $data): string
{
    $normalized = str_replace(["\r\n", "\r"], "\n", $data);
    $normalized = str_replace("\n", "\r\n", $normalized);
    return preg_replace('/\r\n\./', "\r\n..", $normalized) ?? $normalized;
}

function send_mail(string $to, string $subject, string $message, array $data = []): bool
{
    global $config;
    $fromEmail = (string) ($config['mail']['from_email'] ?? '');
    $fromName = (string) ($config['mail']['from_name'] ?? '');
    
    // Fetch Dynamic Settings
    $logo = setting('email_logo', setting('site_logo'));
    if ($logo && !preg_match('/^https?:\/\//', $logo)) {
        $logo = app_url($logo);
    }
    if (!$logo) {
        $logo = 'https://www.concuevas.com/uploads/settings/site-logo-5cfaf6.png';
    }
    
    $phone = setting('contact_phone', '+52 000 000 0000');
    $email = 'cotizacion@concuevas.com';
    $address = setting('contact_address', 'Av. Principal 123, Zona Centro');
    
    // Neumorphism Palette
    $bgMain = '#e6e7ee';
    $textDark = '#44476A';
    $textDeep = '#0D284F'; // Using Brand Blue for contrast
    $shadowLight = '#ffffff';
    $shadowDark = '#b1bcce';

    if ($fromEmail === '') {
        error_log('[Mail] No se encontró "from_email" en config.php');
        return false;
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: ' . format_email_address($fromEmail, $fromName),
        'Reply-To: ' . $fromEmail,
        'X-Mailer: PHP/' . phpversion()
    ];

    // Premium Neumorphic Email Template with Dark Mode Support
    $htmlMessage = "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <meta name='color-scheme' content='light dark'>
        <meta name='supported-color-schemes' content='light dark'>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap');
            
            :root {
                color-scheme: light dark;
                supported-color-schemes: light dark;
            }

            body { 
                font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
                margin: 0; 
                padding: 0; 
                color: {$textDark};
            }
            .wrapper { padding: 40px 15px; }
            .container { 
                max-width: 600px; 
                margin: 0 auto; 
                background-color: {$bgMain}; 
                border-radius: 35px; 
                padding: 30px 40px 50px 40px;
                box-shadow: 15px 15px 30px {$shadowDark}, -15px -15px 30px {$shadowLight}; 
            }
            .header { text-align: center; margin-bottom: 20px; }
            .logo-img { max-height: 90px; }
            h1 { font-size: 24px; color: {$textDeep}; margin-bottom: 15px; font-weight: 700; text-align: center; letter-spacing: -0.5px; }
            .main-text { font-size: 16px; margin-bottom: 30px; font-weight: 400; line-height: 1.7; text-align: center; color: {$textDark}; }
            
            .details-card { 
                background-color: {$bgMain}; 
                padding: 30px; 
                border-radius: 24px; 
                box-shadow: inset 8px 8px 16px {$shadowDark}, inset -8px -8px 16px {$shadowLight}; 
                margin: 35px 0; 
            }
            .details-title { 
                font-weight: 700; 
                color: {$textDeep}; 
                font-size: 14px; 
                text-transform: uppercase; 
                letter-spacing: 1px; 
                margin-bottom: 20px;
                display: block;
            }
            .detail-item { margin-bottom: 15px; }
            .detail-label { font-size: 12px; color: #757575; font-weight: 600; display: block; margin-bottom: 4px; }
            .detail-value { font-size: 15px; color: {$textDeep}; font-weight: 600; }
            
            .message-box {
                margin-top: 20px;
                padding: 20px;
                background-color: rgba(255,255,255,0.3);
                border-radius: 16px;
                font-style: italic;
                font-size: 14px;
                line-height: 1.6;
                color: {$textDark};
                border: 1px solid rgba(255,255,255,0.5);
            }

            .signature { 
                margin-top: 45px; 
                text-align: center;
                color: {$textDeep};
            }
            .signature-name { font-weight: 700; font-size: 17px; margin: 0; }
            .signature-team { font-weight: 400; font-size: 14px; margin-top: 5px; opacity: 0.8; }

            .footer { 
                margin-top: 50px; 
                text-align: center; 
                font-size: 12px; 
                color: #8898aa; 
                border-top: 1px solid rgba(0,0,0,0.05);
                padding-top: 30px;
            }
            .contact-info { margin-bottom: 15px; font-weight: 500; }
            .copyright { opacity: 0.7; font-size: 11px; }

            /* Dark Mode Adjustments */
            @media (prefers-color-scheme: dark) {
                body, .wrapper { background-color: #1b1e2e !important; }
                .container { 
                    background-color: #1b1e2e !important; 
                    box-shadow: 10px 10px 20px #0d0f17, -10px -10px 20px #292e45 !important;
                }
                .details-card {
                    background-color: #1b1e2e !important;
                    box-shadow: inset 6px 6px 12px #0d0f17, inset -6px -6px 12px #292e45 !important;
                }
                h1, .detail-value, .signature-name, .signature { color: #e6e7ee !important; }
                .main-text, .detail-label, .signature-team { color: #aeb3c2 !important; }
                .message-box {
                    background-color: rgba(0,0,0,0.2) !important;
                    color: #aeb3c2 !important;
                    border-color: rgba(255,255,255,0.1) !important;
                }
                .logo-img { filter: brightness(0) invert(1); }
            }
        </style>
    </head>
    <body>
        <div class='wrapper'>
            <div class='container'>
                <!-- Header -->
                <div class='header'>
                    <img src='{$logo}' alt='{$fromName}' class='logo-img'>
                </div>

                <!-- Content -->
                <div class='content'>
                    <h1>¡Hola" . (isset($data['name']) ? ", " . htmlspecialchars($data['name']) : "") . "!</h1>
                    
                    <div class='main-text'>
                        " . nl2br(htmlspecialchars($message)) . "
                    </div>
                    
                    " . (isset($data['project_type']) ? "
                    <div class='details-card'>
                        <span class='details-title'>Resumen del Proyecto</span>
                        
                        <div class='detail-item'>
                            <span class='detail-label'>Tipo de Servicio</span>
                            <span class='detail-value'>" . htmlspecialchars($data['project_type']) . "</span>
                        </div>
                        
                        <div class='message-box'>
                            <strong>Mensaje del cliente:</strong><br>
                            \"" . htmlspecialchars($data['message'] ?? '') . "\"
                        </div>
                    </div>" : "") . "

                    <!-- Signature -->
                    <div class='signature'>
                        <p class='signature-name'>Atentamente,</p>
                        <p class='signature-team'>El equipo de {$fromName}</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class='footer'>
                    <div class='contact-info'>
                        <p>{$address}</p>
                        <p>Tel: {$phone} | Email: {$email}</p>
                    </div>
                    <p class='copyright'>
                        &copy; " . date('Y') . " " . htmlspecialchars($fromName) . ". Todos los derechos reservados.<br>
                        Este es un mensaje automático, por favor no responda directamente.
                    </p>
                </div>
            </div>
        </div>
    </body>
    </html>";

    $smtpConfig = (array) ($config['smtp'] ?? []);
    $smtpEnabled = (bool) ($smtpConfig['enabled'] ?? false);

    if ($smtpEnabled) {
        $smtpHeaders = array_merge(
            [
                'Date: ' . date(DATE_RFC2822),
                'To: ' . $to,
                'Subject: ' . encode_mail_header($subject),
            ],
            $headers
        );
        $rawMessage = implode("\r\n", $smtpHeaders) . "\r\n\r\n" . $htmlMessage;
        $result = smtp_send_message($smtpConfig, $fromEmail, $to, $rawMessage);
        if (!$result) {
            error_log("[Mail] SMTP falló al enviar correo a: $to");
        } else {
            error_log("[Mail] SMTP envió correo con éxito a: $to (Asunto: $subject)");
        }
        return $result;
    }

    $result = mail($to, $subject, $htmlMessage, implode("\r\n", $headers));
    if (!$result) {
        error_log("[Mail] Función mail() de PHP falló al enviar correo a: $to");
    } else {
        error_log("[Mail] Función mail() de PHP envió correo con éxito a: $to (Asunto: $subject)");
    }
    return $result;
}
