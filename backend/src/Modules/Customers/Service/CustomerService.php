<?php

declare(strict_types=1);

namespace App\Modules\Customers\Service;

use App\Modules\Admin\Repository\UserRepository;
use App\Modules\Customers\Entity\Customer;
use App\Modules\Customers\Repository\CustomerRepository;
use App\Shared\Exception\NotFoundException;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function paginate(int $page = 1, int $perPage = 20, ?string $search = null): array
    {
        return $this->customerRepository->paginate($page, $perPage, $search);
    }

    public function findById(int $id): Customer
    {
        return $this->customerRepository->find($id)
            ?? throw NotFoundException::forId('Customer', $id);
    }

    public function create(array $data): Customer
    {
        $customer = new Customer();
        $this->hydrate($customer, $data);
        $this->customerRepository->save($customer);

        return $customer;
    }

    public function update(int $id, array $data): Customer
    {
        $customer = $this->findById($id);
        $this->hydrate($customer, $data);
        $this->customerRepository->save($customer);

        return $customer;
    }

    public function delete(int $id): void
    {
        $this->customerRepository->remove($this->findById($id));
    }

    private function hydrate(Customer $customer, array $data): void
    {
        isset($data['name']) && $customer->setName($data['name']);
        isset($data['email']) && $customer->setEmail($data['email']);
        isset($data['phone']) && $customer->setPhone($data['phone']);
        isset($data['nip']) && $customer->setNip($data['nip']);
        isset($data['address']) && $customer->setAddress($data['address']);
        isset($data['city']) && $customer->setCity($data['city']);
        isset($data['zipCode']) && $customer->setZipCode($data['zipCode']);
        isset($data['country']) && $customer->setCountry($data['country']);
        isset($data['notes']) && $customer->setNotes($data['notes']);
        isset($data['status']) && $customer->setStatus($data['status']);

        if (isset($data['assignedToId'])) {
            $user = $data['assignedToId']
                ? ($this->userRepository->find($data['assignedToId'])
                    ?? throw NotFoundException::forId('User', $data['assignedToId']))
                : null;
            $customer->setAssignedTo($user);
        }
    }
}
