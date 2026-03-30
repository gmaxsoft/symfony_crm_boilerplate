<?php

declare(strict_types=1);

namespace App\Modules\Customers\Repository;

use App\Modules\Customers\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    /**
     * Stronicowana lista kontrahentów z opcjonalnym filtrowaniem.
     *
     * @return array{items: Customer[], total: int}
     */
    public function paginate(int $page = 1, int $perPage = 20, ?string $search = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.assignedTo', 'u')
            ->addSelect('u')
            ->orderBy('c.createdAt', 'DESC');

        if ($search) {
            $qb->andWhere('c.name LIKE :search OR c.email LIKE :search OR c.nip LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $qb->setFirstResult(($page - 1) * $perPage)
           ->setMaxResults($perPage);

        $paginator = new Paginator($qb);

        return [
            'items' => iterator_to_array($paginator),
            'total' => \count($paginator),
        ];
    }

    public function save(Customer $customer, bool $flush = true): void
    {
        $this->getEntityManager()->persist($customer);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Customer $customer, bool $flush = true): void
    {
        $this->getEntityManager()->remove($customer);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
