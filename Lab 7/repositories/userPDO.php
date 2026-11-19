<?php
/** User storage only: parameterized SQL; no HTTP or HTML. */
function findUserById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id=?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}
function findPublicUserById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT user_id,full_name,email,role,status FROM users WHERE user_id=?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}
function findUserByEmail(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email=? COLLATE NOCASE');
    $stmt->execute([trim($email)]);
    return $stmt->fetch() ?: null;
}
function insertUser(PDO $pdo, string $name, string $email, string $hash, string $role): int
{
    $stmt = $pdo->prepare("INSERT INTO users (full_name,email,password_hash,role,status) VALUES (?,?,?,?,'Active')");
    $stmt->execute([$name, $email, $hash, $role]);
    return (int)$pdo->lastInsertId();
}
function listUsers(PDO $pdo, string $search = '', string $role = '', string $status = ''): array
{
    $sql = 'SELECT user_id,full_name,email,role,status,created_at FROM users WHERE 1=1';
    $params = [];
    if ($search !== '') {
        // Escape LIKE wildcards so search is literal rather than attacker-controlled globs.
        $safe = strtr($search, ['\\' => '\\\\', '%' => '\\%', '_' => '\\_']);
        $sql .= " AND (full_name LIKE ? ESCAPE '\\' OR email LIKE ? ESCAPE '\\')";
        $params[] = '%' . $safe . '%';
        $params[] = '%' . $safe . '%';
    }
    if ($role !== '') {
        $sql .= ' AND role=?';
        $params[] = $role;
    }
    if ($status !== '') {
        $sql .= ' AND status=?';
        $params[] = $status;
    }
    $stmt = $pdo->prepare($sql . ' ORDER BY full_name COLLATE NOCASE, user_id');
    $stmt->execute($params);
    return $stmt->fetchAll();
}
function updateUserProfile(PDO $pdo, int $id, string $name, string $email): void
{
    $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=? WHERE user_id=? AND status='Active'");
    $stmt->execute([$name, $email, $id]);
}
function updateUserPassword(PDO $pdo, int $id, string $hash): void
{
    $stmt = $pdo->prepare("UPDATE users SET password_hash=? WHERE user_id=? AND status='Active'");
    $stmt->execute([$hash, $id]);
}
function updateUserRole(PDO $pdo, int $id, string $role): void
{
    $stmt = $pdo->prepare('UPDATE users SET role=? WHERE user_id=?');
    $stmt->execute([$role, $id]);
}
function updateUserStatus(PDO $pdo, int $id, string $status): void
{
    $stmt = $pdo->prepare('UPDATE users SET status=? WHERE user_id=?');
    $stmt->execute([$status, $id]);
}
function countActiveAdministrators(PDO $pdo, ?int $excluding = null): int
{
    $sql = "SELECT COUNT(*) FROM users WHERE role='Administrator' AND status='Active'";
    $params = [];
    if ($excluding !== null) { $sql .= ' AND user_id != ?'; $params[] = $excluding; }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}
function deleteUser(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('DELETE FROM users WHERE user_id=?');
    $stmt->execute([$id]);
}
function linkedRequestCount(PDO $pdo, int $id): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM tutoring_requests r JOIN students s ON r.student_id=s.student_id WHERE s.user_id=?');
    $stmt->execute([$id]);
    return (int)$stmt->fetchColumn();
}
