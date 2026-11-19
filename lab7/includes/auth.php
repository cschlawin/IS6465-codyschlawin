<?php
/** Shared session security, authentication, authorization and CSRF primitives. */
require_once __DIR__ . '/../repositories/userPDO.php';
function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'secure' => $https,
        'httponly' => true, 'samesite' => 'Lax',
    ]);
    session_start();
}
startSecureSession();

function escapeHtml(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verifyCsrfToken(array $post): void
{
    if (!is_string($post['csrf_token'] ?? null) ||
        !hash_equals(csrfToken(), $post['csrf_token'])) {
        http_response_code(403);
        exit('Invalid or missing CSRF token.');
    }
}
function currentUserId(): ?int
{
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}
function currentUser(PDO $pdo): ?array
{
    $id = currentUserId();
    if ($id === null) return null;
    if (!isset($_SESSION['last_activity']) || time() - (int)$_SESSION['last_activity'] > 1200) {
        logoutSession();
        return null;
    }
    $user = findPublicUserById($pdo, $id);
    if (!$user || $user['status'] !== 'Active') {
        logoutSession();
        return null;
    }
    // Always refresh permissions from SQLite; never trust a stored role blindly.
    $_SESSION['role'] = $user['role'];
    $_SESSION['last_activity'] = time();
    return $user;
}
function safeLocalRedirect(mixed $value, string $fallback = 'profile.php'): string
{
    if (!is_string($value) || $value === '' || str_starts_with($value, '//') ||
        preg_match('/[\x00-\x1f\x7f\\\\]/', $value)) return $fallback;
    $parts = parse_url($value);
    if ($parts === false || isset($parts['host']) || isset($parts['scheme']) || str_contains($parts['path'] ?? '', '..')) return $fallback;
    $page = basename($parts['path'] ?? '');
    $allowed = ['profile.php', 'change-password.php', 'requests.php',
        'request-form.php', 'request-details.php', 'edit-request.php',
        'delete-request.php', 'admin-users.php', 'admin-manage-user.php'];
    if (!in_array($page, $allowed, true)) return $fallback;
    return $page . (isset($parts['query']) ? '?' . $parts['query'] : '');
}
function requireLogin(PDO $pdo): array
{
    $user = currentUser($pdo);
    if (!$user) {
        $redirect = safeLocalRedirect($_SERVER['REQUEST_URI'] ?? 'profile.php');
        header('Location: login.php?redirect=' . rawurlencode($redirect), true, 302);
        exit;
    }
    return $user;
}
function requireRole(array $user, array $roles): void
{
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('Access denied for your role.');
    }
}
function loginUser(array $user): void
{
    if (($user['status'] ?? null) !== 'Active') throw new InvalidArgumentException('Inactive user.');
    session_regenerate_id(true);
    $_SESSION = ['user_id' => (int)$user['user_id'],
        'role' => $user['role'], 'last_activity' => time(),
        'csrf_token' => bin2hex(random_bytes(32))];
}
function logoutSession(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 3600, 'path' => $params['path'],
            'domain' => $params['domain'], 'secure' => $params['secure'],
            'httponly' => $params['httponly'], 'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
}
