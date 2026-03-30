<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Modules\Access\Entity\Role;
use App\Tests\Integration\ApiTestCase;

final class AuthApiTest extends ApiTestCase
{
    private const EMAIL = 'admin@test.venom.pl';
    private const PASSWORD = 'Test123!';
    private Role $role;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->role = $this->createRole(Role::ADMINISTRATOR, 'Administrator testowy');
        $this->createUser(self::EMAIL, self::PASSWORD, $this->role, 'Admin', 'Test');
    }

    // ── POST /api/auth/login ──────────────────────────────────────────────────

    public function testLoginWithValidCredentialsReturnsToken(): void
    {
        $data = $this->jsonRequest('POST', '/api/auth/login', [
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
        ]);

        $this->assertStatusCode(200);
        self::assertArrayHasKey('token', $data);
        self::assertNotEmpty($data['token']);
    }

    public function testLoginWithWrongPasswordReturns401(): void
    {
        $this->jsonRequest('POST', '/api/auth/login', [
            'email' => self::EMAIL,
            'password' => 'ZleHaslo!',
        ]);

        $this->assertStatusCode(401);
    }

    public function testLoginWithUnknownEmailReturns401(): void
    {
        $this->jsonRequest('POST', '/api/auth/login', [
            'email' => 'nieistnieje@venom.pl',
            'password' => 'cokolwiek',
        ]);

        $this->assertStatusCode(401);
    }

    public function testLoginWithMissingFieldsReturns4xx(): void
    {
        // Puste dane logowania — Symfony zwraca 400 (brak poprawnego JSON)
        // lub 401 (brak credentials) w zależności od zawartości body
        $this->jsonRequest('POST', '/api/auth/login', ['email' => '', 'password' => '']);
        $status = $this->client->getResponse()->getStatusCode();
        self::assertContains($status, [400, 401], "Oczekiwano 400 lub 401, otrzymano: {$status}");
    }

    // ── GET /api/auth/me ──────────────────────────────────────────────────────

    public function testMeWithValidTokenReturnsUserData(): void
    {
        $token = $this->getToken(self::EMAIL, self::PASSWORD);
        $data = $this->jsonRequest('GET', '/api/auth/me', null, $token);

        $this->assertStatusCode(200);
        self::assertSame('success', $data['status']);
        self::assertSame(self::EMAIL, $data['data']['email']);
        self::assertSame('Admin', $data['data']['firstName']);
        self::assertSame('Test', $data['data']['lastName']);
        self::assertSame('Admin Test', $data['data']['fullName']);
        self::assertSame(Role::ADMINISTRATOR, $data['data']['role']);
    }

    public function testMeWithoutTokenReturns401(): void
    {
        $this->jsonRequest('GET', '/api/auth/me');
        $this->assertStatusCode(401);
    }

    public function testMeWithInvalidTokenReturns401(): void
    {
        $this->jsonRequest('GET', '/api/auth/me', null, 'invalid.jwt.token');
        $this->assertStatusCode(401);
    }
}
