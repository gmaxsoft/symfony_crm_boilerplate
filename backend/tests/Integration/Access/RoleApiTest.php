<?php

declare(strict_types=1);

namespace App\Tests\Integration\Access;

use App\Modules\Access\Entity\Role;
use App\Tests\Integration\ApiTestCase;

final class RoleApiTest extends ApiTestCase
{
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $role        = $this->createRole(Role::ADMINISTRATOR);
        $this->createUser('admin@test.venom.pl', 'Test123!', $role);
        $this->token = $this->getToken('admin@test.venom.pl', 'Test123!');
    }

    // ── GET /api/access/roles ─────────────────────────────────────────────────

    public function testListRolesReturnsAll(): void
    {
        $this->createRole('Rola A', 'Opis A');
        $this->createRole('Rola B');

        $data = $this->jsonRequest('GET', '/api/access/roles', null, $this->token);

        $this->assertStatusCode(200);
        self::assertSame('success', $data['status']);
        // Są 2 nowe + 1 admin = 3 łącznie
        self::assertGreaterThanOrEqual(3, count($data['data']));
    }

    public function testListRolesRequiresAuthentication(): void
    {
        $this->jsonRequest('GET', '/api/access/roles');
        $this->assertStatusCode(401);
    }

    // ── POST /api/access/roles ────────────────────────────────────────────────

    public function testCreateRoleReturns201(): void
    {
        $data = $this->jsonRequest('POST', '/api/access/roles', [
            'name'        => 'Nowa Rola',
            'description' => 'Opis nowej roli',
        ], $this->token);

        $this->assertStatusCode(201);
        self::assertSame('success', $data['status']);
        self::assertSame('Nowa Rola',         $data['data']['name']);
        self::assertSame('Opis nowej roli',    $data['data']['description']);
        self::assertArrayHasKey('id', $data['data']);
    }

    public function testCreateRoleWithoutNameReturns422(): void
    {
        $this->jsonRequest('POST', '/api/access/roles', ['description' => 'X'], $this->token);
        $this->assertStatusCode(422);
    }

    public function testCreateRoleRequiresAuthentication(): void
    {
        $this->jsonRequest('POST', '/api/access/roles', ['name' => 'X']);
        $this->assertStatusCode(401);
    }

    // ── PUT /api/access/roles/{id} ────────────────────────────────────────────

    public function testUpdateRoleChangesNameAndDescription(): void
    {
        $role = $this->createRole('Stara Nazwa', 'Stary opis');

        $data = $this->jsonRequest('PUT', '/api/access/roles/' . $role->getId(), [
            'name'        => 'Nowa Nazwa',
            'description' => 'Nowy opis',
        ], $this->token);

        $this->assertStatusCode(200);
        self::assertSame('Nowa Nazwa', $data['data']['name']);
        self::assertSame('Nowy opis',  $data['data']['description']);
    }

    public function testUpdateNonExistentRoleReturns404(): void
    {
        $this->jsonRequest('PUT', '/api/access/roles/99999', ['name' => 'X'], $this->token);
        $this->assertStatusCode(404);
    }

    // ── DELETE /api/access/roles/{id} ─────────────────────────────────────────

    public function testDeleteRoleWithNoUsersReturns204(): void
    {
        $role = $this->createRole('Do usunięcia');

        $this->jsonRequest('DELETE', '/api/access/roles/' . $role->getId(), null, $this->token);

        $this->assertStatusCode(204);
    }

    public function testDeleteRoleWithUsersReturns409(): void
    {
        $role = $this->createRole('Rola z użytkownikiem');
        $this->createUser('user2@test.venom.pl', 'Pass123!', $role);

        $data = $this->jsonRequest('DELETE', '/api/access/roles/' . $role->getId(), null, $this->token);

        $this->assertStatusCode(409);
        self::assertStringContainsString('użytkowni', strtolower($data['message'] ?? ''));
    }

    public function testDeleteNonExistentRoleReturns404(): void
    {
        $this->jsonRequest('DELETE', '/api/access/roles/99999', null, $this->token);
        $this->assertStatusCode(404);
    }
}
