<?php

declare(strict_types=1);

namespace App\Shared\Contract;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Bazowy kontroler API dla wszystkich modułów.
 * Dostarcza helpery do budowania odpowiedzi JSON.
 */
abstract class AbstractApiController extends AbstractController
{
    protected function success(mixed $data = null, int $status = 200): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'data'   => $data,
        ], $status);
    }

    protected function error(string $message, int $status = 400): JsonResponse
    {
        return $this->json([
            'status'  => 'error',
            'message' => $message,
        ], $status);
    }

    protected function paginated(array $items, int $total, int $page, int $perPage): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'data'   => $items,
            'meta'   => [
                'total'    => $total,
                'page'     => $page,
                'per_page' => $perPage,
                'pages'    => (int) ceil($total / $perPage),
            ],
        ]);
    }
}
