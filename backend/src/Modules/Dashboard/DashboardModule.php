<?php

declare(strict_types=1);

namespace App\Modules\Dashboard;

use App\Shared\Contract\ModuleInterface;

final class DashboardModule implements ModuleInterface
{
    public static function getName(): string
    {
        return 'dashboard';
    }

    public static function getDescription(): string
    {
        return 'Pulpit główny z podsumowaniem danych CRM.';
    }
}
