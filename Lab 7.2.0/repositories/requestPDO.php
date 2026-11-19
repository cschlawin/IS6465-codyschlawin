<?php
/** Lab 6 request repository, extended with ownership filter on every read/write. */
function insertTutoringRequest(PDO $pdo, int $studentId, array $data): int
{
    $stmt = $pdo->prepare('INSERT INTO tutoring_requests(student_id,course,topic,preferred_date,preferred_time) VALUES(?,?,?,?,?)');
    $stmt->execute([$studentId,$data['course'],$data['topic'],$data['preferred_date'],$data['preferred_time']]);
    return (int)$pdo->lastInsertId();
}
function findRequest(PDO $pdo, int $id, ?int $ownerId = null): ?array
{
    $sql = 'SELECT r.*,s.student_name,s.student_email,s.user_id FROM tutoring_requests r JOIN students s ON r.student_id=s.student_id WHERE r.request_id=?';
    $params = [$id];
    if ($ownerId !== null) { $sql .= ' AND s.user_id=?'; $params[] = $ownerId; }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() ?: null;
}
function listRequests(PDO $pdo, string $course = '', string $status = '', ?int $ownerId = null): array
{
    $sql = 'SELECT r.*,s.student_name,s.student_email,s.user_id FROM tutoring_requests r JOIN students s ON r.student_id=s.student_id WHERE 1=1';
    $params = [];
    if ($ownerId !== null) { $sql .= ' AND s.user_id=?'; $params[] = $ownerId; }
    if ($course !== '') { $sql .= ' AND r.course=?'; $params[] = $course; }
    if ($status !== '') { $sql .= ' AND r.status=?'; $params[] = $status; }
    $stmt = $pdo->prepare($sql . ' ORDER BY r.request_id DESC');
    $stmt->execute($params);
    return $stmt->fetchAll();
}
function updateOpenRequest(PDO $pdo, int $id, array $data, ?int $ownerId = null): bool
{
    $sql = "UPDATE tutoring_requests SET course=?,topic=?,preferred_date=?,preferred_time=? WHERE request_id=? AND status='Open'";
    $params = [$data['course'],$data['topic'],$data['preferred_date'],$data['preferred_time'],$id];
    if ($ownerId !== null) {
        $sql .= ' AND student_id IN (SELECT student_id FROM students WHERE user_id=?)';
        $params[] = $ownerId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount() === 1;
}
function cancelOpenRequest(PDO $pdo, int $id, ?int $ownerId = null): bool
{
    $sql = "UPDATE tutoring_requests SET status='Cancelled' WHERE request_id=? AND status='Open'";
    $params = [$id];
    if ($ownerId !== null) {
        $sql .= ' AND student_id IN (SELECT student_id FROM students WHERE user_id=?)';
        $params[] = $ownerId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount() === 1;
}
