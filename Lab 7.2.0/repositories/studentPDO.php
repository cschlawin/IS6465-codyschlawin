<?php
function findStudentByEmail(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare('SELECT student_id,student_name,student_email,user_id FROM students WHERE student_email=? COLLATE NOCASE');
    $stmt->execute([trim($email)]);
    return $stmt->fetch() ?: null;
}
function findStudentByUserId(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare('SELECT student_id,student_name,student_email,user_id FROM students WHERE user_id=?');
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}
function insertStudentIfAbsent(PDO $pdo, string $name, string $email): void
{
    $stmt = $pdo->prepare('INSERT OR IGNORE INTO students (student_name,student_email) VALUES (?,?)');
    $stmt->execute([$name, $email]);
}
function linkStudentToUser(PDO $pdo, int $studentId, int $userId): void
{
    $stmt = $pdo->prepare('UPDATE students SET user_id=? WHERE student_id=? AND user_id IS NULL');
    $stmt->execute([$userId, $studentId]);
    if ($stmt->rowCount() !== 1) throw new DomainException('Student record already linked.');
}
function updateStudentProfileByUserId(PDO $pdo, int $userId, string $name, string $email): void
{
    $stmt = $pdo->prepare('UPDATE students SET student_name=?,student_email=? WHERE user_id=?');
    $stmt->execute([$name, $email, $userId]);
}
function deleteStudentByUserId(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare('DELETE FROM students WHERE user_id=?');
    $stmt->execute([$userId]);
}
