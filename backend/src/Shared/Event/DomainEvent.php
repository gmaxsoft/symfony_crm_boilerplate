<?php

declare(strict_types=1);

namespace App\Shared\Event;

/**
 * Bazowa klasa dla zdarzeń domenowych.
 * Moduły publikują zdarzenia przez EventDispatcher, inne moduły mogą je nasłuchiwać
 * bez bezpośredniego sprzężenia — to główny mechanizm komunikacji między modułami.
 */
abstract class DomainEvent
{
    private \DateTimeImmutable $occurredAt;

    public function __construct()
    {
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /**
     * Zwraca unikalną nazwę zdarzenia, używaną jako klucz w Event Bus.
     */
    abstract public static function getEventName(): string;
}
