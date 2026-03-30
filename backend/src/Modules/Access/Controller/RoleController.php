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
    public function __construct(private readonly RoleService $roleService)
    {
    }

    /** GET /api/access/roles */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->success(array_map($this->serialize(...), $this->roleService->findAll()));
    }

    /** GET /api/access/roles/{id} */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        return $this->success($this->serialize($this->roleService->findById($id)));
    }

    /** POST /api/access/roles */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $name = trim($data['name'] ?? '');

        if ($name === '') {
            return $this->error('Pole "name" jest wymagane.', 422);
        }

        $role = $this->roleService->create($name, $data['description'] ?? null);

        return $this->success($this->serialize($role), 201);
    }

    /** PUT /api/access/roles/{id} */
    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = $request->toArray();
        $name = trim($data['name'] ?? '');

        if ($name === '') {
            return $this->error('Pole "name" jest wymagane.', 422);
        }

        $role = $this->roleService->update($id, $name, $data['description'] ?? null);

        return $this->success($this->serialize($role));
    }

    /** DELETE /api/access/roles/{id} */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->roleService->delete($id);
        } catch (\LogicException $e) {
            return $this->error($e->getMessage(), 409);
        }

        return $this->success(null, 204);
    }

    private function serialize(\App\Modules\Access\Entity\Role $r): array
    {
        return ['id' => $r->getId(), 'name' => $r->getName(), 'description' => $r->getDescription()];
    }
}
