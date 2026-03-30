<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Modules\Access\Entity\Role;
use App\Modules\Access\Repository\RoleRepository;
use App\Modules\Admin\Entity\User;
use App\Modules\Admin\Repository\UserRepository;
use App\Modules\Admin\Service\UserService;
use App\Shared\Exception\NotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserServiceTest extends TestCase
{
    private UserRepository&MockObject $userRepo;
    private RoleRepository&MockObject $roleRepo;
    private UserPasswordHasherInterface&MockObject $hasher;
    private UserService $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->userRepo = $this->createMock(UserRepository::class);
        $this->roleRepo = $this->createMock(RoleRepository::class);
        $this->hasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->service = new UserService($this->userRepo, $this->roleRepo, $this->hasher);
    }

    // ── findAll ───────────────────────────────────────────────────────────────

    public function testFindAllDelegatesToRepository(): void
    {
        $users = [(new User())->setEmail('a@b.pl'), (new User())->setEmail('c@d.pl')];
        $this->userRepo->expects(self::once())
            ->method('findBy')
            ->with([], ['lastName' => 'ASC'])
            ->willReturn($users);

        self::assertSame($users, $this->service->findAll());
    }

    // ── findById ──────────────────────────────────────────────────────────────

    public function testFindByIdReturnsUser(): void
    {
        $user = (new User())->setEmail('x@y.pl');
        $this->userRepo->method('find')->with(7)->willReturn($user);

        self::assertSame($user, $this->service->findById(7));
    }

    public function testFindByIdThrowsNotFoundExceptionWhenMissing(): void
    {
        $this->userRepo->method('find')->willReturn(null);

        $this->expectException(NotFoundException::class);
        $this->service->findById(99);
    }

    // ── create ────────────────────────────────────────────────────────────────

    public function testCreateHashesPasswordAndPersistsUser(): void
    {
        $role = $this->makeRole();
        $this->roleRepo->method('find')->with(1)->willReturn($role);
        $this->hasher->method('hashPassword')->willReturn('$2y$hashed');
        $this->userRepo->expects(self::once())->method('save');

        $user = $this->service->create('jan@venom.pl', 'Haslo123!', 'Jan', 'Kowalski', 1);

        self::assertSame('jan@venom.pl', $user->getEmail());
        self::assertSame('Jan', $user->getFirstName());
        self::assertSame('Kowalski', $user->getLastName());
        self::assertSame('$2y$hashed', $user->getPassword());
        self::assertSame($role, $user->getRole());
        self::assertTrue($user->isActive());
    }

    public function testCreateWithIsActiveFalse(): void
    {
        $this->roleRepo->method('find')->willReturn($this->makeRole());
        $this->hasher->method('hashPassword')->willReturn('hashed');
        $this->userRepo->method('save');

        $user = $this->service->create('x@y.pl', 'pass', 'X', 'Y', 1, false);

        self::assertFalse($user->isActive());
    }

    public function testCreateThrowsNotFoundExceptionForMissingRole(): void
    {
        $this->roleRepo->method('find')->willReturn(null);

        $this->expectException(NotFoundException::class);
        $this->service->create('x@y.pl', 'pass', 'X', 'Y', 999);
    }

    // ── update ────────────────────────────────────────────────────────────────

    public function testUpdateChangesUserFields(): void
    {
        $role = $this->makeRole(Role::SALESPERSON);
        $newRole = $this->makeRole(Role::EMPLOYEE_ADMIN);
        $user = (new User())->setEmail('a@b.pl')->setFirstName('Old')->setLastName('Name')->setRole($role);

        $this->userRepo->method('find')->willReturn($user);
        $this->roleRepo->method('find')->willReturn($newRole);
        $this->userRepo->expects(self::once())->method('save');

        $updated = $this->service->update(1, 'New', 'Name2', 2, false);

        self::assertSame('New', $updated->getFirstName());
        self::assertSame('Name2', $updated->getLastName());
        self::assertSame($newRole, $updated->getRole());
        self::assertFalse($updated->isActive());
    }

    public function testUpdateHashesPasswordWhenProvided(): void
    {
        $user = (new User())->setEmail('a@b.pl')->setFirstName('X')->setLastName('Y')
                            ->setRole($this->makeRole())->setPassword('old_hash');
        $this->userRepo->method('find')->willReturn($user);
        $this->roleRepo->method('find')->willReturn($this->makeRole());
        $this->hasher->expects(self::once())
            ->method('hashPassword')
            ->willReturn('new_hash');
        $this->userRepo->method('save');

        $this->service->update(1, 'X', 'Y', 1, true, 'NewPassword123!');

        self::assertSame('new_hash', $user->getPassword());
    }

    public function testUpdateDoesNotHashPasswordWhenNull(): void
    {
        $user = (new User())->setEmail('a@b.pl')->setFirstName('X')->setLastName('Y')
                            ->setRole($this->makeRole())->setPassword('unchanged');
        $this->userRepo->method('find')->willReturn($user);
        $this->roleRepo->method('find')->willReturn($this->makeRole());
        $this->hasher->expects(self::never())->method('hashPassword');
        $this->userRepo->method('save');

        $this->service->update(1, 'X', 'Y', 1, true);

        self::assertSame('unchanged', $user->getPassword());
    }

    public function testUpdateThrowsNotFoundExceptionForMissingUser(): void
    {
        $this->userRepo->method('find')->willReturn(null);

        $this->expectException(NotFoundException::class);
        $this->service->update(99, 'X', 'Y', 1, true);
    }

    public function testUpdateThrowsNotFoundExceptionForMissingRole(): void
    {
        $user = (new User())->setEmail('a@b.pl')->setFirstName('X')->setLastName('Y')
                            ->setRole($this->makeRole());
        $this->userRepo->method('find')->willReturn($user);
        $this->roleRepo->method('find')->willReturn(null);

        $this->expectException(NotFoundException::class);
        $this->service->update(1, 'X', 'Y', 99, true);
    }

    // ── delete ────────────────────────────────────────────────────────────────

    public function testDeleteRemovesUser(): void
    {
        $user = (new User())->setEmail('del@venom.pl')->setFirstName('X')->setLastName('Y')
                            ->setRole($this->makeRole());
        $this->userRepo->method('find')->willReturn($user);
        $this->userRepo->expects(self::once())->method('remove')->with($user);

        $this->service->delete(1);
    }

    public function testDeleteThrowsNotFoundExceptionWhenMissing(): void
    {
        $this->userRepo->method('find')->willReturn(null);

        $this->expectException(NotFoundException::class);
        $this->service->delete(99);
    }

    private function makeRole(string $name = Role::ADMINISTRATOR): Role
    {
        return (new Role())->setName($name);
    }
}
