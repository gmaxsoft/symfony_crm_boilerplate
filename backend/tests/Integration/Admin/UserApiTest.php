<?php

declare(strict_types=1);

namespace App\Tests\Integration\Admin;

use App\Modules\Access\Entity\Role;
use App\Tests\Integration\ApiTestCase;

final class UserApiTest extends ApiTestCase
{
    private string $token;
    private int $roleId;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $role = $this->createRole(Role::ADMINISTRATOR);
        $this->roleId = (int) $role->getId();
        $this->createUser('admin@test.venom.pl', 'Test123!', $role, 'Admin', 'Test');
        $this->token = $this->getToken('admin@test.venom.pl', 'Test123!');
    }

    // ── GET /api/admin/users ──────────────────────────────────────────────────

    public function testListUsersReturnsAllUsers(): void
    {
        $data = $this->jsonRequest('GET', '/api/admin/users', null, $this->token);

        $this->assertStatusCode(200);
        self::assertSame('success', $data['status']);
        self::assertIsArray($data['data']);
        self::assertGreaterThanOrEqual(1, \count($data['data']));
    }

    public function testListUsersRequiresAuthentication(): void
    {
        $this->jsonRequest('GET', '/api/admin/users');
        $this->assertStatusCode(401);
    }

    // ── POST /api/admin/users ─────────────────────────────────────────────────

    public function testCreateUserReturns201(): void
    {
        $data = $this->jsonRequest('POST', '/api/admin/users', [
            'email' => 'nowyuser@venom.pl',
            'password' => 'Haslo123!',
            'firstName' => 'Nowy',
            'lastName' => 'Uzytkownik',
            'roleId' => $this->roleId,
            'isActive' => true,
        ], $this->token);

        $this->assertStatusCode(201);
        self::assertSame('success', $data['status']);
        self::assertSame('nowyuser@venom.pl', $data['data']['email']);
        self::assertSame('Nowy', $data['data']['firstName']);
        self::assertSame('Uzytkownik', $data['data']['lastName']);
        self::assertTrue($data['data']['isActive']);
        self::assertArrayHasKey('id', $data['data']);
    }

    public function testCreateUserWithMissingFieldsReturns422(): void
    {
        $this->jsonRequest('POST', '/api/admin/users', ['email' => 'only@email.pl'], $this->token);
        $this->assertStatusCode(422);
    }

    public function testCreateUserWithInvalidRoleIdReturns404(): void
    {
        $this->jsonRequest('POST', '/api/admin/users', [
            'email' => 'x@y.pl',
            'password' => 'Haslo123!',
            'firstName' => 'X',
            'lastName' => 'Y',
            'roleId' => 99999,
        ], $this->token);

        $this->assertStatusCode(404);
    }

    public function testCreateUserRequiresAuthentication(): void
    {
        $this->jsonRequest('POST', '/api/admin/users', ['email' => 'x@y.pl']);
        $this->assertStatusCode(401);
    }

    // ── PUT /api/admin/users/{id} ─────────────────────────────────────────────

    public function testUpdateUserChangesFields(): void
    {
        $role = $this->createRole(Role::SALESPERSON);
        $user = $this->createUser('handlowiec@test.pl', 'Pass123!', $role, 'Stare', 'Nazwisko');

        $data = $this->jsonRequest('PUT', '/api/admin/users/' . (int) $user->getId(), [
            'firstName' => 'Nowe',
            'lastName' => 'ImieNazwisko',
            'roleId' => $this->roleId,
            'isActive' => false,
        ], $this->token);

        $this->assertStatusCode(200);
        self::assertSame('Nowe', $data['data']['firstName']);
        self::assertSame('ImieNazwisko', $data['data']['lastName']);
        self::assertFalse($data['data']['isActive']);
    }

    public function testUpdateNonExistentUserReturns404(): void
    {
        $this->jsonRequest('PUT', '/api/admin/users/99999', [
            'firstName' => 'X', 'lastName' => 'Y', 'roleId' => $this->roleId, 'isActive' => true,
        ], $this->token);

        $this->assertStatusCode(404);
    }

    // ── DELETE /api/admin/users/{id} ──────────────────────────────────────────

    public function testDeleteUserReturns204(): void
    {
        $role = $this->createRole(Role::EMPLOYEE_ADMIN);
        $user = $this->createUser('dodelete@test.pl', 'Pass123!', $role);

        $this->jsonRequest('DELETE', '/api/admin/users/' . (int) $user->getId(), null, $this->token);

        $this->assertStatusCode(204);
    }

    public function testDeleteNonExistentUserReturns404(): void
    {
        $this->jsonRequest('DELETE', '/api/admin/users/99999', null, $this->token);
        $this->assertStatusCode(404);
    }
}
