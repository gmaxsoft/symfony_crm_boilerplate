<?php

declare(strict_types=1);

namespace App\Modules\Customers\Controller;

use App\Modules\Customers\Service\CustomerService;
use App\Shared\Contract\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/customers', name: 'customers_')]
final class CustomerController extends AbstractApiController
{
    public function __construct(private readonly CustomerService $customerService) {}

    private function serialize(\App\Modules\Customers\Entity\Customer $c): array
    {
        $assignedTo = $c->getAssignedTo();
        return [
            'id'         => $c->getId(),
            'name'       => $c->getName(),
            'email'      => $c->getEmail(),
            'phone'      => $c->getPhone(),
            'nip'        => $c->getNip(),
            'address'    => $c->getAddress(),
            'city'       => $c->getCity(),
            'zipCode'    => $c->getZipCode(),
            'country'    => $c->getCountry(),
            'notes'      => $c->getNotes(),
            'status'     => $c->getStatus(),
            'assignedTo' => $assignedTo ? [
                'id'       => $assignedTo->getId(),
                'fullName' => $assignedTo->getFullName(),
            ] : null,
            'createdAt'  => $c->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt'  => $c->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }

    /** GET /api/customers */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $page    = max(1, (int) $request->query->get('page', 1));
        $perPage = min(100, max(5, (int) $request->query->get('perPage', 20)));
        $search  = $request->query->get('search');

        $result = $this->customerService->paginate($page, $perPage, $search ?: null);

        return $this->paginated(
            array_map($this->serialize(...), $result['items']),
            $result['total'],
            $page,
            $perPage
        );
    }

    /** GET /api/customers/{id} */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        return $this->success($this->serialize($this->customerService->findById($id)));
    }

    /** POST /api/customers */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $customer = $this->customerService->create($request->toArray());
        return $this->success($this->serialize($customer), 201);
    }

    /** PUT /api/customers/{id} */
    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $customer = $this->customerService->update($id, $request->toArray());
        return $this->success($this->serialize($customer));
    }

    /** DELETE /api/customers/{id} */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $this->customerService->delete($id);
        return $this->success(null, 204);
    }
}
