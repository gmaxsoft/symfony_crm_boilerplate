<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Modules\Access\Entity\Role;
use App\Modules\Admin\Entity\User;
use App\Modules\Admin\Repository\UserRepository;
use App\Modules\Customers\Entity\Customer;
use App\Modules\Customers\Repository\CustomerRepository;
use App\Modules\Customers\Service\CustomerService;
use App\Shared\Exception\NotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CustomerServiceTest extends TestCase
{
    private CustomerRepository&MockObject $customerRepo;
    private UserRepository&MockObject     $userRepo;
    private CustomerService               $service;

    protected function setUp(): void
    {
        $this->customerRepo = $this->createMock(CustomerRepository::class);
        $this->userRepo     = $this->createMock(UserRepository::class);
        $this->service      = new CustomerService($this->customerRepo, $this->userRepo);
    }

    private function makeUser(): User
    {
        return (new User())
            ->setFirstName('Jan')->setLastName('Kowalski')
            ->setEmail('jan@venom.pl')
            ->setRole((new Role())->setName(Role::SALESPERSON));
    }

    // ── paginate ──────────────────────────────────────────────────────────────

    public function testPaginateDelegatesToRepository(): void
    {
        $result = ['data' => [], 'meta' => ['total' => 0]];
        $this->customerRepo->expects(self::once())
            ->method('paginate')
            ->with(1, 20, null)
            ->willReturn($result);

        self::assertSame($result, $this->service->paginate(1, 20));
    }

    public function testPaginateWithSearch(): void
    {
        $this->customerRepo->expects(self::once())
            ->method('paginate')
            ->with(2, 10, 'ACME')
            ->willReturn([]);

        $this->service->paginate(2, 10, 'ACME');
    }

    // ── findById ──────────────────────────────────────────────────────────────

    public function testFindByIdReturnsCustomer(): void
    {
        $customer = (new Customer())->setName('ACME');
        $this->customerRepo->method('find')->with(5)->willReturn($customer);

        self::assertSame($customer, $this->service->findById(5));
    }

    public function testFindByIdThrowsNotFoundExceptionWhenMissing(): void
    {
        $this->customerRepo->method('find')->willReturn(null);

        $this->expectException(NotFoundException::class);
        $this->service->findById(999);
    }

    // ── create ────────────────────────────────────────────────────────────────

    public function testCreateWithMinimalData(): void
    {
        $this->customerRepo->expects(self::once())->method('save');

        $customer = $this->service->create(['name' => 'Firma Test']);

        self::assertSame('Firma Test', $customer->getName());
        self::assertSame('active', $customer->getStatus());
    }

    public function testCreateWithAssignedUser(): void
    {
        $user = $this->makeUser();
        $this->userRepo->method('find')->with(3)->willReturn($user);
        $this->customerRepo->method('save');

        $customer = $this->service->create([
            'name'         => 'Firma XYZ',
            'assignedToId' => 3,
        ]);

        self::assertSame($user, $customer->getAssignedTo());
    }

    public function testCreateThrowsNotFoundExceptionForMissingAssignedUser(): void
    {
        $this->userRepo->method('find')->willReturn(null);

        $this->expectException(NotFoundException::class);
        $this->service->create(['name' => 'X', 'assignedToId' => 99]);
    }

    public function testCreateWithAssignedToIdNullClearsAssignment(): void
    {
        $this->customerRepo->method('save');

        $customer = $this->service->create([
            'name'         => 'Firma',
            'assignedToId' => null,
        ]);

        self::assertNull($customer->getAssignedTo());
    }

    // ── update ────────────────────────────────────────────────────────────────

    public function testUpdateChangesFields(): void
    {
        $customer = (new Customer())->setName('Stara')->setStatus('active');
        $this->customerRepo->method('find')->willReturn($customer);
        $this->customerRepo->expects(self::once())->method('save');

        $updated = $this->service->update(1, ['name' => 'Nowa', 'status' => 'inactive']);

        self::assertSame('Nowa', $updated->getName());
        self::assertSame('inactive', $updated->getStatus());
    }

    public function testUpdateThrowsNotFoundExceptionWhenCustomerMissing(): void
    {
        $this->customerRepo->method('find')->willReturn(null);

        $this->expectException(NotFoundException::class);
        $this->service->update(99, ['name' => 'X']);
    }

    // ── delete ────────────────────────────────────────────────────────────────

    public function testDeleteRemovesCustomer(): void
    {
        $customer = (new Customer())->setName('Do usunięcia');
        $this->customerRepo->method('find')->willReturn($customer);
        $this->customerRepo->expects(self::once())->method('remove')->with($customer);

        $this->service->delete(1);
    }

    public function testDeleteThrowsNotFoundExceptionWhenMissing(): void
    {
        $this->customerRepo->method('find')->willReturn(null);

        $this->expectException(NotFoundException::class);
        $this->service->delete(99);
    }
}
