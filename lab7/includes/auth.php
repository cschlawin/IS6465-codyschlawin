<?php
// includes/auth.php
// Session, authentication, authorization, and CSRF helpers for Peer Tutoring Hub Lab 7

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(array $post): void {
    $token = $post['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('Invalid or missing CSRF token.');
    }
}

function currentUserId(): ?int {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function currentUser(PDO $pdo): ?array {
    $id = currentUserId();
    if (!$id) return null;
    $stmt = $pdo->prepare('SELECT user_id, full_name, email, role, status FROM users WHERE user_id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user || $user['status'] !== 'Active') {
        logoutSession();
        return null;
    }
    return $user;
}

function requireLogin(PDO $pdo): array {
    $user = currentUser($pdo);
    if (!$user) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? 'requests.php'));
        exit;
    }
    return $user;
}

function requireRole(array $user, array $allowedRoles): void {
    if (!in_array($user['role'], $allowedRoles, true)) {
        http_response_code(403);
        exit('Access denied for your role.');
    }
}

function loginUser(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['user_id'];
    $_SESSION['role'] = $user['role'];
    csrfToken();
}

function logoutSession(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function escapeHtml(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
