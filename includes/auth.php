<?php
declare(strict_types=1);

function is_logged_in(): bool
{
    return isset($_SESSION['admin_user_id']);
}

function current_admin_id(): int
{
    return (int) ($_SESSION['admin_user_id'] ?? 0);
}

function must_change_credentials(): bool
{
    return (bool) ($_SESSION['must_change_credentials'] ?? false);
}

function attempt_login(string $email, string $password): bool
{
    $stmt = db()->prepare('SELECT id, password_hash, must_change_credentials FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    if (!password_verify($password, (string) $user['password_hash'])) {
        return false;
    }

    $_SESSION['admin_user_id'] = (int) $user['id'];
    $_SESSION['must_change_credentials'] = (int) ($user['must_change_credentials'] ?? 0) === 1;
    return true;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function require_login(bool $enforceCredentialRotation = true): void
{
    if (!is_logged_in()) {
        redirect(app_url('admin/login.php'));
    }

    if ($enforceCredentialRotation && must_change_credentials()) {
        redirect(app_url('admin/force_credentials.php'));
    }
}

function complete_credential_rotation(int $userId, string $email, string $newPassword): void
{
    $stmt = db()->prepare(
        'UPDATE users SET email = :email, password_hash = :password_hash, must_change_credentials = 0 WHERE id = :id'
    );
    $stmt->execute([
        'id' => $userId,
        'email' => $email,
        'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
    ]);

    $_SESSION['must_change_credentials'] = false;
}
