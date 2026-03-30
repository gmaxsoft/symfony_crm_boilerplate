<?php

declare(strict_types=1);

namespace App\Tests\Integration\Dashboard;

use App\Modules\Access\Entity\Role;
use App\Modules\Customers\Entity\Customer;
use App\Tests\Integration\ApiTestCase;

final class DashboardApiTest extends ApiTestCase
{
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $role = $this->createRole(Role::ADMINISTRATOR);
        $this->createUser('admin@test.venom.pl', 'Test123!', $role);
        $this->token = $this->getToken('admin@test.venom.pl', 'Test123!');
    }

    // ── GET /api/dashboard/stats ──────────────────────────────────────────────

    public function testDashboardStatsReturnsExpectedStructure(): void
    {
        $data = $this->jsonRequest('GET', '/api/dashboard', null, $this->token);

        $this->assertStatusCode(200);
        self::assertSame('success', $data['status']);
        self::assertArrayHasKey('stats', $data['data']);
        self::assertArrayHasKey('customers', $data['data']['stats']);
        self::assertArrayHasKey('users',     $data['data']['stats']);
        self::assertIsInt($data['data']['stats']['customers']);
        self::assertIsInt($data['data']['stats']['users']);
    }

    public function testDashboardStatsReflectsActualCounts(): void
    {
        // Tworzymy 2 kontrahentów
        foreach (['Firma A', 'Firma B'] as $name) {
            $c = (new Customer())->setName($name);
            $this->em->persist($c);
        }
        $this->em->flush();

        $data = $this->jsonRequest('GET', '/api/dashboard', null, $this->token);

        $this->assertStatusCode(200);
        // Powinny być co najmniej 2 kontrahenci (+ ewentualne z innych testów)
        self::assertGreaterThanOrEqual(2, $data['data']['stats']['customers']);
        // Co najmniej 1 użytkownik (admin tworzony w setUp)
        self::assertGreaterThanOrEqual(1, $data['data']['stats']['users']);
    }

    public function testDashboardRequiresAuthentication(): void
    {
        $this->jsonRequest('GET', '/api/dashboard');
        $this->assertStatusCode(401);
    }
}
