<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Modules\Access\Entity\Role;
use App\Modules\Access\Repository\RoleRepository;
use App\Modules\Access\Service\RoleService;
use App\Modules\Admin\Entity\User;
use App\Shared\Exception\NotFoundException;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RoleServiceTest extends TestCase
{
    private RoleRepository&MockObject $roleRepo;
    private RoleService $service;

    protected function setUp(): void
    {
        $this->roleRepo = $this->createMock(RoleRepository::class);
        $this->service  = new RoleService($this->roleRepo);
    }

    // ── findAll ───────────────────────────────────────────────────────────────

    public function testFindAllDelegatesToRepository(): void
    {
        $roles = [(new Role())->setName('A'), (new Role())->setName('B')];
        $this->roleRepo->expects(self::once())->method('findAll')->willReturn($roles);

        self::assertSame($roles, $this->service->findAll());
    }

    // ── findById ──────────────────────────────────────────────────────────────

    public function testFindByIdReturnsRole(): void
    {
        $role = (new Role())->setName('Admin');
        $this->roleRepo->method('find')->with(1)->willReturn($role);

        self::assertSame($role, $this->service->findById(1));
    }

    public function testFindByIdThrowsNotFoundExceptionWhenMissing(): void
    {
        $this->roleRepo->method('find')->willReturn(null);

        $this->expectException(NotFoundException::class);
        $this->service->findById(99);
    }

    // ── create ────────────────────────────────────────────────────────────────

    public function testCreatePersistsAndReturnsRole(): void
    {
        $this->roleRepo->expects(self::once())->method('save');

        $role = $this->service->create('Nowa Rola', 'Opis');

        self::assertSame('Nowa Rola', $role->getName());
        self::assertSame('Opis', $role->getDescription());
    }

    public function testCreateWithNullDescription(): void
    {
        $this->roleRepo->method('save');

        $role = $this->service->create('Rola Bez Opisu');
        self::assertNull($role->getDescription());
    }

    // ── update ────────────────────────────────────────────────────────────────

    public function testUpdateChangesNameAndDescription(): void
    {
        $role = (new Role())->setName('Stara')->setDescription('Stary opis');
        $this->roleRepo->method('find')->willReturn($role);
        $this->roleRepo->expects(self::once())->method('save');

        $updated = $this->service->update(1, 'Nowa', 'Nowy opis');

        self::assertSame('Nowa', $updated->getName());
        self::assertSame('Nowy opis', $updated->getDescription());
    }

    public function testUpdateThrowsNotFoundExceptionWhenRoleMissing(): void
    {
        $this->roleRepo->method('find')->willReturn(null);

        $this->expectException(NotFoundException::class);
        $this->service->update(99, 'X');
    }

    // ── delete ────────────────────────────────────────────────────────────────

    public function testDeleteRoleWithNoUsers(): void
    {
        $role = (new Role())->setName('Do usunięcia');
        // getUsers() zwraca pustą kolekcję (brak użytkowników)
        $this->roleRepo->method('find')->willReturn($role);
        $this->roleRepo->expects(self::once())->method('remove')->with($role);

        $this->service->delete(1);
    }

    public function testDeleteThrowsLogicExceptionWhenRoleHasUsers(): void
    {
        $role = (new Role())->setName('Z użytkownikami');

        // Symulujemy kolekcję z jednym użytkownikiem
        $mockCollection = $this->createMock(\Doctrine\Common\Collections\Collection::class);
        $mockCollection->method('count')->willReturn(1);

        $reflection = new \ReflectionClass($role);
        $prop = $reflection->getProperty('users');
        $prop->setAccessible(true);
        $prop->setValue($role, $mockCollection);

        $this->roleRepo->method('find')->willReturn($role);

        $this->expectException(\LogicException::class);
        $this->service->delete(1);
    }

    public function testDeleteThrowsNotFoundExceptionWhenRoleMissing(): void
    {
        $this->roleRepo->method('find')->willReturn(null);

        $this->expectException(NotFoundException::class);
        $this->service->delete(99);
    }
}
