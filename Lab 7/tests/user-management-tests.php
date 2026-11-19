<?php
/**
 * In-memory acceptance tests for UM-01..UM-10 + ownership security.
 * Execute: php tests/user-management-tests.php
 * Never reads or writes the included working database/tutoring.db.
 */
ob_start(); // Prevent HTTP headers from being sent before session tests.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/../services/requestService.php';
require_once __DIR__ . '/../includes/auth.php';
$passed = 0;
$failed = 0;
function check(bool $condition, string $label): void
{
    global $passed, $failed;
    if ($condition) { $passed++; echo "PASS: $label\n"; }
    else { $failed++; echo "FAIL: $label\n"; }
}
function expectFailure(callable $action, string $expectedClass = InvalidArgumentException::class): bool
{
    try { $action(); return false; }
    catch (Throwable $e) { return $e instanceof $expectedClass; }
}
function createFixture(array $overrides = []): array
{
    return array_merge([
        'full_name'=>'Student Example','email'=>'student1@example.test',
        'password'=>'SecurePass1!', 'confirm_password'=>'SecurePass1!','role'=>'Student'
    ], $overrides);
}
try {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys=ON');
    $pdo->exec(file_get_contents(__DIR__ . '/../database/setup-database.sql'));
    $admin = insertUser($pdo, 'Admin Example', 'admin@example.test', password_hash('AdminStrong1!', PASSWORD_DEFAULT), 'Administrator');
    $secondAdmin = insertUser($pdo, 'Second Admin', 'other-admin@example.test', password_hash('AdminStrong2!', PASSWORD_DEFAULT), 'Administrator');
    check((int)$pdo->query('PRAGMA foreign_keys')->fetchColumn() === 1, 'DB: foreign keys enabled');

    // UM-01 Registration, positive/negative, rollback and password storage.
    $studentId = registerUser($pdo, createFixture());
    $student = findUserById($pdo, $studentId);
    check($student['status'] === 'Active' && $student['role'] === 'Student', 'UM-01 Student is Active');
    check($student['password_hash'] !== 'SecurePass1!' && password_verify('SecurePass1!', $student['password_hash']), 'UM-01 secure password hash');
    $linked = findStudentByUserId($pdo, $studentId);
    check($linked && $linked['student_email'] === 'student1@example.test', 'UM-01 Student-to-user link exists');
    $tutorId = registerUser($pdo, createFixture(['full_name'=>'Tutor Example', 'email'=>'tutor@example.test','role'=>'Tutor']));
    check(findUserById($pdo, $tutorId)['status'] === 'Active', 'UM-01 Tutor becomes Active');
    check(expectFailure(fn()=>registerUser($pdo, createFixture())), 'UM-01 duplicate email rejected');
    check(expectFailure(fn()=>registerUser($pdo, createFixture(['email'=>'bad@example.test','role'=>'Administrator']))), 'UM-01 public Administrator denied');
    check(expectFailure(fn()=>registerUser($pdo, createFixture(['email'=>'bad@example.test','confirm_password'=>'mismatch']))), 'UM-01 mismatched passwords rejected');
    check(expectFailure(fn()=>registerUser($pdo, createFixture(['email'=>'bad@example.test','full_name'=>'']))), 'UM-01 missing name rejected');
    check(findUserByEmail($pdo, 'bad@example.test') === null, 'UM-01 invalid registration does not write user');
    insertStudentIfAbsent($pdo, 'Historical Student', 'legacy@example.test');
    check(expectFailure(fn()=>registerUser($pdo, createFixture(['email'=>'legacy@example.test']))), 'UM-01 legacy unverified ownership not silently inherited');
    check(findUserByEmail($pdo,'legacy@example.test') === null, 'UM-01 legacy collision rolled back');

    // UM-02 own profile: read from trusted session identity (not URL or POST ID).
    loginUser($student);
    $verified = currentUser($pdo);
    check($verified && (int)$verified['user_id'] === $studentId, 'UM-02 own session identifies correct user');
    check(!array_key_exists('password_hash', $verified), 'UM-02 profile query excludes hash');
    check(currentUserId() === $studentId, 'UM-02 identity is session-derived');

    // UM-03 admin listing and filters.
    $matches = listUsers($pdo, 'Tutor Example', 'Tutor', 'Active');
    check(count($matches) === 1 && (int)$matches[0]['user_id'] === $tutorId, 'UM-03 name/role/status filtering');
    check(listUsers($pdo, 'no-such-person') === [], 'UM-03 no matches returns empty result');
    check(!array_key_exists('password_hash', $matches[0]), 'UM-03 admin listing omits password hash');
    check(expectFailure(fn()=>assertAdministrator($pdo, $studentId), DomainException::class), 'UM-03 student cannot administer users');

    // UM-04 user can edit own name/email, but not role; validate duplicates.
    updateOwnProfile($pdo, $studentId, 'Updated Student', 'new-student@example.test');
    $changed = findUserById($pdo, $studentId);
    check($changed['full_name']==='Updated Student' && $changed['email']==='new-student@example.test', 'UM-04 own name and email saved');
    check($changed['role']==='Student', 'UM-04 role not changed by profile update');
    check(findStudentByUserId($pdo,$studentId)['student_email']==='new-student@example.test', 'UM-04 student email synchronized');
    check(expectFailure(fn()=>updateOwnProfile($pdo,$studentId,'Updated Student','admin@example.test')), 'UM-04 duplicate email rejected');
    check(findUserById($pdo,$studentId)['email']==='new-student@example.test', 'UM-04 invalid update leaves original email');
    check(findUserById($pdo,$tutorId)['email']==='tutor@example.test', 'UM-04 unrelated user unchanged');

    // UM-05 password change requires correct old and confirmation.
    check(expectFailure(fn()=>changePassword($pdo,$studentId,'wrong','NextSecure1!','NextSecure1!')), 'UM-05 incorrect current password rejected');
    check(expectFailure(fn()=>changePassword($pdo,$studentId,'SecurePass1!','NextSecure1!','not-match')), 'UM-05 confirmation mismatch rejected');
    changePassword($pdo,$studentId,'SecurePass1!','NextSecure1!','NextSecure1!');
    check(authenticate($pdo,'new-student@example.test','NextSecure1!') !== null, 'UM-05 new password authenticates');
    check(authenticate($pdo,'new-student@example.test','SecurePass1!') === null, 'UM-05 old password stops authenticating');

    // UM-06 admin role updates with last-admin and unauthorized protections.
    check(expectFailure(fn()=>setUserRole($pdo,$studentId,$tutorId,'Student'),DomainException::class), 'UM-06 nonadmin cannot change roles');
    setUserRole($pdo,$admin,$tutorId,'Student');
    check(findUserById($pdo,$tutorId)['role']==='Student' && findStudentByUserId($pdo,$tutorId)!==null, 'UM-06 Tutor-to-Student creates linked student');
    setUserRole($pdo,$admin,$tutorId,'Tutor');
    check(findUserById($pdo,$tutorId)['role']==='Tutor', 'UM-06 role can be changed back');
    setUserRole($pdo,$admin,$secondAdmin,'Tutor');
    check(expectFailure(fn()=>setUserRole($pdo,$admin,$admin,'Tutor')), 'UM-06 last active administrator protected');
    check(findUserById($pdo,$admin)['role']==='Administrator', 'UM-06 rejected role update leaves administrator');

    // UM-07 status change and inactive login restriction.
    check(expectFailure(fn()=>setUserStatus($pdo,$studentId,$tutorId,'Inactive'),DomainException::class), 'UM-07 nonadmin status change denied');
    setUserStatus($pdo,$admin,$tutorId,'Inactive');
    check(findUserById($pdo,$tutorId)['status']==='Inactive', 'UM-07 status persisted');
    check(authenticate($pdo,'tutor@example.test','SecurePass1!')===null, 'UM-07 inactive account cannot log in');
    setUserStatus($pdo,$admin,$tutorId,'Active');
    check(authenticate($pdo,'tutor@example.test','SecurePass1!')!==null, 'UM-07 reactivation restores login');
    check(expectFailure(fn()=>setUserStatus($pdo,$admin,$admin,'Inactive')), 'UM-07 last active administrator cannot deactivate');

    // UC01 and ownership fixture supports UM-08 and lecture authorization expectations.
    $requestData = [
        'student_name'=>'Updated Student','student_email'=>'new-student@example.test',
        'course'=>'IS 6465','topics'=>['Exam review','Study plan'],
        'reason'=>'Please help me review PHP validation.','preferredtime'=>'Morning',
        'preferreddate'=>(new DateTimeImmutable('+10 days'))->format('Y-m-d'),
    ];
    $requestId = submitRequest($pdo,$requestData,$studentId);
    check(findRequest($pdo,$requestId,$studentId)!==null, 'UC01 linked student can create own request');
    check(findRequest($pdo,$requestId,$tutorId)===null, 'Security: another student cannot view record');
    check(count(listRequests($pdo,'','',$studentId))===1 && count(listRequests($pdo,'','',$tutorId))===0, 'Security: owner-based list filtering');
    check(!cancelOpenRequest($pdo,$requestId,$tutorId), 'Security: another student cannot cancel record');
    check(!reviseRequest($pdo,$requestId,$requestData,$tutorId), 'Security: another student cannot edit record');
    check(findRequest($pdo,$requestId,$studentId)['status']==='Open', 'Security: rejected changes preserve status');

    // UM-08 retention and role constraints.
    check(expectFailure(fn()=>attemptDeleteUser($pdo,$studentId,$tutorId),DomainException::class), 'UM-08 nonadmin cannot delete');
    check(expectFailure(fn()=>attemptDeleteUser($pdo,$admin,$studentId)), 'UM-08 related tutoring requests block deletion');
    check(findUserById($pdo,$studentId)!==null && findRequest($pdo,$requestId)!==null, 'UM-08 blocked deletion retains account and request');
    check(expectFailure(fn()=>attemptDeleteUser($pdo,$admin,$admin)), 'UM-08 last administrator cannot be deleted');
    $orphanId = registerUser($pdo,createFixture(['full_name'=>'No Requests','email'=>'empty@example.test','role'=>'Tutor']));
    attemptDeleteUser($pdo,$admin,$orphanId);
    check(findUserById($pdo,$orphanId)===null, 'UM-08 unreferenced account deleted under conservative policy');

    // UM-09 session ID rotation and safe redirect protection.
    $previousId = session_id();
    loginUser(findUserById($pdo,$admin));
    check(session_id() !== $previousId && currentUserId() === $admin, 'UM-09 login regenerates session ID');
    check(isset($_SESSION['csrf_token']) && strlen($_SESSION['csrf_token']) === 64, 'UM-09 CSRF token in trusted session');
    check(safeLocalRedirect('https://evil.test/steal')==='profile.php' && safeLocalRedirect('//evil.test')==='profile.php', 'Security: open redirect denied');
    check(safeLocalRedirect('requests.php?course=IS+6465')==='requests.php?course=IS+6465', 'Security: local redirect permitted');
    check(session_get_cookie_params()['httponly']===true, 'Security: session cookie HttpOnly');
    check(session_get_cookie_params()['samesite']==='Lax', 'Security: session cookie SameSite Lax');
    // Timeouts can be tested without waiting 20 minutes.
    $_SESSION['last_activity']=time()-1201;
    check(currentUser($pdo)===null && currentUserId()===null, 'UM-09 / UM-02 idle session expires after 20 minutes');
    startSecureSession();
    loginUser(findUserById($pdo,$admin));

    // UM-10 invalidate server session and browser cookie.
    logoutSession();
    check(currentUserId()===null && $_SESSION===[], 'UM-10 logout clears authenticated session');
    check(session_status()!==PHP_SESSION_ACTIVE, 'UM-10 server session destroyed');
} catch (Throwable $e) {
    $failed++;
    echo 'FAIL: Test harness error: ' . $e->getMessage() . PHP_EOL;
}
$captured = ob_get_clean();
echo $captured;
echo "\nResults: $passed passed, $failed failed\n";
exit($failed > 0 ? 1 : 0);
