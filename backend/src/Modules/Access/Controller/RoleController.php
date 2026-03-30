<?php

declare(strict_types=1);

namespace App\Modules\Access\Controller;

use App\Modules\Access\Service\RoleService;
use App\Shared\Contract\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/access/roles', name: 'access_roles_')]
final class RoleController extends AbstractApiController
{
    public function __construct(private readonly RoleService $roleService) {}

    /** GET /api/access/roles */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $roles = array_map(
            fn ($r) => ['id' => $r->getId(), 'name' => $r->getName(), 'description' => $r->getDescription()],
            $this->roleService->findAll()
        );
        return $this->success($roles);
    }

    /** GET /api/access/roles/{id} */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $r = $this->roleService->findById($id);
        return $this->success(['id' => $r->getId(), 'name' => $r->getName(), 'description' => $r->getDescription()]);
    }

    /** POST /api/access/roles */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $role = $this->roleService->create($data['name'] ?? '', $data['description'] ?? null);
        return $this->success(['id' => $role->getId(), 'name' => $role->getName()], 201);
    }

    /** PUT /api/access/roles/{id} */
    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = $request->toArray();
        $role = $this->roleService->update($id, $data['name'] ?? '', $data['description'] ?? null);
        return $this->success(['id' => $role->getId(), 'name' => $role->getName()]);
    }

    /** DELETE /api/access/roles/{id} */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $this->roleService->delete($id);
        return $this->success(null, 204);
    }
}
