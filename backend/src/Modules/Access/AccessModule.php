<?php

declare(strict_types=1);

namespace App\Modules\Access;

use App\Shared\Contract\ModuleInterface;

final class AccessModule implements ModuleInterface
{
    public static function getName(): string { return 'access'; }
    public static function getDescription(): string { return 'Zarzadzanie uprawnieniami i rolami uzytkownikow.'; }
}
