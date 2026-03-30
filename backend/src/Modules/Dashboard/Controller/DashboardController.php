<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Controller;

use App\Shared\Contract\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/dashboard', name: 'dashboard_')]
final class DashboardController extends AbstractApiController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->success([
            'module'  => 'Dashboard',
            'version' => '1.0.0',
            'status'  => 'VENOM CRM działa poprawnie.',
        ]);
    }
}
