<?php

declare(strict_types=1);

namespace App\Modules\Admin;

use App\Shared\Contract\ModuleInterface;

final class AdminModule implements ModuleInterface
{
    public static function getName(): string { return 'admin'; }
    public static function getDescription(): string { return 'Zarzadzanie uzytkownikami systemu CRM.'; }
}
