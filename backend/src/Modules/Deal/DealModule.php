<?php

declare(strict_types=1);

namespace App\Modules\Deal;

use App\Shared\Contract\ModuleInterface;

final class DealModule implements ModuleInterface
{
    public static function getName(): string
    {
        return 'deal';
    }

    public static function getDescription(): string
    {
        return 'Zarządzanie szansami sprzedaży i pipeline\'em.';
    }
}
