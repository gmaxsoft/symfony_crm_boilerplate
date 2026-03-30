<?php

declare(strict_types=1);

namespace App\Modules\Customers;

use App\Shared\Contract\ModuleInterface;

final class CustomersModule implements ModuleInterface
{
    public static function getName(): string { return 'customers'; }
    public static function getDescription(): string { return 'Zarzadzanie kontrahentami CRM.'; }
}
