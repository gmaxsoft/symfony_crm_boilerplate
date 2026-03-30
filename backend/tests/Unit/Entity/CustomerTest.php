<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Modules\Access\Entity\Role;
use App\Modules\Admin\Entity\User;
use App\Modules\Customers\Entity\Customer;
use PHPUnit\Framework\TestCase;

final class CustomerTest extends TestCase
{
    public function testIdIsNullByDefault(): void
    {
        self::assertNull((new Customer())->getId());
    }

    public function testSetAndGetName(): void
    {
        $customer = (new Customer())->setName('ACME Sp. z o.o.');
        self::assertSame('ACME Sp. z o.o.', $customer->getName());
    }

    public function testOptionalFieldsAreNullByDefault(): void
    {
        $c = new Customer();
        self::assertNull($c->getEmail());
        self::assertNull($c->getPhone());
        self::assertNull($c->getNip());
        self::assertNull($c->getAddress());
        self::assertNull($c->getCity());
        self::assertNull($c->getZipCode());
        self::assertNull($c->getCountry());
        self::assertNull($c->getNotes());
        self::assertNull($c->getAssignedTo());
        self::assertNull($c->getUpdatedAt());
    }

    public function testDefaultStatusIsActive(): void
    {
        self::assertSame('active', (new Customer())->getStatus());
    }

    public function testSetStatus(): void
    {
        $customer = (new Customer())->setStatus('inactive');
        self::assertSame('inactive', $customer->getStatus());
    }

    public function testSetAllContactFields(): void
    {
        $c = (new Customer())
            ->setName('Firma XYZ')
            ->setEmail('kontakt@firma.pl')
            ->setPhone('+48 123 456 789')
            ->setNip('1234567890')
            ->setAddress('ul. Testowa 1')
            ->setCity('Warszawa')
            ->setZipCode('00-001')
            ->setCountry('Polska')
            ->setNotes('Ważny klient');

        self::assertSame('Firma XYZ', $c->getName());
        self::assertSame('kontakt@firma.pl', $c->getEmail());
        self::assertSame('+48 123 456 789', $c->getPhone());
        self::assertSame('1234567890', $c->getNip());
        self::assertSame('ul. Testowa 1', $c->getAddress());
        self::assertSame('Warszawa', $c->getCity());
        self::assertSame('00-001', $c->getZipCode());
        self::assertSame('Polska', $c->getCountry());
        self::assertSame('Ważny klient', $c->getNotes());
    }

    public function testSetAndGetAssignedTo(): void
    {
        $user = (new User())->setFirstName('Jan')->setLastName('Kowalski')
                                ->setEmail('jan@venom.pl')
                                ->setRole((new Role())->setName(Role::SALESPERSON));
        $customer = (new Customer())->setAssignedTo($user);
        self::assertSame($user, $customer->getAssignedTo());
    }

    public function testSetAssignedToNull(): void
    {
        $user = (new User())->setFirstName('X')->setLastName('Y')
                                ->setEmail('x@y.pl')
                                ->setRole((new Role())->setName(Role::SALESPERSON));
        $customer = (new Customer())->setAssignedTo($user)->setAssignedTo(null);
        self::assertNull($customer->getAssignedTo());
    }

    public function testCreatedAtIsSetOnConstruct(): void
    {
        $before = new \DateTimeImmutable();
        $c = new Customer();
        $after = new \DateTimeImmutable();

        self::assertGreaterThanOrEqual($before, $c->getCreatedAt());
        self::assertLessThanOrEqual($after, $c->getCreatedAt());
    }

    public function testOnPreUpdateSetsUpdatedAt(): void
    {
        $c = new Customer();
        self::assertNull($c->getUpdatedAt());

        $c->onPreUpdate();

        self::assertInstanceOf(\DateTimeImmutable::class, $c->getUpdatedAt());
    }

    public function testSettersReturnStatic(): void
    {
        $c = new Customer();
        self::assertSame($c, $c->setName('X'));
        self::assertSame($c, $c->setEmail('x@y.pl'));
        self::assertSame($c, $c->setStatus('prospect'));
    }
}
