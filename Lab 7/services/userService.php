<?php
/** UM-01..UM-09 business rules. Controllers supply the actor ID from a verified session. */
require_once __DIR__ . '/../repositories/userPDO.php';
require_once __DIR__ . '/../repositories/studentPDO.php';

function normalizedEmail(string $email): string
{
    return strtolower(trim($email));
}
function userTextLength(string $text): int
{
    return function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
}
function validPassword(string $password): bool
{
    // Exact password policy is unresolved in design; conservative classroom baseline.
    return strlen($password) >= 8 && strlen($password) <= 1024;
}
function validateRegistration(array $input): array
{
    $name = trim((string)($input['full_name'] ?? ''));
    $email = normalizedEmail((string)($input['email'] ?? ''));
    $password = (string)($input['password'] ?? '');
    $confirm = (string)($input['confirm_password'] ?? '');
    $role = (string)($input['role'] ?? '');
    $errors = [];
    if ($name === '' || userTextLength($name) > 100) $errors[] = 'Name is required (max 100 characters).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) $errors[] = 'Enter a valid email.';
    if (!validPassword($password)) $errors[] = 'Password must be 8 to 1024 bytes long.';
    if ($password !== $confirm) $errors[] = 'Password confirmation does not match.';
    if (!in_array($role, ['Student', 'Tutor'], true)) $errors[] = 'Choose Student or Tutor; public Administrator registration is prohibited.';
    return [$errors, compact('name', 'email', 'password', 'role')];
}
function registerUser(PDO $pdo, array $input): int
{
    [$errors, $data] = validateRegistration($input);
    if ($errors) throw new InvalidArgumentException(implode(' ', $errors));
    $pdo->beginTransaction();
    try {
        if (findUserByEmail($pdo, $data['email'])) throw new InvalidArgumentException('An account with that email already exists.');
        if ($data['role'] === 'Student' && findStudentByEmail($pdo, $data['email'])) {
            // Never allow an unverified signup to inherit Lab 6 legacy requests merely by typing an email.
            throw new InvalidArgumentException('A legacy student record already uses this email. Ask an administrator to verify the account link.');
        }
        $id = insertUser($pdo, $data['name'], $data['email'], password_hash($data['password'], PASSWORD_DEFAULT), $data['role']);
        if ($data['role'] === 'Student') {
            insertStudentIfAbsent($pdo, $data['name'], $data['email']);
            $student = findStudentByEmail($pdo, $data['email']);
            if (!$student) throw new RuntimeException('Student insert failed.');
            linkStudentToUser($pdo, (int)$student['student_id'], $id);
        }
        $pdo->commit();
        return $id;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        if ($e instanceof PDOException && str_contains($e->getMessage(), 'UNIQUE')) {
            throw new InvalidArgumentException('An account with that email already exists.', 0, $e);
        }
        throw $e;
    }
}
function authenticate(PDO $pdo, string $email, string $password): ?array
{
    $user = findUserByEmail($pdo, normalizedEmail($email));
    if (!$user || $user['status'] !== 'Active' || !password_verify($password, $user['password_hash'])) return null;
    return $user;
}
function updateOwnProfile(PDO $pdo, int $actorId, string $name, string $email): void
{
    $name = trim($name);
    $email = normalizedEmail($email);
    if ($name === '' || userTextLength($name) > 100) throw new InvalidArgumentException('Name is required (max 100 characters).');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) throw new InvalidArgumentException('Enter a valid email.');
    $pdo->beginTransaction();
    try {
        $user = findUserById($pdo, $actorId);
        if (!$user || $user['status'] !== 'Active') throw new DomainException('Active account required.');
        $duplicate = findUserByEmail($pdo, $email);
        if ($duplicate && (int)$duplicate['user_id'] !== $actorId) throw new InvalidArgumentException('This email is already registered.');
        if ($user['role'] === 'Student') {
            $legacy = findStudentByEmail($pdo, $email);
            if ($legacy && (int)$legacy['user_id'] !== $actorId) throw new InvalidArgumentException('That email is linked to another student record.');
        }
        updateUserProfile($pdo, $actorId, $name, $email);
        // A Student account's request identity must be synchronized with their profile.
        updateStudentProfileByUserId($pdo, $actorId, $name, $email);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
function changePassword(PDO $pdo, int $actorId, string $current, string $next, string $confirmation): void
{
    $user = findUserById($pdo, $actorId);
    if (!$user || $user['status'] !== 'Active' || !password_verify($current, $user['password_hash'])) {
        throw new InvalidArgumentException('Current password is incorrect.');
    }
    if (!validPassword($next)) throw new InvalidArgumentException('New password must be 8 to 1024 bytes long.');
    if ($next !== $confirmation) throw new InvalidArgumentException('New passwords do not match.');
    updateUserPassword($pdo, $actorId, password_hash($next, PASSWORD_DEFAULT));
}
function assertAdministrator(PDO $pdo, int $actorId): void
{
    $actor = findUserById($pdo, $actorId);
    if (!$actor || $actor['role'] !== 'Administrator' || $actor['status'] !== 'Active') {
        throw new DomainException('Administrator authorization required.');
    }
}
function setUserRole(PDO $pdo, int $adminId, int $targetId, string $role): void
{
    if (!in_array($role, ['Student', 'Tutor', 'Administrator'], true)) throw new InvalidArgumentException('Invalid role.');
    $pdo->beginTransaction();
    try {
        assertAdministrator($pdo, $adminId);
        $target = findUserById($pdo, $targetId);
        if (!$target) throw new InvalidArgumentException('Account not found.');
        if ($target['role'] !== $role) {
            if ($target['role'] === 'Administrator' && $target['status'] === 'Active' &&
                countActiveAdministrators($pdo, $targetId) < 1) {
                throw new InvalidArgumentException('Cannot demote the last active administrator.');
            }
            if ($role === 'Student' && !findStudentByUserId($pdo, $targetId)) {
                if (findStudentByEmail($pdo, $target['email'])) {
                    throw new InvalidArgumentException('A legacy student record needs ownership verification before changing this role.');
                }
                insertStudentIfAbsent($pdo, $target['full_name'], $target['email']);
                $student = findStudentByEmail($pdo, $target['email']);
                if (!$student) throw new RuntimeException('Failed to create student link.');
                linkStudentToUser($pdo, (int)$student['student_id'], $targetId);
            }
            updateUserRole($pdo, $targetId, $role);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
function setUserStatus(PDO $pdo, int $adminId, int $targetId, string $status): void
{
    if (!in_array($status, ['Active', 'Inactive'], true)) throw new InvalidArgumentException('Invalid account status.');
    $pdo->beginTransaction();
    try {
        assertAdministrator($pdo, $adminId);
        $target = findUserById($pdo, $targetId);
        if (!$target) throw new InvalidArgumentException('Account not found.');
        if ($target['status'] !== $status) {
            if ($target['role'] === 'Administrator' && $target['status'] === 'Active' &&
                $status === 'Inactive' && countActiveAdministrators($pdo, $targetId) < 1) {
                throw new InvalidArgumentException('Cannot deactivate the last active administrator.');
            }
            updateUserStatus($pdo, $targetId, $status);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
function attemptDeleteUser(PDO $pdo, int $adminId, int $targetId): void
{
    // Provisional CONSERVATIVE deletion policy: hard-delete only when no related requests exist.
    // Final retention policy is explicitly unresolved in the source design package.
    $pdo->beginTransaction();
    try {
        assertAdministrator($pdo, $adminId);
        $target = findUserById($pdo, $targetId);
        if (!$target) throw new InvalidArgumentException('Account not found.');
        if ($target['role'] === 'Administrator' && $target['status'] === 'Active' &&
            countActiveAdministrators($pdo, $targetId) < 1) {
            throw new InvalidArgumentException('Cannot delete the last active administrator.');
        }
        if (linkedRequestCount($pdo, $targetId) > 0) {
            throw new InvalidArgumentException('Cannot delete an account with tutoring requests. Deactivate it instead.');
        }
        deleteStudentByUserId($pdo, $targetId);
        deleteUser($pdo, $targetId);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
