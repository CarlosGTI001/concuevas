<?php
declare(strict_types=1);

function setup_error_handling(array $config): void
{
    $debug = (bool) ($config['app']['debug'] ?? false);
    $logFile = (string) ($config['app']['error_log'] ?? __DIR__ . '/../error_log');

    ini_set('display_errors', $debug ? '1' : '0');
    ini_set('log_errors', '1');
    ini_set('error_log', $logFile);
    error_reporting(E_ALL);

    set_error_handler(
        static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new ErrorException($message, 0, $severity, $file, $line);
        }
    );

    set_exception_handler(
        static function (Throwable $e) use ($debug): void {
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: text/html; charset=UTF-8');
            }

            error_log(format_error_log($e));

            echo render_error_page(
                'Error interno del servidor',
                $debug
                    ? nl2br(htmlspecialchars(format_exception_details($e), ENT_QUOTES, 'UTF-8'))
                    : 'Ocurrió un error inesperado. Revisa el archivo de log para más detalles.'
            );
        }
    );

    register_shutdown_function(
        static function () use ($debug): void {
            $fatal = error_get_last();
            if (!$fatal) {
                return;
            }

            $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
            if (!in_array($fatal['type'], $fatalTypes, true)) {
                return;
            }

            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: text/html; charset=UTF-8');
            }

            $message = sprintf(
                "Fatal error: %s\nArchivo: %s\nLínea: %d",
                $fatal['message'] ?? 'Error fatal',
                $fatal['file'] ?? 'desconocido',
                (int) ($fatal['line'] ?? 0)
            );

            error_log($message);

            echo render_error_page(
                'Error fatal del servidor',
                $debug
                    ? nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'))
                    : 'Se produjo un error fatal. Revisa el archivo de log para más detalles.'
            );
        }
    );
}

function format_exception_details(Throwable $e): string
{
    return sprintf(
        "%s\nMensaje: %s\nArchivo: %s\nLínea: %d\n\nTrace:\n%s",
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );
}

function format_error_log(Throwable $e): string
{
    return sprintf(
        "[%s] %s",
        date('Y-m-d H:i:s'),
        format_exception_details($e)
    );
}

function render_error_page(string $title, string $details): string
{
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $homeUrl = '/';

    return '<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>' . $safeTitle . '</title>
  <style>
    *{box-sizing:border-box}
    body{
      margin:0;
      font-family:Arial,Helvetica,sans-serif;
      color:#0f244d;
      background:
        radial-gradient(circle at 12% 18%, rgba(30,91,191,.28), transparent 36%),
        radial-gradient(circle at 88% 82%, rgba(244,196,48,.26), transparent 40%),
        linear-gradient(145deg,#e9f0ff 0%,#dbe8ff 48%,#eaf3ff 100%);
      min-height:100vh;
      display:grid;
      place-items:center;
      padding:18px;
    }
    .card{
      width:min(980px,100%);
      background:rgba(255,255,255,.58);
      border:1px solid rgba(255,255,255,.62);
      border-radius:14px;
      backdrop-filter:blur(10px);
      -webkit-backdrop-filter:blur(10px);
      box-shadow:0 16px 38px rgba(10,31,70,.14);
      overflow:hidden;
    }
    .head{
      padding:18px 20px;
      border-bottom:1px solid rgba(163,184,226,.45);
      background:linear-gradient(135deg,rgba(11,61,145,.88),rgba(11,61,145,.74));
      color:#fff;
    }
    .chip{
      display:inline-block;
      font-size:.78rem;
      letter-spacing:.02em;
      text-transform:uppercase;
      border-radius:999px;
      padding:4px 10px;
      border:1px solid rgba(255,255,255,.42);
      background:rgba(255,255,255,.18);
      margin-bottom:8px;
    }
    h1{margin:0;font-size:1.2rem;line-height:1.35}
    .content{padding:18px 20px 20px}
    .details{
      background:rgba(243,247,255,.85);
      border:1px solid rgba(165,187,229,.55);
      border-radius:10px;
      padding:12px;
      line-height:1.5;
      font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;
      font-size:.88rem;
      overflow:auto;
      max-height:52vh;
    }
    .actions{margin-top:12px}
    .btn{
      display:inline-block;
      text-decoration:none;
      border-radius:7px;
      padding:10px 14px;
      background:#f4c430;
      color:#1a1a1a;
      font-weight:700;
      border:1px solid rgba(0,0,0,.08);
    }
  </style>
</head>
<body>
    <div class="card">
      <div class="head">
        <span class="chip">Error 500</span>
        <h1>' . $safeTitle . '</h1>
      </div>
      <div class="content">
      <div class="details">' . $details . '</div>
        <div class="actions">
          <a class="btn" href="' . $homeUrl . '">Volver al inicio</a>
        </div>
      </div>
    </div>
</body>
</html>';
}
