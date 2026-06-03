<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Santo_Domingo');

$config = require __DIR__ . '/../config.php';

define('APP_VERSION', '2.9.3');

// Global Input Sanitization
array_walk_recursive($_GET, function(&$val) { $val = trim((string)$val); });
array_walk_recursive($_POST, function(&$val) { $val = trim((string)$val); });

require_once __DIR__ . '/error_handler.php';
setup_error_handling($config);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/cms.php';
require_once __DIR__ . '/auth.php';
