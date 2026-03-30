<?php

declare(strict_types=1);

namespace App\Modules\Admin;

use App\Shared\Contract\ModuleInterface;

final class AdminModule implements ModuleInterface
{
    #[\Override]
    public static function getName(): string
    {
        return 'admin';
    }

    #[\Override]
    public static function getDescription(): string
    {
        return 'Zarzadzanie uzytkownikami systemu CRM.';
    }
}
