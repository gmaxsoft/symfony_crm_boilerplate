<?php

declare(strict_types=1);

namespace App\Tests\Integration\Customers;

use App\Modules\Access\Entity\Role;
use App\Modules\Customers\Entity\Customer;
use App\Tests\Integration\ApiTestCase;

final class CustomerApiTest extends ApiTestCase
{
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $role = $this->createRole(Role::ADMINISTRATOR);
        $this->createUser('admin@test.venom.pl', 'Test123!', $role);
        $this->token = $this->getToken('admin@test.venom.pl', 'Test123!');
    }

    private function createCustomer(string $name, string $status = 'active'): Customer
    {
        $customer = (new Customer())->setName($name)->setStatus($status);
        $this->em->persist($customer);
        $this->em->flush();
        return $customer;
    }

    // ── GET /api/customers ────────────────────────────────────────────────────

    public function testListCustomersReturnsPagedResult(): void
    {
        $this->createCustomer('ACME Sp. z o.o.');
        $this->createCustomer('Beta Tech');

        $data = $this->jsonRequest('GET', '/api/customers?page=1&perPage=20', null, $this->token);

        $this->assertStatusCode(200);
        self::assertSame('success', $data['status']);
        self::assertIsArray($data['data']);
        self::assertArrayHasKey('meta', $data);
        self::assertGreaterThanOrEqual(2, $data['meta']['total']);
    }

    public function testListCustomersWithSearch(): void
    {
        $this->createCustomer('ZnalazloSieACME');
        $this->createCustomer('NiePasujeFirma');

        $data = $this->jsonRequest('GET', '/api/customers?search=ZnalazloSie', null, $this->token);

        $this->assertStatusCode(200);
        $names = array_column($data['data'], 'name');
        self::assertContains('ZnalazloSieACME', $names);
        self::assertNotContains('NiePasujeFirma', $names);
    }

    public function testListCustomersRequiresAuthentication(): void
    {
        $this->jsonRequest('GET', '/api/customers');
        $this->assertStatusCode(401);
    }

    // ── POST /api/customers ───────────────────────────────────────────────────

    public function testCreateCustomerReturns201(): void
    {
        $data = $this->jsonRequest('POST', '/api/customers', [
            'name'    => 'Nowy Kontrahent Sp. z o.o.',
            'email'   => 'kontakt@nowy.pl',
            'phone'   => '+48 500 600 700',
            'nip'     => '1234567890',
            'city'    => 'Kraków',
            'country' => 'Polska',
            'status'  => 'prospect',
        ], $this->token);

        $this->assertStatusCode(201);
        self::assertSame('success',                      $data['status']);
        self::assertSame('Nowy Kontrahent Sp. z o.o.',   $data['data']['name']);
        self::assertSame('kontakt@nowy.pl',              $data['data']['email']);
        self::assertSame('prospect',                     $data['data']['status']);
        self::assertArrayHasKey('id', $data['data']);
    }

    public function testCreateCustomerWithoutNameReturns422(): void
    {
        $this->jsonRequest('POST', '/api/customers', ['email' => 'x@y.pl'], $this->token);
        $this->assertStatusCode(422);
    }

    public function testCreateCustomerRequiresAuthentication(): void
    {
        $this->jsonRequest('POST', '/api/customers', ['name' => 'X']);
        $this->assertStatusCode(401);
    }

    // ── GET /api/customers/{id} ───────────────────────────────────────────────

    public function testGetCustomerByIdReturnsDetails(): void
    {
        $customer = $this->createCustomer('ACME Detail');

        $data = $this->jsonRequest('GET', '/api/customers/' . $customer->getId(), null, $this->token);

        $this->assertStatusCode(200);
        self::assertSame('ACME Detail', $data['data']['name']);
    }

    public function testGetNonExistentCustomerReturns404(): void
    {
        $this->jsonRequest('GET', '/api/customers/99999', null, $this->token);
        $this->assertStatusCode(404);
    }

    // ── PUT /api/customers/{id} ───────────────────────────────────────────────

    public function testUpdateCustomerChangesFields(): void
    {
        $customer = $this->createCustomer('Stara Nazwa', 'active');

        $data = $this->jsonRequest('PUT', '/api/customers/' . $customer->getId(), [
            'name'   => 'Nowa Nazwa',
            'status' => 'inactive',
        ], $this->token);

        $this->assertStatusCode(200);
        self::assertSame('Nowa Nazwa', $data['data']['name']);
        self::assertSame('inactive',   $data['data']['status']);
    }

    public function testUpdateNonExistentCustomerReturns404(): void
    {
        $this->jsonRequest('PUT', '/api/customers/99999', ['name' => 'X'], $this->token);
        $this->assertStatusCode(404);
    }

    // ── DELETE /api/customers/{id} ────────────────────────────────────────────

    public function testDeleteCustomerReturns204(): void
    {
        $customer = $this->createCustomer('Do usunięcia');

        $this->jsonRequest('DELETE', '/api/customers/' . $customer->getId(), null, $this->token);

        $this->assertStatusCode(204);

        // Weryfikacja że naprawdę zniknął
        $this->em->clear();
        $deleted = $this->em->find(Customer::class, $customer->getId());
        self::assertNull($deleted);
    }

    public function testDeleteNonExistentCustomerReturns404(): void
    {
        $this->jsonRequest('DELETE', '/api/customers/99999', null, $this->token);
        $this->assertStatusCode(404);
    }
}
