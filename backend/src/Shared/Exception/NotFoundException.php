<?php

declare(strict_types=1);

namespace App\Shared\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Rzucany, gdy zasób nie zostanie odnaleziony w żadnym module.
 * Symfony automatycznie mapuje ten wyjątek na odpowiedź HTTP 404.
 */
class NotFoundException extends NotFoundHttpException
{
    public static function forId(string $resource, int|string $id): self
    {
        return new self(sprintf('%s with ID "%s" not found.', $resource, $id));
    }
}
