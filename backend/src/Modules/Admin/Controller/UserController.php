<?php

declare(strict_types=1);

namespace App\Modules\Admin\Controller;

use App\Modules\Admin\Service\UserService;
use App\Shared\Contract\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/users', name: 'admin_users_')]
final class UserController extends AbstractApiController
{
    public function __construct(private readonly UserService $userService) {}

    private function serialize(\App\Modules\Admin\Entity\User $u): array
    {
        return [
            'id'        => $u->getId(),
            'email'     => $u->getEmail(),
            'firstName' => $u->getFirstName(),
            'lastName'  => $u->getLastName(),
            'fullName'  => $u->getFullName(),
            'role'      => ['id' => $u->getRole()->getId(), 'name' => $u->getRole()->getName()],
            'isActive'  => $u->isActive(),
            'createdAt' => $u->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $u->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }

    /** GET /api/admin/users */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->success(array_map($this->serialize(...), $this->userService->findAll()));
    }

    /** GET /api/admin/users/{id} */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        return $this->success($this->serialize($this->userService->findById($id)));
    }

    /** POST /api/admin/users */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $d = $request->toArray();
        $user = $this->userService->create(
            email:         $d['email'] ?? '',
            plainPassword: $d['password'] ?? '',
            firstName:     $d['firstName'] ?? '',
            lastName:      $d['lastName'] ?? '',
            roleId:        (int) ($d['roleId'] ?? 0),
            isActive:      (bool) ($d['isActive'] ?? true),
        );
        return $this->success($this->serialize($user), 201);
    }

    /** PUT /api/admin/users/{id} */
    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $d = $request->toArray();
        $user = $this->userService->update(
            id:            $id,
            firstName:     $d['firstName'] ?? '',
            lastName:      $d['lastName'] ?? '',
            roleId:        (int) ($d['roleId'] ?? 0),
            isActive:      (bool) ($d['isActive'] ?? true),
            plainPassword: $d['password'] ?? null,
        );
        return $this->success($this->serialize($user));
    }

    /** DELETE /api/admin/users/{id} */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $this->userService->delete($id);
        return $this->success(null, 204);
    }
}
