<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
require_login(false);

$currentAdminFile = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
if (must_change_credentials() && !in_array($currentAdminFile, ['force_credentials.php', 'logout.php'], true)) {
    redirect(app_url('admin/force_credentials.php'));
}
