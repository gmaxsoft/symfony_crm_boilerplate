<?php

declare(strict_types=1);

namespace App\Modules\Contact;

use App\Shared\Contract\ModuleInterface;

final class ContactModule implements ModuleInterface
{
    public static function getName(): string
    {
        return 'contact';
    }

    public static function getDescription(): string
    {
        return 'Zarządzanie kontaktami i leadami CRM.';
    }
}
