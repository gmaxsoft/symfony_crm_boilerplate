<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Modules\Access\Entity\Role;
use PHPUnit\Framework\TestCase;

final class RoleTest extends TestCase
{
    public function testConstants(): void
    {
        self::assertSame('Administrator', Role::ADMINISTRATOR);
        self::assertSame('Pracownik administracyjny', Role::EMPLOYEE_ADMIN);
        self::assertSame('Handlowiec', Role::SALESPERSON);
    }

    public function testAllRolesContainsAllThreeRoles(): void
    {
        self::assertCount(3, Role::ALL_ROLES);
        self::assertContains(Role::ADMINISTRATOR, Role::ALL_ROLES);
        self::assertContains(Role::EMPLOYEE_ADMIN, Role::ALL_ROLES);
        self::assertContains(Role::SALESPERSON, Role::ALL_ROLES);
    }

    public function testIdIsNullByDefault(): void
    {
        $role = new Role();
        self::assertNull($role->getId());
    }

    public function testSetAndGetName(): void
    {
        $role = (new Role())->setName('TestRola');
        self::assertSame('TestRola', $role->getName());
    }

    public function testSetNameReturnsStatic(): void
    {
        $role = new Role();
        self::assertSame($role, $role->setName('X'));
    }

    public function testDescriptionIsNullByDefault(): void
    {
        $role = new Role();
        self::assertNull($role->getDescription());
    }

    public function testSetAndGetDescription(): void
    {
        $role = (new Role())->setDescription('Opis roli');
        self::assertSame('Opis roli', $role->getDescription());
    }

    public function testSetDescriptionToNull(): void
    {
        $role = (new Role())->setDescription('Opis')->setDescription(null);
        self::assertNull($role->getDescription());
    }

    public function testUsersCollectionIsEmptyByDefault(): void
    {
        $role = new Role();
        self::assertCount(0, $role->getUsers());
    }
}
