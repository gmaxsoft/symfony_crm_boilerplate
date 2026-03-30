<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Modules\Access\Entity\Role;
use App\Modules\Admin\Entity\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    private function makeRole(string $name): Role
    {
        return (new Role())->setName($name);
    }

    public function testIdIsNullByDefault(): void
    {
        self::assertNull((new User())->getId());
    }

    public function testSetAndGetEmail(): void
    {
        $user = (new User())->setEmail('jan@venom.pl');
        self::assertSame('jan@venom.pl', $user->getEmail());
    }

    public function testSetAndGetFirstAndLastName(): void
    {
        $user = (new User())->setFirstName('Jan')->setLastName('Kowalski');
        self::assertSame('Jan', $user->getFirstName());
        self::assertSame('Kowalski', $user->getLastName());
    }

    public function testGetFullName(): void
    {
        $user = (new User())->setFirstName('Anna')->setLastName('Nowak');
        self::assertSame('Anna Nowak', $user->getFullName());
    }

    public function testIsActiveTrueByDefault(): void
    {
        self::assertTrue((new User())->isActive());
    }

    public function testSetIsActive(): void
    {
        $user = (new User())->setIsActive(false);
        self::assertFalse($user->isActive());
    }

    public function testSetAndGetRole(): void
    {
        $role = $this->makeRole(Role::ADMINISTRATOR);
        $user = (new User())->setRole($role);
        self::assertSame($role, $user->getRole());
    }

    public function testSetAndGetPassword(): void
    {
        $user = (new User())->setPassword('hashed_pw');
        self::assertSame('hashed_pw', $user->getPassword());
    }

    public function testCreatedAtIsSetOnConstruct(): void
    {
        $before = new \DateTimeImmutable();
        $user   = new User();
        $after  = new \DateTimeImmutable();

        self::assertGreaterThanOrEqual($before, $user->getCreatedAt());
        self::assertLessThanOrEqual($after, $user->getCreatedAt());
    }

    public function testUpdatedAtIsNullByDefault(): void
    {
        self::assertNull((new User())->getUpdatedAt());
    }

    public function testGetUserIdentifier(): void
    {
        $user = (new User())->setEmail('test@venom.pl');
        self::assertSame('test@venom.pl', $user->getUserIdentifier());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('rolesNormalizationProvider')]
    public function testGetRolesNormalization(string $roleName, string $expectedSymfonyRole): void
    {
        $user = (new User())->setRole($this->makeRole($roleName));
        $roles = $user->getRoles();

        self::assertContains('ROLE_USER', $roles);
        self::assertContains($expectedSymfonyRole, $roles);
    }

    /** @return array<string, array{string, string}> */
    public static function rolesNormalizationProvider(): array
    {
        return [
            'Administrator'            => [Role::ADMINISTRATOR, 'ROLE_ADMINISTRATOR'],
            'Pracownik administracyjny' => [Role::EMPLOYEE_ADMIN, 'ROLE_PRACOWNIK_ADMINISTRACYJNY'],
            'Handlowiec'               => [Role::SALESPERSON, 'ROLE_HANDLOWIEC'],
        ];
    }

    public function testEraseCredentialsDoesNothing(): void
    {
        $user = (new User())->setPassword('secret');
        $user->eraseCredentials();
        // Hasło NIE jest kasowane — celowe (stateless JWT nie wymaga kasowania)
        self::assertSame('secret', $user->getPassword());
    }
}
