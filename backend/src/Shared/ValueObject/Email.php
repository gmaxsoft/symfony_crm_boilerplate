<?php

declare(strict_types=1);

namespace App\Shared\ValueObject;

use App\Shared\Exception\DomainException;

/**
 * Value Object: adres e-mail.
 * Niezmienny, walidowany przy tworzeniu — używany w module User i Contact.
 */
final class Email
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if (!filter_var($normalized, \FILTER_VALIDATE_EMAIL)) {
            throw new DomainException(\sprintf('"%s" is not a valid email address.', $value));
        }

        $this->value = $normalized;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
