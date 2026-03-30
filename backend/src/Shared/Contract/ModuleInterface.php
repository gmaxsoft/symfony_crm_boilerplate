<?php

declare(strict_types=1);

namespace App\Shared\Contract;

/**
 * Kontrakt dla każdego modułu w architekturze Modular Monolith.
 *
 * Każdy moduł może opcjonalnie implementować ten interfejs,
 * aby wymusić spójność i umożliwić introspekcję (np. listę modułów w panelu admina).
 */
interface ModuleInterface
{
    /**
     * Zwraca unikalną nazwę modułu (np. "user", "contact").
     */
    public static function getName(): string;

    /**
     * Zwraca opis modułu widoczny w interfejsie.
     */
    public static function getDescription(): string;
}
