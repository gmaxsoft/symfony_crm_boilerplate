<?php

declare(strict_types=1);

namespace App\Modules\Customers;

use App\Shared\Contract\ModuleInterface;

final class CustomersModule implements ModuleInterface
{
    #[\Override]
    public static function getName(): string
    {
        return 'customers';
    }

    #[\Override]
    public static function getDescription(): string
    {
        return 'Zarzadzanie kontrahentami CRM.';
    }
}
