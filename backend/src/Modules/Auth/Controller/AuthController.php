<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controller;

use App\Shared\Contract\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Modules\Admin\Entity\User;

#[Route('/api/auth', name: 'auth_')]
final class AuthController extends AbstractApiController
{
    /**
     * POST /api/auth/login
     * Obsługiwany przez Symfony Security (json_login) + LexikJWT.
     * Ten endpoint istnieje tylko jako dokumentacja trasy — faktyczną obsługę
     * przejmuje firewall skonfigurowany w security.yaml.
     */
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Symfony Security + LexikJWT obsługuje tę trasę automatycznie.
        // Kod poniżej nie zostanie wykonany — odpowiedź zwraca handler JWT.
        return $this->error('Nieoczekiwany błąd logowania.', 500);
    }

    /**
     * GET /api/auth/me
     * Zwraca dane aktualnie zalogowanego użytkownika.
     */
    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->error('Brak autoryzacji.', 401);
        }

        return $this->success([
            'id'        => $user->getId(),
            'email'     => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName'  => $user->getLastName(),
            'fullName'  => $user->getFullName(),
            'role'      => $user->getRole()->getName(),
            'isActive'  => $user->isActive(),
        ]);
    }
}
