<?php

declare(strict_types=1);

namespace App\Modules\Company;

use App\Shared\Contract\ModuleInterface;

final class CompanyModule implements ModuleInterface
{
    public static function getName(): string
    {
        return 'company';
    }

    public static function getDescription(): string
    {
        return 'Zarządzanie firmami i kontami klientów.';
    }
}
