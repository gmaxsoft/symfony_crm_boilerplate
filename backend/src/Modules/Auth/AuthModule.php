<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use App\Shared\Contract\ModuleInterface;

final class AuthModule implements ModuleInterface
{
    #[\Override]
    public static function getName(): string
    {
        return 'auth';
    }

    #[\Override]
    public static function getDescription(): string
    {
        return 'Autoryzacja i uwierzytelnianie uzytkownikow CRM.';
    }
}
