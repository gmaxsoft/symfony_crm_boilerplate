<?php

declare(strict_types=1);

namespace App\Modules\Dashboard;

use App\Shared\Contract\ModuleInterface;

final class DashboardModule implements ModuleInterface
{
    #[\Override]
    public static function getName(): string
    {
        return 'dashboard';
    }

    #[\Override]
    public static function getDescription(): string
    {
        return 'Pulpit glowny ze statystykami systemu CRM.';
    }
}
