<?php

declare(strict_types=1);

namespace App\Modules\Access\Repository;

use App\Modules\Access\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Role>
 */
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    public function findByName(string $name): ?Role
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function save(Role $role, bool $flush = true): void
    {
        $this->getEntityManager()->persist($role);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Role $role, bool $flush = true): void
    {
        $this->getEntityManager()->remove($role);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
