<?php
// repositories/userPDO.php
// Database operations for users table

function findUserByEmail(PDO $pdo, string $email): ?array {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function findUserById(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function insertUser(PDO $pdo, string $fullName, string $email, string $passwordHash, string $role): int {
    $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role, status) VALUES (?, ?, ?, ?, \'Active\')');
    $stmt->execute([$fullName, $email, $passwordHash, $role]);
    return (int)$pdo->lastInsertId();
}

function updateUserProfile(PDO $pdo, int $userId, string $fullName, string $email): bool {
    $stmt = $pdo->prepare('UPDATE users SET full_name = ?, email = ? WHERE user_id = ? AND status = \'Active\'');
    $stmt->execute([$fullName, $email, $userId]);
    return $stmt->rowCount() === 1;
}

function updateUserPassword(PDO $pdo, int $userId, string $passwordHash): bool {
    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?');
    $stmt->execute([$passwordHash, $userId]);
    return $stmt->rowCount() === 1;
}

function updateUserRole(PDO $pdo, int $userId, string $role): bool {
    $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE user_id = ?');
    $stmt->execute([$role, $userId]);
    return $stmt->rowCount() === 1;
}

function updateUserStatus(PDO $pdo, int $userId, string $status): bool {
    $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE user_id = ?');
    $stmt->execute([$status, $userId]);
    return $stmt->rowCount() === 1;
}

function listUsers(PDO $pdo, string $search = '', string $role = '', string $status = ''): array {
    $sql = 'SELECT user_id, full_name, email, role, status, created_at FROM users WHERE 1=1';
    $args = [];
    if ($search !== '') {
        $sql .= ' AND (full_name LIKE ? OR email LIKE ?)';
        $like = '%' . $search . '%';
        $args[] = $like;
        $args[] = $like;
    }
    if ($role !== '' && in_array($role, ['Student', 'Tutor', 'Administrator'], true)) {
        $sql .= ' AND role = ?';
        $args[] = $role;
    }
    if ($status !== '' && in_array($status, ['Active', 'Inactive'], true)) {
        $sql .= ' AND status = ?';
        $args[] = $status;
    }
    $sql .= ' ORDER BY full_name';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($args);
    return $stmt->fetchAll();
}

function countActiveAdministrators(PDO $pdo, ?int $excludeId = null): int {
    $sql = 'SELECT COUNT(*) FROM users WHERE role = \'Administrator\' AND status = \'Active\'';
    $args = [];
    if ($excludeId !== null) {
        $sql .= ' AND user_id != ?';
        $args[] = $excludeId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($args);
    return (int)$stmt->fetchColumn();
}

function deleteUser(PDO $pdo, int $userId): bool {
    $stmt = $pdo->prepare('DELETE FROM users WHERE user_id = ?');
    $stmt->execute([$userId]);
    return $stmt->rowCount() === 1;
}

function linkStudentToUser(PDO $pdo, int $studentId, int $userId): void {
    $stmt = $pdo->prepare('UPDATE students SET user_id = ? WHERE student_id = ?');
    $stmt->execute([$userId, $studentId]);
}
