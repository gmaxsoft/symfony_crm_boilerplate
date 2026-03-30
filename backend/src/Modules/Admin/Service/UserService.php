<?php

declare(strict_types=1);

namespace App\Modules\Admin\Service;

use App\Modules\Access\Repository\RoleRepository;
use App\Modules\Admin\Entity\User;
use App\Modules\Admin\Repository\UserRepository;
use App\Shared\Exception\NotFoundException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private readonly UserRepository              $userRepository,
        private readonly RoleRepository              $roleRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    /** @return User[] */
    public function findAll(): array
    {
        return $this->userRepository->findBy([], ['lastName' => 'ASC']);
    }

    public function findById(int $id): User
    {
        return $this->userRepository->find($id)
            ?? throw NotFoundException::forId('User', $id);
    }

    public function create(
        string $email,
        string $plainPassword,
        string $firstName,
        string $lastName,
        int    $roleId,
        bool   $isActive = true,
    ): User {
        $role = $this->roleRepository->find($roleId)
            ?? throw NotFoundException::forId('Role', $roleId);

        $user = (new User())
            ->setEmail($email)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRole($role)
            ->setIsActive($isActive);

        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        $this->userRepository->save($user);
        return $user;
    }

    public function update(
        int     $id,
        string  $firstName,
        string  $lastName,
        int     $roleId,
        bool    $isActive,
        ?string $plainPassword = null,
    ): User {
        $user = $this->findById($id);
        $role = $this->roleRepository->find($roleId)
            ?? throw NotFoundException::forId('Role', $roleId);

        $user->setFirstName($firstName)
             ->setLastName($lastName)
             ->setRole($role)
             ->setIsActive($isActive);

        if ($plainPassword) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        }

        $this->userRepository->save($user);
        return $user;
    }

    public function delete(int $id): void
    {
        $this->userRepository->remove($this->findById($id));
    }
}
