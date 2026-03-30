<?php

declare(strict_types=1);

namespace App\Modules\User;

use App\Shared\Contract\ModuleInterface;

final class UserModule implements ModuleInterface
{
    public static function getName(): string
    {
        return 'user';
    }

    public static function getDescription(): string
    {
        return 'Zarządzanie użytkownikami, rolami i uwierzytelnianiem.';
    }
}
