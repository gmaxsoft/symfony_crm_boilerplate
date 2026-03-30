<?php

declare(strict_types=1);

namespace App\Modules\Access\Service;

use App\Modules\Access\Entity\Role;
use App\Modules\Access\Repository\RoleRepository;
use App\Shared\Exception\NotFoundException;

class RoleService
{
    public function __construct(private readonly RoleRepository $roleRepository) {}

    /** @return Role[] */
    public function findAll(): array
    {
        return $this->roleRepository->findAll();
    }

    public function findById(int $id): Role
    {
        return $this->roleRepository->find($id)
            ?? throw NotFoundException::forId('Role', $id);
    }

    public function create(string $name, ?string $description = null): Role
    {
        $role = (new Role())
            ->setName($name)
            ->setDescription($description);

        $this->roleRepository->save($role);
        return $role;
    }

    public function update(int $id, string $name, ?string $description = null): Role
    {
        $role = $this->findById($id);
        $role->setName($name)->setDescription($description);
        $this->roleRepository->save($role);
        return $role;
    }

    public function delete(int $id): void
    {
        $role = $this->findById($id);
        if ($role->getUsers()->count() > 0) {
            throw new \LogicException('Nie można usunąć roli przypisanej do użytkowników.');
        }
        $this->roleRepository->remove($role);
    }
}
