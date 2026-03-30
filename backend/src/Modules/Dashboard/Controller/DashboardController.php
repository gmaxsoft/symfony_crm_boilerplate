<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Controller;

use App\Modules\Admin\Repository\UserRepository;
use App\Modules\Customers\Repository\CustomerRepository;
use App\Shared\Contract\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/dashboard', name: 'dashboard_')]
final class DashboardController extends AbstractApiController
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * GET /api/dashboard
     * Zwraca statystyki ogólne systemu CRM.
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->success([
            'stats' => [
                'customers' => $this->customerRepository->count([]),
                'users' => $this->userRepository->count(['isActive' => true]),
            ],
            'notice' => 'Moduł Dashboard jest w budowie.',
        ]);
    }
}
