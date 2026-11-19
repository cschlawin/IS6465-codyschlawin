<?php
/** Existing UC01/UC02 tutoring workflows retained from Lab 6. */
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../repositories/requestPDO.php';
require_once __DIR__ . '/../repositories/userPDO.php';
require_once __DIR__ . '/../repositories/studentPDO.php';
function decodeTopic(string $topic): array
{
    $value = json_decode($topic, true);
    return is_array($value) && is_string($value['reason'] ?? null) && is_array($value['topics'] ?? null)
        ? $value : ['reason' => $topic, 'topics' => []];
}
function validateSubmission(array $input, bool $requireStudent = true): array
{
    $errors = [];
    $name = trim((string)($input['student_name'] ?? ''));
    $email = trim((string)($input['student_email'] ?? ''));
    $course = (string)($input['course'] ?? '');
    $reason = trim((string)($input['reason'] ?? ''));
    $date = (string)($input['preferreddate'] ?? '');
    $time = (string)($input['preferredtime'] ?? '');
    $topics = $input['topics'] ?? [];
    if ($requireStudent && $name === '') $errors[] = 'Student name is required.';
    if ($requireStudent && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid student email is required.';
    if (!in_array($course, ['IS 6465','IS 6495','IS 6570'], true)) $errors[] = 'Choose a valid course.';
    if ($msg = validatePreferredTime($time)) $errors[] = $msg;
    if ($msg = validateTopics($topics)) $errors[] = $msg;
    if ($msg = validateReason($reason)) $errors[] = $msg;
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    if (!$parsed || $parsed->format('Y-m-d') !== $date || $parsed < new DateTimeImmutable('today')) {
        $errors[] = 'Choose a valid date today or later.';
    }
    $data = ['student_name' => $name, 'student_email' => $email, 'course' => $course,
        'topic' => json_encode(['reason' => $reason, 'topics' => array_values(is_array($topics) ? $topics : [])]),
        'preferred_date' => $date, 'preferred_time' => $time];
    return [$errors, $data];
}
function submitRequest(PDO $pdo, array $input, ?int $ownerId = null): int
{
    [$errors, $data] = validateSubmission($input);
    if ($errors) throw new InvalidArgumentException(implode(' ', $errors));
    $pdo->beginTransaction();
    try {
        if ($ownerId !== null) {
            $user = findPublicUserById($pdo, $ownerId);
            if (!$user || $user['role'] !== 'Student' || $user['status'] !== 'Active' ||
                strcasecmp($user['email'], $data['student_email']) !== 0) throw new DomainException('Student identity mismatch.');
            $student = findStudentByUserId($pdo, $ownerId);
            if (!$student) throw new DomainException('Student account is not linked. Contact an administrator.');
        } else {
            // Legacy code path is used by isolated UC01 regression tests only, not any HTTP handler.
            insertStudentIfAbsent($pdo, $data['student_name'], $data['student_email']);
            $student = findStudentByEmail($pdo, $data['student_email']);
        }
        if (!$student) throw new RuntimeException('Student lookup failed.');
        $id = insertTutoringRequest($pdo, (int)$student['student_id'], $data);
        $pdo->commit();
        return $id;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
function reviseRequest(PDO $pdo, int $id, array $input, ?int $ownerId = null): bool
{
    [$errors, $data] = validateSubmission($input, false);
    if ($errors) throw new InvalidArgumentException(implode(' ', $errors));
    return updateOpenRequest($pdo, $id, $data, $ownerId);
}
