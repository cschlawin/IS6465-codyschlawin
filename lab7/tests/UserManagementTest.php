<?php

use PHPUnit\Framework\TestCase;

final class UserManagementTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = openTestDatabase();
    }

    public function testValidRegistrationCreatesActiveUser(): void
    {
        $id = registerUser($this->pdo, [
            'full_name' => 'Test Student',
            'email' => 'test@example.edu',
            'password' => 'Passw0rd!',
            'confirm_password' => 'Passw0rd!',
            'role' => 'Student',
        ]);

        $this->assertGreaterThan(0, $id);
        $user = findUserById($this->pdo, $id);
        $this->assertSame('Active', $user['status']);
        $this->assertSame('Student', $user['role']);
    }

    public function testDuplicateEmailIsRejected(): void
    {
        registerUser($this->pdo, [
            'full_name' => 'Test Student',
            'email' => 'dup@example.edu',
            'password' => 'Passw0rd!',
            'confirm_password' => 'Passw0rd!',
            'role' => 'Student',
        ]);

        $this->expectException(InvalidArgumentException::class);
        registerUser($this->pdo, [
            'full_name' => 'Duplicate',
            'email' => 'dup@example.edu',
            'password' => 'Passw0rd!',
            'confirm_password' => 'Passw0rd!',
            'role' => 'Student',
        ]);
    }

    public function testAdministratorSelfRegistrationIsDenied(): void
    {
        $this->expectException(InvalidArgumentException::class);
        registerUser($this->pdo, [
            'full_name' => 'Bad Role',
            'email' => 'admintry@example.edu',
            'password' => 'Passw0rd!',
            'confirm_password' => 'Passw0rd!',
            'role' => 'Administrator',
        ]);
    }

    public function testValidCredentialsAuthenticateActiveUser(): void
    {
        registerUser($this->pdo, [
            'full_name' => 'Login Student',
            'email' => 'login@example.edu',
            'password' => 'Passw0rd!',
            'confirm_password' => 'Passw0rd!',
            'role' => 'Student',
        ]);

        $user = authenticate($this->pdo, 'login@example.edu', 'Passw0rd!');
        $this->assertNotNull($user);
        $this->assertSame('Active', $user['status']);
    }

    public function testInvalidPasswordIsRejected(): void
    {
        registerUser($this->pdo, [
            'full_name' => 'Login Student',
            'email' => 'login2@example.edu',
            'password' => 'Passw0rd!',
            'confirm_password' => 'Passw0rd!',
            'role' => 'Student',
        ]);

        $this->assertNull(authenticate($this->pdo, 'login2@example.edu', 'wrong'));
    }

    public function testPasswordChangeReplacesOldPassword(): void
    {
        $id = registerUser($this->pdo, [
            'full_name' => 'Password Student',
            'email' => 'pw@example.edu',
            'password' => 'Passw0rd!',
            'confirm_password' => 'Passw0rd!',
            'role' => 'Student',
        ]);

        changePassword($this->pdo, $id, 'Passw0rd!', 'NewPass1!', 'NewPass1!');

        $this->assertNotNull(authenticate($this->pdo, 'pw@example.edu', 'NewPass1!'));
        $this->assertNull(authenticate($this->pdo, 'pw@example.edu', 'Passw0rd!'));
    }

    public function testInactiveUserCannotLogIn(): void
    {
        $id = registerUser($this->pdo, [
            'full_name' => 'Inactive Student',
            'email' => 'inactive@example.edu',
            'password' => 'Passw0rd!',
            'confirm_password' => 'Passw0rd!',
            'role' => 'Student',
        ]);

        setUserStatus($this->pdo, $id, 'Inactive');
        $this->assertNull(authenticate($this->pdo, 'inactive@example.edu', 'Passw0rd!'));
    }
}
