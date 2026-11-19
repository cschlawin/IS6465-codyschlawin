<?php
// services/userService.php
// Business logic for user management (UM-01 to UM-10)

require_once __DIR__ . '/../repositories/userPDO.php';
require_once __DIR__ . '/../repositories/studentPDO.php';

function validateRegistration(array $input): array {
    $errors = [];
    $fullName = trim((string)($input['full_name'] ?? ''));
    $email = trim((string)($input['email'] ?? ''));
    $password = (string)($input['password'] ?? '');
    $confirm = (string)($input['confirm_password'] ?? '');
    $role = (string)($input['role'] ?? '');

    if ($fullName === '' || mb_strlen($fullName) > 100) {
        $errors[] = 'Full name is required (max 100 characters).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Password confirmation does not match.';
    }
    if (!in_array($role, ['Student', 'Tutor'], true)) {
        $errors[] = 'Account type must be Student or Tutor. Administrator cannot be self-registered.';
    }
    return [$errors, [
        'full_name' => $fullName,
        'email' => $email,
        'password' => $password,
        'role' => $role
    ]];
}

function registerUser(PDO $pdo, array $input): int {
    [$errors, $data] = validateRegistration($input);
    if ($errors) {
        throw new InvalidArgumentException(implode(' ', $errors));
    }
    if (findUserByEmail($pdo, $data['email'])) {
        throw new InvalidArgumentException('An account with that email already exists.');
    }
    $hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $userId = insertUser($pdo, $data['full_name'], $data['email'], $hash, $data['role']);

    if ($data['role'] === 'Student') {
        insertStudentIfAbsent($pdo, $data['full_name'], $data['email']);
        $student = findStudentByEmail($pdo, $data['email']);
        if ($student) {
            linkStudentToUser($pdo, (int)$student['student_id'], $userId);
        }
    }
    return $userId;
}

function authenticate(PDO $pdo, string $email, string $password): ?array {
    $user = findUserByEmail($pdo, $email);
    if (!$user || $user['status'] !== 'Active') {
        return null;
    }
    if (!password_verify($password, $user['password_hash'])) {
        return null;
    }
    return $user;
}

function changePassword(PDO $pdo, int $userId, string $current, string $newPassword, string $confirm): void {
    $user = findUserById($pdo, $userId);
    if (!$user || !password_verify($current, $user['password_hash'])) {
        throw new InvalidArgumentException('Current password is incorrect.');
    }
    if (strlen($newPassword) < 8) {
        throw new InvalidArgumentException('New password must be at least 8 characters.');
    }
    if ($newPassword !== $confirm) {
        throw new InvalidArgumentException('New password confirmation does not match.');
    }
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    if (!updateUserPassword($pdo, $userId, $hash)) {
        throw new RuntimeException('Failed to update password.');
    }
}

function updateOwnProfile(PDO $pdo, int $userId, string $fullName, string $email): void {
    $fullName = trim($fullName);
    $email = trim($email);
    if ($fullName === '' || mb_strlen($fullName) > 100) {
        throw new InvalidArgumentException('Full name is required (max 100 characters).');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Valid email is required.');
    }
    $existing = findUserByEmail($pdo, $email);
    if ($existing && (int)$existing['user_id'] !== $userId) {
        throw new InvalidArgumentException('That email is already in use.');
    }
    if (!updateUserProfile($pdo, $userId, $fullName, $email)) {
        throw new RuntimeException('Failed to update profile.');
    }
    $user = findUserById($pdo, $userId);
    if ($user && $user['role'] === 'Student') {
        $stmt = $pdo->prepare('UPDATE students SET student_name = ?, student_email = ? WHERE user_id = ?');
        $stmt->execute([$fullName, $email, $userId]);
    }
}

function setUserRole(PDO $pdo, int $adminId, int $targetId, string $newRole): void {
    if (!in_array($newRole, ['Student', 'Tutor', 'Administrator'], true)) {
        throw new InvalidArgumentException('Invalid role.');
    }
    $target = findUserById($pdo, $targetId);
    if (!$target) {
        throw new InvalidArgumentException('User not found.');
    }
    if ($target['role'] === 'Administrator' && $newRole !== 'Administrator') {
        if (countActiveAdministrators($pdo, $targetId) < 1) {
            throw new InvalidArgumentException('Cannot change the role of the last active administrator.');
        }
    }
    if (!updateUserRole($pdo, $targetId, $newRole)) {
        throw new RuntimeException('Failed to update role.');
    }
}

function setUserStatus(PDO $pdo, int $targetId, string $newStatus): void {
    if (!in_array($newStatus, ['Active', 'Inactive'], true)) {
        throw new InvalidArgumentException('Invalid status.');
    }
    $target = findUserById($pdo, $targetId);
    if (!$target) {
        throw new InvalidArgumentException('User not found.');
    }
    if ($target['role'] === 'Administrator' && $newStatus === 'Inactive') {
        if (countActiveAdministrators($pdo, $targetId) < 1) {
            throw new InvalidArgumentException('Cannot deactivate the last active administrator.');
        }
    }
    if (!updateUserStatus($pdo, $targetId, $newStatus)) {
        throw new RuntimeException('Failed to update status.');
    }
}

function attemptDeleteUser(PDO $pdo, int $targetId): void {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM tutoring_requests r JOIN students s ON r.student_id = s.student_id WHERE s.user_id = ?');
    $stmt->execute([$targetId]);
    if ((int)$stmt->fetchColumn() > 0) {
        throw new InvalidArgumentException('Cannot delete account that has related tutoring requests. Deactivate instead.');
    }
    $target = findUserById($pdo, $targetId);
    if ($target && $target['role'] === 'Administrator' && countActiveAdministrators($pdo, $targetId) < 1) {
        throw new InvalidArgumentException('Cannot delete the last active administrator.');
    }
    if (!deleteUser($pdo, $targetId)) {
        throw new RuntimeException('Failed to delete user.');
    }
}
